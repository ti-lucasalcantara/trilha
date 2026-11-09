<?php

namespace App\Controllers\Autenticacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use CodeIgniter\HTTP\ResponseInterface;

/** Autentica contas de organizações e abre o contexto de trabalho correto. */
class ControladorAcesso extends BaseController
{
    /** Exibe o formulário de entrada. */
    public function exibir(): ResponseInterface
    {
        return $this->renderizarEntrada();
    }

    /** Confere a senha e regenera a sessão antes de abrir a organização. */
    public function entrar(): ResponseInterface
    {
        helper(['url', 'form']);
        $chave = 'entrada_' . hash('sha256', $this->request->getIPAddress());
        if (!service('throttler')->check($chave, 10, 60)) {
            return $this->renderizarEntrada('Muitas tentativas. Aguarde um minuto e tente novamente.', 429);
        }

        $email = $this->request->getPost('email');
        $senha = $this->request->getPost('senha');
        if (!is_string($email) || !is_string($senha) || strlen($email) > 254 || strlen($senha) > 72 || str_contains($senha, "\0")) {
            return $this->renderizarEntrada('E-mail ou senha inválidos.', 401);
        }

        $banco = db_connect();
        $conta = $banco->table('usuarios')->select('id, senha_hash')->where(['email' => strtolower(trim($email)), 'status' => 'ativo'])->get()->getRowArray();
        $valida = password_verify($senha, $conta['senha_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$conta || !$valida) return $this->renderizarEntrada('E-mail ou senha inválidos.', 401);
        $vinculos = (new AcessoOrganizacao($banco))->vinculos((string) $conta['id']);
        if (!$vinculos) return $this->renderizarEntrada('Sua conta não possui vínculo ativo com uma organização.', 403);

        $sessao = session();
        $sessao->regenerate(true);
        $sessao->remove(SimulacaoEmpresa::SESSAO);
        $sessao->set(AcessoOrganizacao::SESSAO, (string) $conta['id']);
        if (count($vinculos) === 1) {
            return redirect()->to(site_url('organizacao/' . $vinculos[0]['hash'] . '/dashboard'))->withCookies();
        }
        return $this->response->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('autenticacao/organizacoes', ['vinculos' => $vinculos]));
    }

    /** Encerra a sessão autenticada. */
    public function sair(): ResponseInterface
    {
        helper('url');
        session()->destroy();
        return redirect()->to(site_url('login'))->withCookies();
    }

    /** Renderiza a tela sem permitir cache de credenciais ou avisos. */
    private function renderizarEntrada(?string $aviso = null, int $codigoHttp = 200): ResponseInterface
    {
        helper(['url', 'form']);

        return $this->response->setStatusCode($codigoHttp)->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('autenticacao/entrar', ['aviso' => $aviso]));
    }
}
