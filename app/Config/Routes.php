<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
// O nome routes é fornecido pelo CI4; os métodos dos controladores são próprios do Trilha.
$routes->get('/', 'Inicio::exibir');
$routes->get('login', 'Autenticacao\ControladorAcesso::exibir');
$routes->post('login', 'Autenticacao\ControladorAcesso::entrar', ['filter' => 'csrf']);
$routes->post('sair', 'Autenticacao\ControladorAcesso::sair', ['filter' => 'csrf']);
$routes->get('bloqueio', 'Autenticacao\ControladorAcesso::bloqueio');
$routes->post('bloqueio', 'Autenticacao\ControladorAcesso::desbloquear', ['filter' => 'csrf']);
$routes->post('bloqueio/trocar-usuario', 'Autenticacao\ControladorAcesso::trocarUsuario', ['filter' => 'csrf']);
$routes->get('bloqueio/avatar', 'Autenticacao\ControladorAcesso::avatarBloqueado');
$routes->get('organizacao/(:segment)/escolher-setor', 'Autenticacao\ControladorAcesso::escolherSetor/$1');
$routes->post('organizacao/(:segment)/escolher-setor', 'Autenticacao\ControladorAcesso::confirmarSetor/$1', ['filter' => 'csrf']);

$routes->get('organizacao/(:any)/dashboard', 'Organizacao\CaixaTrabalho::exibir/$1');
$routes->get('organizacao/(:segment)/pastas', 'Organizacao\Pastas::exibir/$1');
$routes->post('organizacao/(:segment)/pastas', 'Organizacao\Pastas::salvar/$1', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/meu-perfil', 'Organizacao\Perfil::exibir/$1');
$routes->post('organizacao/(:segment)/meu-perfil/senha', 'Organizacao\Perfil::alterarSenha/$1', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/meu-perfil/foto', 'Organizacao\Perfil::foto/$1');
$routes->post('organizacao/(:segment)/meu-perfil/foto', 'Organizacao\Perfil::alterarFoto/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/contexto-trabalho', 'Organizacao\ContextoTrabalho::selecionar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/estrutura', 'Organizacao\Estrutura::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/equipe', 'Organizacao\Equipe::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/perfis', 'Organizacao\Perfis::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/perfis/(:num)/excluir', 'Organizacao\Perfis::excluir/$1/$2', ['filter' => 'csrf']);
$routes->get('organizacao/(:any)/processos', 'Organizacao\Processos::exibir/$1');
$routes->get('organizacao/(:segment)/processos/novo', 'Organizacao\Processos::novo/$1');
$routes->post('organizacao/(:segment)/processos/novo', 'Organizacao\Processos::abrir/$1', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/processos/(:segment)/editar', 'Organizacao\Processos::editar/$1/$2');
$routes->post('organizacao/(:segment)/processos/(:segment)/editar', 'Organizacao\Processos::atualizar/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/lote', 'Organizacao\Processos::executarLote/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/(:segment)/documentos', 'Organizacao\Processos::enviarDocumento/$1/$2', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/processos/(:segment)/editor-documento', 'Organizacao\Processos::editorDocumento/$1/$2');
$routes->post('organizacao/(:segment)/processos/(:segment)/editor-documento', 'Organizacao\Processos::editorDocumento/$1/$2', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/processos/(:segment)/documentos/(:num)/leitura', 'Organizacao\Processos::lerDocumentoEditado/$1/$2/$3');
$routes->post('organizacao/(:segment)/processos/(:segment)/documentos/(:num)/nome', 'Organizacao\Processos::renomearDocumento/$1/$2/$3', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/(:segment)/documentos/(:num)/exclusao', 'Organizacao\Processos::alterarExclusaoDocumento/$1/$2/$3', ['filter' => 'csrf']);
$routes->get('organizacao/(:segment)/processos/(:segment)/documentos/(:num)/arquivo', 'Organizacao\Processos::baixarDocumento/$1/$2/$3');
$routes->get('organizacao/(:segment)/processos/(:segment)/documentos/(:num)/visualizar', 'Organizacao\Processos::baixarDocumento/$1/$2/$3/visualizar');
$routes->post('organizacao/(:segment)/processos/(:segment)/pasta', 'Organizacao\Processos::moverParaPasta/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/(:segment)/acompanhamento', 'Organizacao\Processos::acompanhar/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/pastas', 'Organizacao\Processos::cadastrarPasta/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/processos/pastas/acao', 'Organizacao\Processos::alterarPasta/$1', ['filter' => 'csrf']);
$routes->get('organizacao/(:any)/processos/(:segment)', 'Organizacao\Processos::visualizar/$1/$2');
$routes->get('organizacao/(:segment)/configuracoes', 'Organizacao\Estrutura::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/estrutura', 'Organizacao\Estrutura::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/equipe', 'Organizacao\Equipe::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/perfis', 'Organizacao\Perfis::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/tipos-processo', 'Organizacao\TiposProcesso::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/situacoes-processo', 'Organizacao\SituacoesProcesso::exibir/$1');
$routes->get('organizacao/(:segment)/configuracoes/tipos-documento', 'Organizacao\TiposDocumento::exibir/$1');
$routes->post('organizacao/(:segment)/configuracoes/tipos-documento', 'Organizacao\TiposDocumento::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/tipos-processo', 'Organizacao\TiposProcesso::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/tipos-processo/(:num)/excluir', 'Organizacao\TiposProcesso::excluir/$1/$2', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/situacoes-processo', 'Organizacao\SituacoesProcesso::salvar/$1', ['filter' => 'csrf']);
$routes->post('organizacao/(:segment)/situacoes-processo/inicial', 'Organizacao\SituacoesProcesso::definirInicial/$1', ['filter' => 'csrf']);
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
