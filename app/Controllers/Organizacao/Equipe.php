<?php

namespace App\Controllers\Organizacao;

use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoEquipe;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Gerencia a seção de equipe da organização. */
class Equipe extends Configuracoes
{
    public function exibir(string $hash): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        $dados = (new GestaoEquipe($banco, (string) $contexto['empresa']))->dadosConfiguracoes();
        return $this->renderizarConfiguracao($hash, 'equipe', 'Equipe', $contexto, [
            'configuracoes' => $dados + [
                'equipe' => $contexto['vinculo'] === null ? null : site_url('organizacao/' . $hash . '/equipe'),
                'csrfNome' => csrf_token(), 'csrfValor' => csrf_hash(),
            ],
        ]);
    }

    /** Cadastra um usuário interno com um perfil da mesma empresa. */
    public function salvar(string $hash): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash);
        $nome = $this->texto('nome');
        $email = strtolower($this->texto('email'));
        $senha = $this->request->getPost('senha');
        $perfil = $this->texto('perfil');
        if (!$nome || mb_strlen($nome) > 200 || strlen($email) > 254 || !filter_var($email, FILTER_VALIDATE_EMAIL)
            || !is_string($senha) || strlen($senha) < 8 || strlen($senha) > 72 || str_contains($senha, "\0") || !$this->identificadorValido($perfil)) {
            return $voltar->with('erroSimulacao', 'Confira nome, e-mail, senha e perfil.');
        }

        try {
            $gestao->cadastrarUsuario($nome, $email, $senha, $perfil);
        } catch (DomainException $erro) {
            return $voltar->with('erroSimulacao', $erro->getMessage());
        } catch (DatabaseException $erro) {
            return $voltar->with('erroSimulacao', $erro->getCode() === 1062 ? 'O e-mail já está cadastrado. Solicite o vínculo pelo Admin do Trilha.' : 'Não foi possível cadastrar o usuário.');
        }
        return $voltar->with('sucessoSimulacao', 'Usuário cadastrado com o perfil selecionado.');
    }

    /** Resolve a empresa e exige o vínculo de fundador em toda gravação. */
    private function abrirGestao(string $hash): GestaoEquipe
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        if ($contexto['vinculo'] === null) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return new GestaoEquipe($banco, (string) $contexto['empresa']);
    }

    private function voltar(string $hash): ResponseInterface
    {
        helper('url');
        return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes/equipe'), 303)->withCookies();
    }

    private function texto(string $campo): string
    {
        $valor = $this->request->getPost($campo);
        return is_string($valor) ? trim($valor) : '';
    }

    private function identificadorValido(string $valor): bool
    {
        return (bool) preg_match('/^[1-9][0-9]{0,19}$/D', $valor);
    }
}
