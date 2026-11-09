<?php

namespace App\Libraries\Admin;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use RuntimeException;

/** Grava somente a estrutura da empresa selecionada na simulação. */
class EstruturaSimulada
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista unidades e setores sempre no âmbito da empresa. */
    public function listar(): array
    {
        $unidades = $this->banco->table('unidades')->where('organizacao_id', $this->empresa)->orderBy('nome')->get()->getResultArray();
        $setores = $this->banco->table('setores')->where('organizacao_id', $this->empresa)->orderBy('nome')->get()->getResultArray();
        return compact('unidades', 'setores');
    }

    /** Inclui ou edita um registro, conferindo o escopo também no UPDATE. */
    public function salvar(string $tabela, string $id, array $dados, string $unidade): void
    {
        $consulta = $this->banco->table($tabela);
        $agora = date('Y-m-d H:i:s');
        if ($id === '') {
            if ($tabela === 'setores') {
                if (!$this->banco->table('unidades')->where(['id' => $unidade, 'organizacao_id' => $this->empresa])->countAllResults()) {
                    throw PageNotFoundException::forPageNotFound();
                }
                $dados['unidade_id'] = $unidade;
            }
            $salvou = $consulta->insert($dados + ['organizacao_id' => $this->empresa, 'criado_em' => $agora]);
        } else {
            $escopo = ['id' => $id, 'organizacao_id' => $this->empresa];
            if (!$consulta->where($escopo)->countAllResults()) throw PageNotFoundException::forPageNotFound();
            $salvou = $consulta->where($escopo)->update($dados + ['atualizado_em' => $agora]);
        }
        if (!$salvou) throw new RuntimeException('Falha ao gravar a estrutura da organização.');
    }
}
