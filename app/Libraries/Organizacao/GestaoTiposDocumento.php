<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

class GestaoTiposDocumento
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    public function listar(): array
    {
        return $this->banco->table('tipos_documento')->where('organizacao_id', $this->empresa)
            ->orderBy('nome')->get()->getResultArray();
    }

    public function salvar(string $id, array $dados, ?string $autor): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar o cadastro.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $agora = date('Y-m-d H:i:s');
            $escopo = ['organizacao_id' => $this->empresa];
            $tipos = $this->banco->table('tipos_documento');
            if ($id === '') {
                $tipos->insert($escopo + $dados + ['criado_em' => $agora]);
                $id = (string) $this->banco->insertID();
            } else {
                $chave = $escopo + ['id' => $id];
                if (!$tipos->where($chave)->countAllResults()) throw new DomainException('Tipo de documento não encontrado nesta organização.');
                $tipos->where($chave)->update($dados + ['atualizado_em' => $agora]);
            }

            if ($dados['ativo']) {
                $chaveVersao = $escopo + ['tipo_documento_id' => $id];
                $versoes = $this->banco->table('tipo_documento_versoes');
                if (!$versoes->where($chaveVersao)->where('publicado_em <=', $agora)->countAllResults()) {
                    $ultima = $versoes->where($chaveVersao)->selectMax('versao')->get()->getRowArray();
                    $versoes->insert($chaveVersao + [
                        'versao' => (int) ($ultima['versao'] ?? 0) + 1, 'publicado_em' => $agora,
                        'publicado_por' => $autor, 'criado_em' => $agora,
                    ]);
                }
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                throw new DomainException('Não foi possível salvar o tipo de documento.');
            }
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }
}
