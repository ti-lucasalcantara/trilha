<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use DomainException;
use Throwable;

class EdicaoProcesso
{
    public const ERRO_REVISAO = 'O processo foi alterado. Confira os dados atualizados antes de editar novamente.';
    public function __construct(
        private readonly BaseConnection $banco,
        private readonly string $empresa,
        private readonly ConsultaProcessos $consulta,
    ) {
    }

    public function obter(string $hash, ?string $leitor): array
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $hash)) throw PageNotFoundException::forPageNotFound();
        $processo = $this->consulta->listar($leitor, $hash, true)[0] ?? null;
        if (!$processo) throw PageNotFoundException::forPageNotFound();

        $escopo = ['organizacao_id' => $this->empresa, 'processo_id' => $processo['id']];
        $politica = $this->banco->table('politicas_acesso')->where($escopo)->get()->getRowArray();
        $nivel = $politica['nivel'] ?? 'interno';
        $campo = $nivel === 'restrito' ? 'setor_id' : 'vinculo_id';
        $concessoes = $nivel === 'interno' ? [] : $this->banco->table('concessoes_acesso')
            ->select("id, {$campo} AS destinatario")
            ->where(['organizacao_id' => $this->empresa, 'politica_id' => $politica['id'], 'revogado_em' => null])
            ->where('permite_visualizar', 1)->where("{$campo} IS NOT NULL", null, false)
            ->groupStart()->where('expira_em', null)->orWhere('expira_em >', date('Y-m-d H:i:s'))->groupEnd()
            ->orderBy('id')->get()->getResultArray();

        return [
            'processo' => $processo, 'politica' => $politica, 'acesso' => $nivel, 'concessoes' => $concessoes,
            'revisao' => hash('sha256', serialize([$processo['assunto'], $processo['descricao'], $processo['descricao_formatada'], $nivel, $concessoes])),
        ];
    }

    /** Atualiza os dados e as concessões na mesma transação. */
    public function salvar(string $hash, ?string $leitor, string $autor, array $dados, array $permitidos): ?array
    {
        $formatada = DescricaoProcesso::limpar($dados['descricao_formatada']);
        if (strlen($formatada) > 65535) throw new DomainException('A descrição excede o tamanho permitido.');

        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a edição.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $edicao = $this->obter($hash, $leitor);
            if (!hash_equals($edicao['revisao'], $dados['revisao'])) {
                throw new DomainException(self::ERRO_REVISAO);
            }

            $agora = date('Y-m-d H:i:s');
            $processo = $edicao['processo']['id'];
            if ($dados['descricao_formatada'] === '' && $dados['descricao'] === ($edicao['processo']['descricao'] ?? '')) {
                $formatada = $edicao['processo']['descricao_formatada'] ?? '';
            }
            $this->banco->table('processos')->where(['organizacao_id' => $this->empresa, 'id' => $processo])->update([
                'assunto' => $dados['assunto'], 'descricao' => $dados['descricao'] !== '' ? $dados['descricao'] : null,
                'descricao_formatada' => $dados['descricao'] !== '' && $formatada !== '' ? $formatada : null,
                'atualizado_em' => $agora,
            ]);
            $this->salvarAcesso($edicao, $dados, $permitidos, $autor, $agora);
            try {
                $atual = $this->obter($hash, $leitor);
            } catch (PageNotFoundException) {
                $atual = null;
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                throw new DomainException('Não foi possível concluir a edição.');
            }
            return $atual;
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    private function salvarAcesso(array $edicao, array $dados, array $permitidos, string $autor, string $agora): void
    {
        $nivel = $dados['acesso'];
        $mudouNivel = $nivel !== $edicao['acesso'];
        $politica = $edicao['politica']['id'] ?? null;
        $autorizados = $dados['autorizados'];
        if (!$politica) {
            if ($nivel === 'interno') return;
            $this->banco->table('politicas_acesso')->insert([
                'organizacao_id' => $this->empresa, 'processo_id' => $edicao['processo']['id'],
                'nivel' => $nivel, 'herda_processo' => 0, 'alterada_por' => $autor, 'criado_em' => $agora,
            ]);
            $politica = $this->banco->insertID();
        }
        $escopo = ['organizacao_id' => $this->empresa, 'politica_id' => $politica];
        if ($mudouNivel) {
            $this->banco->table('politicas_acesso')->where(['organizacao_id' => $this->empresa, 'id' => $politica])
                ->update(['nivel' => $nivel, 'alterada_por' => $autor, 'atualizado_em' => $agora]);
            $this->banco->table('concessoes_acesso')->where($escopo)->where('revogado_em', null)->update(['revogado_em' => $agora]);
        } else {
            foreach ($edicao['concessoes'] as $concessao) {
                $destino = $concessao['destinatario'];
                if (isset($dados['autorizados'][$destino]) || !isset($permitidos[$destino])) {
                    unset($autorizados[$destino]);
                    continue;
                }
                $this->banco->table('concessoes_acesso')->where($escopo)->where('id', $concessao['id'])->update(['revogado_em' => $agora]);
            }
        }
        $campo = $nivel === 'restrito' ? 'setor_id' : 'vinculo_id';
        foreach ($autorizados as $destino) {
            $this->banco->table('concessoes_acesso')->insert($escopo + [
                $campo => $destino, 'permite_visualizar' => 1, 'permite_download' => 0,
                'permite_editar' => 0, 'permite_tramitar' => 0, 'inicio_em' => $agora,
                'concedido_por' => $autor, 'criado_em' => $agora,
            ]);
        }
    }
}
