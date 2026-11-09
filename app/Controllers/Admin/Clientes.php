<?php

namespace App\Controllers\Admin;

/** Configura o cadastro administrativo de clientes. */
class Clientes extends Cadastro
{
    protected const CADASTRO = 'clientes';
    protected const TITULO = 'Clientes';
    protected const SINGULAR = 'cliente';
    protected const DESCRICAO = 'Gerencie as organizações clientes e suas contratações.';
}
