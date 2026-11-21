<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\HTTP\ResponseInterface;

abstract class Configuracoes extends BaseController
{
    /** Compartilha apenas o contexto e o layout das páginas de configurações. */
    protected function renderizarConfiguracao(string $hash, string $secao, string $titulo, array $contexto, array $dados): ResponseInterface
    {
        helper(['url', 'form']);
        $vinculo = $contexto['vinculo'];
        $layout = [
            'tituloPagina' => $titulo,
            'paginaAtiva' => 'configuracoes',
            'secaoConfiguracoes' => $secao,
            'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $contexto['organizacao'], 'nomeUsuario' => $contexto['usuario'],
            'usuarioTemAvatar' => !empty($contexto['tem_avatar']),
            'modoSimulacao' => $contexto['simulacao'], 'simulacaoSemVinculo' => $vinculo === null, 'vinculoPreferencias' => $vinculo, 'administradorSimulado' => true,
            'consultasPermitidas' => [], 'empresaSimulada' => $contexto['empresa'],
            'contextoTrabalho' => (new ContextoSetor(db_connect(), (string) $contexto['empresa'], null))->obter(),
        ];
        $view = 'organizacao/configuracoes/' . str_replace('-', '_', $secao);
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view($view, $layout + $dados));
    }
}
