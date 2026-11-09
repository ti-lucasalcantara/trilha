<?php

namespace App\Libraries\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;

/** Protege o Admin e suas prévias até a instalação da autenticação. */
class AcessoLocal
{
    /** Aceita apenas requisições locais em desenvolvimento. */
    public static function validar(IncomingRequest $requisicao): void
    {
        $origem = $requisicao->getServer('REMOTE_ADDR');
        $endereco = strtolower($requisicao->getUri()->getHost());
        if (ENVIRONMENT !== 'development'
            || !in_array($origem, ['127.0.0.1', '::1'], true)
            || !in_array($endereco, ['localhost', '127.0.0.1', '::1', '[::1]', 'trilha.localhost'], true)) {
            throw PageNotFoundException::forPageNotFound();
        }
    }
}
