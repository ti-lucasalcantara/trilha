<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

/** Gerencia o catálogo de tipos e a vigência das suas regras de numeração. */
class GestaoTiposProcesso
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista tipos, máscaras vigentes e uso no histórico da organização. */
    public function listar(): array
    {
        $tipos = $this->tabela('tipos_processo')->orderBy('nome')->get()->getResultArray();
        $regras = $this->regrasVigentes(date('Y-m-d H:i:s'))->orderBy('versao')->get()->getResultArray();
        $porTipo = array_column($regras, null, 'tipo_processo_id');
        $usados = array_fill_keys(array_column($this->tabela('processos')->distinct()->select('tipo_processo_id')->get()->getResultArray(), 'tipo_processo_id'), true);
        foreach ($tipos as &$tipo) {
            $regra = $porTipo[$tipo['id']] ?? null;
            $tipo['mascara'] = $regra['mascara'] ?? '';
            $tipo['digitos_minimos'] = $regra['digitos_minimos'] ?? 4;
            $tipo['sigla_numeracao'] = $regra['sigla'] ?? $tipo['sigla'];
            $tipo['em_uso'] = isset($usados[$tipo['id']]);
        }
        unset($tipo);
        return $tipos;
    }

    /** Cadastra ou edita o tipo e cria nova vigência apenas quando a numeração muda. */
    public function salvar(string $id, array $dados, ?string $autor): void
    {
        $numeracao = ['mascara' => $dados['mascara'], 'sigla' => $dados['sigla_numeracao'], 'digitos_minimos' => $dados['digitos_minimos']];
        AberturaProcesso::formatarNumero($numeracao, 1, (int) date('Y'));
        $this->transacionar(function () use ($id, $dados, $autor, $numeracao): void {
            $agora = date('Y-m-d H:i:s');
            $cadastro = ['nome' => $dados['nome'], 'sigla' => $dados['sigla'],
                'descricao' => $dados['descricao'] === '' ? null : $dados['descricao'], 'ativo' => $dados['ativo']];
            if ($id === '') {
                $this->tabela('tipos_processo')->insert($cadastro + ['organizacao_id' => $this->empresa, 'criado_em' => $agora]);
                $id = (string) $this->banco->insertID();
            } else {
                $this->exigirTipo($id);
                $this->tabela('tipos_processo')->where('id', $id)->update($cadastro + ['atualizado_em' => $agora]);
            }

            $chave = ['organizacao_id' => $this->empresa, 'tipo_processo_id' => $id];
            if (!$this->tabela('tipo_processo_versoes')->where('tipo_processo_id', $id)->countAllResults()) {
                $this->tabela('tipo_processo_versoes')->insert($chave + [
                    'versao' => 1, 'publicado_em' => $agora, 'publicado_por' => $autor, 'criado_em' => $agora,
                ]);
            }
            $vigente = $this->regrasVigentes($agora)->where('tipo_processo_id', $id)->orderBy('versao', 'DESC')->get(1)->getRowArray();
            if ($vigente && array_intersect_key($vigente, $numeracao) == $numeracao) return;

            $ultima = $this->tabela('regras_numeracao_processo')->where('tipo_processo_id', $id)
                ->selectMax('versao')->get()->getRowArray()['versao'];
            $proxima = $this->tabela('regras_numeracao_processo')->where('tipo_processo_id', $id)
                ->where('vigente_desde >', $agora)->orderBy('vigente_desde')->get(1)->getRowArray();
            $this->regrasVigentes($agora)->where('tipo_processo_id', $id)
                ->update(['vigente_ate' => $agora, 'atualizado_em' => $agora]);
            $this->tabela('regras_numeracao_processo')->insert($chave + $numeracao + [
                'versao' => (int) $ultima + 1, 'vigente_desde' => $agora,
                'vigente_ate' => $proxima['vigente_desde'] ?? null, 'criado_em' => $agora,
            ]);
        });
    }

    /** Exclui tipos sem uso e conserva os que já identificam processos. */
    public function excluir(string $id): void
    {
        $this->transacionar(function () use ($id): void {
            $this->exigirTipo($id);
            if ($this->tabela('processos')->where('tipo_processo_id', $id)->countAllResults()) {
                throw new DomainException('Este tipo tem processos vinculados. Inative-o para preservar o histórico.');
            }
            foreach (['sequencias_processo', 'regras_numeracao_processo', 'tipo_processo_versoes'] as $tabela) {
                $this->tabela($tabela)->where('tipo_processo_id', $id)->delete();
            }
            $this->tabela('tipos_processo')->where('id', $id)->delete();
        });
    }

    /** Aplica o escopo da organização a todas as consultas do catálogo. */
    private function tabela(string $nome): BaseBuilder
    {
        return $this->banco->table($nome)->where('organizacao_id', $this->empresa);
    }

    /** Seleciona as regras válidas na data de referência. */
    private function regrasVigentes(string $agora): BaseBuilder
    {
        return $this->tabela('regras_numeracao_processo')->where('vigente_desde <=', $agora)
            ->groupStart()->where('vigente_ate', null)->orWhere('vigente_ate >', $agora)->groupEnd();
    }

    /** Impede alterações em tipos que não pertencem à organização. */
    private function exigirTipo(string $id): void
    {
        if (!$this->tabela('tipos_processo')->where('id', $id)->countAllResults()) {
            throw new DomainException('Tipo de processo não encontrado nesta organização.');
        }
    }

    /** Serializa alterações com a abertura de processos e reverte falhas parciais. */
    private function transacionar(callable $operacao): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a alteração.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $operacao();
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                throw new DomainException('Não foi possível concluir a alteração.');
            }
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }
}
