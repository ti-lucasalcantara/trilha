<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Abre a prévia administrativa da organização demonstrativa. */
class Configuracoes extends BaseController
{
    /** Reaproveita a navegação e a identidade visual da área da organização. */
    public function exibir(string $identificadorOrganizacao): string
    {
        if ($identificadorOrganizacao !== '1') {
            throw PageNotFoundException::forPageNotFound();
        }

        helper('url');

        return view('organizacao/configuracoes', [
            'tituloPagina' => 'Estrutura e equipe',
            'paginaAtiva' => 'configuracoes',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte',
            'nomeUsuario' => 'Lucas',
        ]);
    }
}
