<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Database\BaseConnection;
use App\Libraries\Admin\AcessoLocal;
use App\Libraries\Admin\Cadastros;

/** Compartilha a proteção provisória e a apresentação do painel local. */
abstract class BaseAdmin extends BaseController
{
    /** Abre os cadastros usando a conexão restrita do painel. */
    protected function abrirBanco(): Cadastros
    {
        return new Cadastros($this->conexaoAdmin());
    }

    /** Restringe o painel ao servidor local até a instalação da autenticação. */
    protected function conexaoAdmin(): BaseConnection
    {
        AcessoLocal::validar($this->request);
        helper(['url', 'form']);
        return db_connect();
    }

    /** Impede que respostas com dados administrativos sejam armazenadas em cache. */
    protected function renderizar(string $vista, array $dados): ResponseInterface
    {
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('admin/' . $vista, $dados));
    }
}
