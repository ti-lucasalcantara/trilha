<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\BaseConnection;
use DomainException;

/** Gerencia as situações de processo no escopo da organização. */
class GestaoSituacoesProcesso
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista as situações em ordem e identifica vínculos com processos ou seu histórico. */
    public function listar(): array
    {
        return $this->consulta()->select('status_processo.*')
            ->select('EXISTS (SELECT 1 FROM processos p WHERE p.organizacao_id = status_processo.organizacao_id AND p.status_id = status_processo.id)
                OR EXISTS (SELECT 1 FROM processo_eventos_estado e WHERE e.organizacao_id = status_processo.organizacao_id
                    AND status_processo.id IN (e.estado_anterior_id, e.estado_novo_id)) AS em_uso', false)
            ->orderBy('ordem')->orderBy('id')->get()->getResultArray();
    }

    /** Grava a situação sem alterar os vínculos existentes. */
    public function salvar(string $id, array $dados): void
    {
        $agora = date('Y-m-d H:i:s');
        if ($id === '') {
            $this->consulta()->insert($dados + ['organizacao_id' => $this->empresa, 'criado_em' => $agora]);
            return;
        }
        $this->exigirSituacao($id);
        $this->consulta()->where('id', $id)->update($dados + ['atualizado_em' => $agora]);
    }

    /** As chaves estrangeiras impedem a exclusão se a situação estiver em uso. */
    public function excluir(string $id): void
    {
        $this->consulta()->where('id', $id)->delete();
        if (!$this->banco->affectedRows()) throw new DomainException('Situação não encontrada nesta organização.');
    }

    /** Limita leitura e alteração à empresa da sessão. */
    private function consulta(): BaseBuilder
    {
        return $this->banco->table('status_processo')->where('organizacao_id', $this->empresa);
    }

    /** Verifica se o registro pertence à empresa antes de editar. */
    private function exigirSituacao(string $id): void
    {
        if (!$this->consulta()->where('id', $id)->countAllResults()) {
            throw new DomainException('Situação não encontrada nesta organização.');
        }
    }
}
