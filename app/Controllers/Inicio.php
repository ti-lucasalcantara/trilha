<?php

namespace App\Controllers;

/** Apresenta o produto e seus planos na página pública do Trilha. */
class Inicio extends BaseController
{
    /** Prepara os endereços internos sem executar contratação ou autenticação. */
    public function exibir(): string
    {
        helper('url');

        return view('comercial/inicio', [
            'enderecoDemonstracao' => site_url('organizacao/1/dashboard'),
            'enderecoLogin' => site_url('login'),
            'enderecoContato' => '#',
        ]);
    }
}
