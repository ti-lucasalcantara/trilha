<?php

namespace App\Controllers\Admin;

/** Configura o cadastro administrativo de usuarios. */
class Usuarios extends Cadastro
{
    protected const CADASTRO = 'usuarios';
    protected const TITULO = 'Usuários';
    protected const SINGULAR = 'usuário';
    protected const DESCRICAO = 'Gerencie contas e seus vínculos com organizações.';
}
