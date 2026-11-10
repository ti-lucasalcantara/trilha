<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

/** Abre processos e reserva sua numeração dentro da mesma transação. */
class AberturaProcesso
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista os cadastros ativos usados pelo formulário da organização. */
    public function opcoes(): array
    {
        return [
            'tipos' => $this->cadastroAtivo('tipos_processo')->select('id, nome')->orderBy('nome')->get()->getResultArray(),
            'situacoes' => $this->cadastroAtivo('status_processo')->select('id, nome')->orderBy('ordem')->orderBy('id')->get()->getResultArray(),
        ];
    }

    /** Grava o processo, seu estado inicial e as concessões selecionadas. */
    public function abrir(array $dados, array $setor, string $autor, int $ano): string
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a abertura do processo.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $agora = date('Y-m-d H:i:s');
            // Mantém a situação ativa até concluir a abertura, mesmo durante uma edição concorrente.
            $situacao = $this->banco->query('SELECT id FROM status_processo WHERE organizacao_id = ? AND id = ? AND ativo = 1 FOR UPDATE',
                [$this->empresa, $dados['situacao']])->getRowArray();
            if (!$this->cadastroAtivo('tipos_processo')->where('id', $dados['tipo'])->countAllResults() || !$situacao) {
                throw new DomainException('Selecione um tipo e uma situação ativos desta organização.');
            }

            $tipo = ['organizacao_id' => $this->empresa, 'tipo_processo_id' => $dados['tipo']];
            $versao = $this->banco->table('tipo_processo_versoes')->where($tipo)
                ->where('publicado_em <=', $agora)->orderBy('versao', 'DESC')->get(1)->getRowArray();
            $regra = $this->banco->table('regras_numeracao_processo')->where($tipo)->where('vigente_desde <=', $agora)
                ->groupStart()->where('vigente_ate', null)->orWhere('vigente_ate >', $agora)->groupEnd()
                ->orderBy('versao', 'DESC')->get(1)->getRowArray();
            if (!$versao || !$regra) {
                throw new DomainException('Este tipo precisa de uma versão publicada e de uma regra de numeração vigente.');
            }
            if ($this->banco->table('tipo_processo_campos')->where([
                'organizacao_id' => $this->empresa, 'tipo_processo_versao_id' => $versao['id'], 'obrigatorio' => 1,
            ])->countAllResults()) {
                throw new DomainException('Este tipo exige campos personalizados que ainda precisam ser incluídos no formulário.');
            }

            $sequencia = $tipo + ['ano' => $ano];
            $contador = $this->banco->table('sequencias_processo')->where($sequencia)->get()->getRowArray();
            // Considera processos existentes mesmo quando o contador ainda não foi registrado.
            $ultimo = $this->banco->table('processos')->selectMax('numero_sequencial', 'numero')
                ->where($sequencia)->get()->getRowArray()['numero'];
            $base = max((int) $ultimo, (int) ($contador['ultimo_numero'] ?? 0));
            if ($base === PHP_INT_MAX) throw new DomainException('A sequência deste tipo atingiu o limite.');
            $numero = $base + 1;
            $numeroCompleto = self::formatarNumero($regra, $numero, $ano);
            if ($this->banco->table('processos')->where([
                'organizacao_id' => $this->empresa, 'numero_completo' => $numeroCompleto,
            ])->countAllResults()) {
                throw new DomainException('A regra gerou um número já usado. Revise a configuração da numeração deste tipo.');
            }

            $hash = bin2hex(random_bytes(24));
            $this->banco->table('processos')->insert($sequencia + [
                'hash_publico' => $hash,
                'tipo_processo_versao_id' => $versao['id'], 'regra_numeracao_id' => $regra['id'],
                'numero_sequencial' => $numero, 'numero_completo' => $numeroCompleto,
                'assunto' => $dados['assunto'], 'descricao' => $dados['descricao'] === '' ? null : $dados['descricao'],
                'status_id' => $dados['situacao'], 'unidade_origem_id' => $setor['unidade'], 'setor_origem_id' => $setor['id'],
                'aberto_por' => $autor, 'aberto_em' => $agora, 'criado_em' => $agora,
            ]);
            $processo = (string) $this->banco->insertID();
            $registro = ['organizacao_id' => $this->empresa, 'processo_id' => $processo, 'criado_em' => $agora];
            $this->banco->table('processo_eventos_estado')->insert($registro + [
                'estado_novo_id' => $dados['situacao'], 'autor_id' => $autor, 'ocorrido_em' => $agora,
            ]);
            $this->banco->table('politicas_acesso')->insert($registro + [
                'nivel' => $dados['acesso'], 'herda_processo' => 0, 'alterada_por' => $autor,
            ]);
            $politica = (string) $this->banco->insertID();
            foreach ($dados['autorizados'] as $autorizado) {
                $campo = $dados['acesso'] === 'restrito' ? 'setor_id' : 'vinculo_id';
                $this->banco->table('concessoes_acesso')->insert([
                    'organizacao_id' => $this->empresa, 'politica_id' => $politica, $campo => $autorizado,
                    'permite_visualizar' => 1, 'permite_download' => 0, 'permite_editar' => 0, 'permite_tramitar' => 0,
                    'inicio_em' => $agora, 'concedido_por' => $autor, 'criado_em' => $agora,
                ]);
            }
            if ($contador) {
                $this->banco->table('sequencias_processo')->where($sequencia)->update(['ultimo_numero' => $numero, 'atualizado_em' => $agora]);
            } else {
                $this->banco->table('sequencias_processo')->insert($sequencia + ['ultimo_numero' => $numero, 'criado_em' => $agora]);
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                throw new DomainException('Não foi possível concluir a abertura do processo.');
            }
            return $hash;
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Limita as opções a cadastros ativos da empresa que abre o processo. */
    private function cadastroAtivo(string $tabela): \CodeIgniter\Database\BaseBuilder
    {
        return $this->banco->table($tabela)->where(['organizacao_id' => $this->empresa, 'ativo' => 1]);
    }

    /** Substitui os marcadores da máscara, preservando a ordem definida pela empresa. */
    public static function formatarNumero(array $regra, int $numero, int $ano): string
    {
        $mascara = $regra['mascara'];
        $digitos = (int) $regra['digitos_minimos'];
        if ($digitos < 0 || $digitos > 200 || !str_contains($mascara, '{numero}')) {
            throw new DomainException('A máscara deve conter {numero} e ter um preenchimento válido.');
        }
        $formatado = strtr($mascara, [
            '{sigla}' => $regra['sigla'],
            '{numero}' => str_pad((string) $numero, $digitos, '0', STR_PAD_LEFT),
            '{ano}' => (string) $ano,
        ]);
        if (str_contains($formatado, '{') || str_contains($formatado, '}') || mb_strlen($formatado) > 200) {
            throw new DomainException('Confira os marcadores da máscara e o limite de 200 caracteres do número.');
        }
        return $formatado;
    }
}
