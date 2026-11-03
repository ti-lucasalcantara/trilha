<?php

namespace App\Controllers;

/** Mantém a página inicial do projeto até a implementação da área de trabalho. */
class Inicio extends BaseController
{
    /** Exibe a página inicial fornecida pelo CodeIgniter. */
    public function exibir(): string
    {
        return view('welcome_message');
    }
}
