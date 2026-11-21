<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use App\Libraries\Organizacao\FotoUsuario;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

class Perfil extends BaseController
{
    public function exibir(string $hash): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $acesso = new AcessoOrganizacao($banco);
        $contexto = $acesso->contexto($hash);
        $empresa = (string) $contexto['empresa'];
        $vinculo = $contexto['vinculo'] !== null ? (string) $contexto['vinculo'] : null;
        $administrador = (bool) $contexto['fundador'];

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/perfil', [
            'tituloPagina' => 'Meu Perfil',
            'paginaAtiva' => 'perfil',
            'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $contexto['organizacao'],
            'nomeUsuario' => $contexto['usuario'],
            'usuarioTemAvatar' => !empty($contexto['tem_avatar']),
            'emailUsuario' => $contexto['email'] ?? null,
            'modoSimulacao' => $contexto['simulacao'],
            'simulacaoSemVinculo' => $vinculo === null,
            'vinculoPreferencias' => $vinculo,
            'administradorSimulado' => $administrador,
            'empresaSimulada' => $empresa,
            'consultasPermitidas' => $administrador ? [] : $acesso->consultas($contexto),
            'contextoTrabalho' => (new ContextoSetor($banco, $empresa, $administrador ? null : $vinculo))->obter(),
        ]));
    }

    public function foto(string $hash): ResponseInterface
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
        if (!isset($contexto['usuario_id'])) throw PageNotFoundException::forPageNotFound();
        return (new FotoUsuario($banco))->responder((string) $contexto['usuario_id'], $this->response);
    }

    public function alterarFoto(string $hash): ResponseInterface
    {
        helper('url');
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
        if ($contexto['simulacao']) throw PageNotFoundException::forPageNotFound();
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/meu-perfil') . '#foto-perfil', 303)->withCookies();
        try {
            (new FotoUsuario($banco))->salvar((string) $contexto['usuario_id'], $this->request->getFile('foto'));
        } catch (DomainException $erro) {
            return $voltar->with('erroFoto', $erro->getMessage());
        } catch (DatabaseException) {
            return $voltar->with('erroFoto', 'Não foi possível salvar a foto. Tente novamente.');
        }
        return $voltar->with('sucessoFoto', 'Foto de perfil atualizada.');
    }

    public function alterarSenha(string $hash): ResponseInterface
    {
        helper('url');
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
        $usuario = session()->get(AcessoOrganizacao::SESSAO);
        if ($contexto['simulacao'] || !is_string($usuario)) throw PageNotFoundException::forPageNotFound();

        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/meu-perfil') . '#alterar-senha', 303)->withCookies();
        $atual = $this->request->getPost('senha_atual');
        $nova = $this->request->getPost('nova_senha');
        $confirmacao = $this->request->getPost('confirmar_senha');
        if (!is_string($nova) || !mb_check_encoding($nova, 'UTF-8') || mb_strlen($nova) < 8 || strlen($nova) > 72 || str_contains($nova, "\0")) {
            return $voltar->with('erroSenha', 'Use pelo menos 8 caracteres e no máximo 72 bytes na nova senha.');
        }
        if (!preg_match('/\p{Lu}/u', $nova) || !preg_match('/\p{Nd}/u', $nova) || !preg_match('/[\p{P}\p{S}]/u', $nova)) {
            return $voltar->with('erroSenha', 'Inclua pelo menos uma letra maiúscula, um número e um caractere especial.');
        }
        if ($nova !== $confirmacao) return $voltar->with('erroSenha', 'A confirmação deve ser igual à nova senha.');

        if (!service('throttler')->check('perfil_senha_' . $usuario, 8, 60)) {
            return $voltar->with('erroSenha', 'Muitas tentativas. Aguarde um minuto e tente novamente.');
        }

        $escopo = ['id' => $usuario, 'status' => 'ativo'];
        try {
            $conta = $banco->table('usuarios')->select('senha_hash')->where($escopo)->get()->getRowArray();
            if (!$conta || !is_string($atual) || strlen($atual) > 72 || str_contains($atual, "\0")
                || !password_verify($atual, $conta['senha_hash'])) {
                return $voltar->with('erroSenha', 'A senha atual está incorreta.');
            }
            $banco->table('usuarios')->where($escopo + ['senha_hash' => $conta['senha_hash']])
                ->update(['senha_hash' => password_hash($nova, PASSWORD_DEFAULT), 'atualizado_em' => date('Y-m-d H:i:s')]);
            if ($banco->affectedRows() !== 1) {
                return $voltar->with('erroSenha', 'A senha foi alterada em outra sessão. Confira a senha atual e tente novamente.');
            }
        } catch (DatabaseException) {
            return $voltar->with('erroSenha', 'Não foi possível alterar a senha. Tente novamente.');
        }
        session()->regenerate(true);
        return $voltar->withCookies()->with('sucessoSenha', 'Senha alterada com sucesso.');
    }
}
