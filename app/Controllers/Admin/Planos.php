<?php

namespace App\Controllers\Admin;

/** Configura o cadastro administrativo de planos. */
class Planos extends Cadastro
{
    protected const CADASTRO = 'planos';
    protected const TITULO = 'Planos';
    protected const SINGULAR = 'plano';
    protected const DESCRICAO = 'Gerencie os valores mensais e limites de cada plano.';
}
