<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// O nome routes é fornecido pelo CI4; os métodos dos controladores são próprios do Trilha.
$routes->get('/', 'Inicio::exibir');
$routes->get('login', 'Autenticacao\ControladorAcesso::exibir');
$routes->post('login', 'Autenticacao\ControladorAcesso::entrar', ['filter' => 'csrf']);
$routes->post('sair', 'Autenticacao\ControladorAcesso::sair', ['filter' => 'csrf']);

// Usa o identificador provisório 1 na prévia visual da organização.
$routes->get('organizacao/(:any)/dashboard', 'Organizacao\Painel::exibir/$1');
$routes->post('organizacao/(:segment)/contexto-trabalho', 'Organizacao\ContextoTrabalho::selecionar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/simulacao/estrutura', 'Organizacao\EstruturaSimulada::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/estrutura', 'Organizacao\EstruturaSimulada::salvarAutenticada/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/equipe', 'Organizacao\Equipe::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/perfis', 'Organizacao\Equipe::salvarPerfil/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/perfis/(:num)/excluir', 'Organizacao\Equipe::excluirPerfil/$1/$2', ['filter' => 'csrf']);
$routes->get('organizacao/(:any)/processos', 'Organizacao\Processos::exibir/$1');
$routes->get('organizacao/(:segment)/processos/novo', 'Organizacao\Processos::novo/$1');
$routes->post('organizacao/(:segment)/processos/novo', 'Organizacao\Processos::abrir/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/lote', 'Organizacao\Processos::executarLote/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/(:segment)/pasta', 'Organizacao\Processos::moverParaPasta/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/(:segment)/acompanhamento', 'Organizacao\Processos::acompanhar/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/pastas', 'Organizacao\Processos::cadastrarPasta/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/pastas/acao', 'Organizacao\Processos::alterarPasta/$1', ['filter' => 'csrf']);
$routes->get('organizacao/(:any)/processos/(:segment)', 'Organizacao\Processos::visualizar/$1/$2');
$routes->get('organizacao/(:any)/configuracoes', 'Organizacao\Configuracoes::exibir/$1');
$routes->post('organizacao/(:segment)/tipos-processo', 'Organizacao\TiposProcesso::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/tipos-processo/(:num)/excluir', 'Organizacao\TiposProcesso::excluir/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/situacoes-processo', 'Organizacao\SituacoesProcesso::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/situacoes-processo/(:num)/excluir', 'Organizacao\SituacoesProcesso::excluir/$1/$2', ['filter' => 'csrf']);
$routes->get('admin', 'Admin\Painel::exibir');
$routes->get('admin/clientes', 'Admin\Clientes::exibir');
$routes->get('admin/planos', 'Admin\Planos::exibir');
$routes->get('admin/usuarios', 'Admin\Usuarios::exibir');
$routes->get('admin/financeiro', 'Admin\Financeiro::exibir');
$routes->get('admin/clientes/(:num)', 'Admin\Empresas::exibir/$1');
$routes->post('admin/clientes/(:num)/simular', 'Admin\Empresas::simular/$1', ['filter' => 'csrf']);
$routes->post('admin/clientes/(:num)/encerrar-simulacao', 'Admin\Empresas::encerrarSimulacao/$1', ['filter' => 'csrf']);

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
