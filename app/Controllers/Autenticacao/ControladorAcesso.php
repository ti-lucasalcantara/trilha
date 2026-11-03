<?php

namespace App\Controllers\Autenticacao;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

/** Apresenta a tela de entrada enquanto a autenticação não está implementada. */
class ControladorAcesso extends BaseController
{
    /** Exibe o formulário de entrada. */
    public function exibir(): ResponseInterface
    {
        return $this->renderizarEntrada();
    }

    /** Informa que a verificação de credenciais ainda não está disponível. */
    public function informarIndisponibilidade(): ResponseInterface
    {
        return $this->renderizarEntrada(
            'O acesso à conta ainda não está disponível. A autenticação será habilitada em uma próxima etapa.',
            501,
        );
    }

    /** Renderiza a tela sem permitir que o navegador armazene seu conteúdo em cache. */
    private function renderizarEntrada(?string $aviso = null, int $codigoHttp = 200): ResponseInterface
    {
        helper(['url', 'form']);

        // A propriedade response e seus métodos pertencem ao contrato do CodeIgniter.
        return $this->response
            ->setStatusCode($codigoHttp)
            ->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('autenticacao/entrar', ['aviso' => $aviso]));
    }
}
