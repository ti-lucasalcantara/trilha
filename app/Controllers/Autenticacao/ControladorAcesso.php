<?php

namespace App\Controllers\Autenticacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use App\Libraries\Organizacao\FotoUsuario;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Autentica contas de organizações e abre o contexto de trabalho correto. */
class ControladorAcesso extends BaseController
{
    private const BLOQUEIO = 'conta_bloqueada_trilha';

    /** Exibe o formulário de entrada. */
    public function exibir(): ResponseInterface
    {
        helper('url');
        if (session()->has(self::BLOQUEIO)) return redirect()->to(site_url('bloqueio'))->withCookies();
        return $this->renderizarEntrada();
    }

    /** Confere a senha e regenera a sessão antes de abrir a organização. */
    public function entrar(): ResponseInterface
    {
        helper('url');
        if (session()->has(self::BLOQUEIO)) return redirect()->to(site_url('bloqueio'), 303)->withCookies();
        return $this->autenticar();
    }

    public function bloqueio(): ResponseInterface
    {
        helper('url');
        $conta = $this->contaBloqueada();
        return $conta ? $this->renderizarEntrada(bloqueada: $conta) : redirect()->to(site_url('login'))->withCookies();
    }

    public function desbloquear(): ResponseInterface
    {
        helper('url');
        $conta = $this->contaBloqueada();
        return $conta ? $this->autenticar($conta) : redirect()->to(site_url('login'), 303)->withCookies();
    }

    public function avatarBloqueado(): ResponseInterface
    {
        $usuario = session()->get(self::BLOQUEIO)['usuario'] ?? null;
        if (!is_string($usuario)) throw PageNotFoundException::forPageNotFound();
        return (new FotoUsuario(db_connect()))->responder($usuario, $this->response);
    }

    public function trocarUsuario(): ResponseInterface
    {
        helper('url');
        $sessao = session();
        if ($sessao->has(self::BLOQUEIO)) {
            $sessao->remove(self::BLOQUEIO);
            $sessao->regenerate(true);
        }
        return redirect()->to(site_url('login'), 303)->withCookies();
    }

