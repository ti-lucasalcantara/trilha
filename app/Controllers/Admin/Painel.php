<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\ResponseInterface;

/** Apresenta indicadores reais da plataforma. */
class Painel extends BaseAdmin
{
    /** Consulta os cadastros usados no painel administrativo. */
    public function exibir(): ResponseInterface
    {
        $banco = $this->abrirBanco();

        return $this->renderizar('painel', [
            'tituloPagina' => 'Visão geral',
            'descricaoPagina' => 'Acompanhe as organizações, os planos e os usuários do Trilha.',
            'paginaAtiva' => 'painel',
            'totalClientes' => $banco->contar('clientes'),
            'totalPlanos' => $banco->contar('planos'),
            'totalUsuarios' => $banco->contar('usuarios'),
        ]);
    }
}
