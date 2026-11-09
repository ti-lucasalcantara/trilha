<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// O nome routes é fornecido pelo CI4; os métodos dos controladores são próprios do Trilha.
$routes->get('/', 'Inicio::exibir');
$routes->get('login', 'Autenticacao\ControladorAcesso::exibir');
$routes->post('login', 'Autenticacao\ControladorAcesso::informarIndisponibilidade', ['filter' => 'csrf']);

// Usa o identificador provisório 1 na prévia visual da organização.
$routes->get('organizacao/(:any)/dashboard', 'Organizacao\Painel::exibir/$1');
$routes->get('organizacao/(:any)/processos', 'Organizacao\Processos::exibir/$1');
$routes->get('organizacao/(:any)/processos/(:segment)', 'Organizacao\Processos::visualizar/$1/$2');
$routes->get('organizacao/(:any)/configuracoes', 'Organizacao\Configuracoes::exibir/$1');
$routes->get('admin', 'Admin\Painel::exibir');
$routes->get('admin/clientes', 'Admin\Clientes::exibir');
$routes->get('admin/planos', 'Admin\Planos::exibir');
$routes->get('admin/usuarios', 'Admin\Usuarios::exibir');
$routes->get('admin/financeiro', 'Admin\Financeiro::exibir');
$routes->get('admin/clientes/(:num)', 'Admin\Empresas::exibir/$1');
$routes->get('admin/clientes/(:num)/simular', 'Admin\Empresas::simular/$1');

// As gravações administrativas exigem o token CSRF do formulário.
foreach (['clientes' => 'Clientes', 'planos' => 'Planos', 'usuarios' => 'Usuarios'] as $cadastroAdmin => $controladorAdmin) {
    $caminhoAdmin = 'admin/' . $cadastroAdmin;
    $destinoAdmin = 'Admin\\' . $controladorAdmin . '::';
    $routes->get($caminhoAdmin . '/novo', $destinoAdmin . 'novo');
    $routes->get($caminhoAdmin . '/(:num)/editar', $destinoAdmin . 'editar/$1');
    $routes->post($caminhoAdmin, $destinoAdmin . 'salvar', ['filter' => 'csrf']);
    $routes->post($caminhoAdmin . '/(:num)', $destinoAdmin . 'salvar/$1', ['filter' => 'csrf']);
    $routes->post($caminhoAdmin . '/(:num)/excluir', $destinoAdmin . 'excluir/$1', ['filter' => 'csrf']);
}
