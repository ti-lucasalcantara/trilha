<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

/** Centraliza a inicialização dos controladores do Trilha. */
abstract class BaseController extends Controller
{
    /** Inicializa os serviços do controlador; o nome do método é exigido pelo CI4. */
    public function initController(RequestInterface $requisicao, ResponseInterface $resposta, LoggerInterface $registrador)
    {
        parent::initController($requisicao, $resposta, $registrador);
    }
}
