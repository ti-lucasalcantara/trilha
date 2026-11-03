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