    private function autenticar(?array $bloqueada = null): ResponseInterface
    {
        helper(['url', 'form']);
        $chave = 'entrada_' . hash('sha256', $this->request->getIPAddress());
        if (!service('throttler')->check($chave, 10, 60)) {
            return $this->renderizarEntrada('Muitas tentativas. Aguarde um minuto e tente novamente.', 429, $bloqueada);
        }

        $email = $this->request->getPost('email');
        $senha = $this->request->getPost('senha');
        $mensagemInvalida = $bloqueada ? 'Senha incorreta. Tente novamente.' : 'E-mail ou senha inválidos.';
        if (($bloqueada === null && (!is_string($email) || strlen($email) > 254))
            || !is_string($senha) || strlen($senha) > 72 || str_contains($senha, "\0")) {
            return $this->renderizarEntrada($mensagemInvalida, 401, $bloqueada);
        }

        $banco = db_connect();
        $conta = $bloqueada ?? $banco->table('usuarios')->select('id, senha_hash')->where(['email' => strtolower(trim($email)), 'status' => 'ativo'])->get()->getRowArray();
        $valida = password_verify($senha, $conta['senha_hash'] ?? '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi.');
        if (!$conta || !$valida) return $this->renderizarEntrada($mensagemInvalida, 401, $bloqueada);
        $vinculos = (new AcessoOrganizacao($banco))->vinculos((string) $conta['id']);
        if (!$vinculos) return $this->renderizarEntrada('Sua conta não possui vínculo ativo com uma organização.', 403, $bloqueada);

        $sessao = session();
        $sessao->regenerate(true);
        $sessao->remove([SimulacaoEmpresa::SESSAO, self::BLOQUEIO]);
        $sessao->set(AcessoOrganizacao::SESSAO, (string) $conta['id']);
        $organizacao = $bloqueada['organizacao'] ?? null;
        if (count($vinculos) === 1 || in_array($organizacao, array_column($vinculos, 'hash'), true)) {
            $destino = count($vinculos) === 1 ? $vinculos[0]['hash'] : $organizacao;
            return redirect()->to(site_url('organizacao/' . $destino . '/escolher-setor'), 303)->withCookies();
        }
        return $this->response->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('autenticacao/organizacoes', ['vinculos' => $vinculos]));
    }

    public function escolherSetor(string $hash): ResponseInterface
    {
        return $this->entradaPorSetor($hash);
    }

    public function confirmarSetor(string $hash): ResponseInterface
    {
        return $this->entradaPorSetor($hash, true);
    }

    private function entradaPorSetor(string $hash, bool $confirmar = false): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
        if ($contexto['simulacao']) throw PageNotFoundException::forPageNotFound();

        $setores = new ContextoSetor($banco, (string) $contexto['empresa'], $contexto['fundador'] ? null : (string) $contexto['vinculo']);
        $opcoes = $setores->obter();
        $base = site_url('organizacao/' . $hash);
        $painel = redirect()->to($base . '/dashboard', 303)->withCookies();
        if (!$opcoes['setores']) return $painel;
        if (!$confirmar && count($opcoes['setores']) === 1) {
            $unico = reset($opcoes['setores']);
            if ($setores->selecionar($unico['unidade'], $unico['id'])) return $painel;
            return redirect()->to($base . '/escolher-setor', 303)->withCookies();
        }

        if ($confirmar) {
            $id = $this->request->getPost('setor');
            $setor = is_string($id) ? ($opcoes['setores'][$id] ?? null) : null;
            if ($setor && $setores->selecionar($setor['unidade'], $setor['id'])) return $painel;
            return redirect()->to($base . '/escolher-setor', 303)->withCookies()
                ->with('erroEscolhaSetor', 'Selecione um setor disponível para continuar.');
        }

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('autenticacao/setores', [
            'organizacao' => $contexto['organizacao'], 'unidades' => $opcoes['unidades'],
            'acao' => $base . '/escolher-setor',
        ]));
    }

    /** Encerra o acesso e retém somente a identificação usada no bloqueio. */
    public function sair(): ResponseInterface
    {
        helper('url');
        $sessao = session();
        $usuario = $sessao->get(AcessoOrganizacao::SESSAO);
        if (!is_string($usuario)) return redirect()->to(site_url('login'), 303)->withCookies();

        $organizacao = $this->request->getPost('organizacao');
        $sessao->remove(array_keys($_SESSION ?? []));
        $sessao->regenerate(true);
        $sessao->set(self::BLOQUEIO, [
            'usuario' => $usuario,
            'organizacao' => is_string($organizacao) && preg_match('/^[a-f0-9]{48}$/D', $organizacao) ? $organizacao : null,
        ]);
        return redirect()->to(site_url('bloqueio'), 303)->withCookies();
    }

    private function contaBloqueada(): ?array
    {
        $sessao = session();
        $bloqueio = $sessao->get(self::BLOQUEIO);
        if (!isset($bloqueio['usuario'])) return null;
        $conta = db_connect()->table('usuarios')->select('id, nome, senha_hash')
            ->select('avatar_jpeg IS NOT NULL AS tem_avatar', false)
            ->where(['id' => $bloqueio['usuario'], 'status' => 'ativo'])->get()->getRowArray();
        if (!$conta) {
            $sessao->remove(self::BLOQUEIO);
            return null;
        }
        return $conta + ['organizacao' => $bloqueio['organizacao']];
    }

    /** Renderiza a tela sem permitir cache de credenciais ou avisos. */
    private function renderizarEntrada(?string $aviso = null, int $codigoHttp = 200, ?array $bloqueada = null): ResponseInterface
    {
        helper(['url', 'form']);

        return $this->response->setStatusCode($codigoHttp)->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('autenticacao/entrar', [
                'aviso' => $aviso, 'nomeContaBloqueada' => $bloqueada['nome'] ?? null,
                'avatarContaBloqueada' => !empty($bloqueada['tem_avatar']),
            ]));
    }
}
