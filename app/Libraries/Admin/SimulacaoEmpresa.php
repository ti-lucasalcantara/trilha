<?php

namespace App\Libraries\Admin;

use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;

/** Identifica o contexto temporário de prévia na sessão administrativa. */
class SimulacaoEmpresa
{
    public const SESSAO = 'simulacao_empresa_admin';

    /** Confirma o vínculo da sessão com a empresa antes de qualquer leitura ou gravação. */
    public static function contexto(IncomingRequest $requisicao, string $hash, bool $exigirAdministrador = false): array
    {
        AcessoLocal::validar($requisicao);
        $simulacao = session()->get(self::SESSAO);
        if (!preg_match('/^[a-f0-9]{48}$/D', $hash)
            || !$simulacao || !hash_equals($simulacao['hash'], $hash)
            || ($exigirAdministrador && $simulacao['usuario'] !== 'administrador')) {
            throw PageNotFoundException::forPageNotFound();
        }

        return $simulacao;
    }
}
