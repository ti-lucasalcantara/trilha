<?php

namespace App\Libraries\Admin;

use App\Libraries\Organizacao\AcessoOrganizacao;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\IncomingRequest;

/** Resolve o contexto temporário aberto pelo administrador do Trilha. */
class SimulacaoEmpresa
{
    public const SESSAO = 'simulacao_empresa_admin';

    /** Confirma o vínculo da sessão com a empresa antes de qualquer leitura ou gravação. */
    public static function contexto(IncomingRequest $requisicao, string $hash, bool $exigirAdministrador = false): array
    {
        AcessoLocal::validar($requisicao);
        $simulacao = session()->get(self::SESSAO);
        if (!preg_match('/^[a-f0-9]{48}$/D', $hash)
            || !$simulacao || !hash_equals($simulacao['hash'], $hash)) {
            throw PageNotFoundException::forPageNotFound();
        }

        $banco = db_connect();
        if ($simulacao['usuario'] === 'administrador') {
            $empresa = (new Cadastros($banco))->obter('clientes', (string) $simulacao['empresa']);
            if (!hash_equals($empresa['hash_publico'], $hash)) throw PageNotFoundException::forPageNotFound();
            return ['empresa' => (string) $empresa['id'], 'organizacao' => $empresa['nome'],
                'usuario' => 'Administrador da organização', 'vinculo' => null, 'fundador' => true, 'simulacao' => true];
        }

        $contexto = (new AcessoOrganizacao($banco))->vinculos($simulacao['usuario'], $hash)[0] ?? null;
        if (!$contexto || (string) $contexto['empresa'] !== (string) $simulacao['empresa']
            || ($exigirAdministrador && !$contexto['fundador'])) {
            throw PageNotFoundException::forPageNotFound();
        }
        return $contexto + ['simulacao' => true];
    }
}
