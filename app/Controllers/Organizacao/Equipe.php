<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoEquipe;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Recebe alterações de equipe e perfis do fundador autenticado. */
class Equipe extends BaseController
{
    /** Cadastra um usuário interno com um perfil da mesma empresa. */
    public function salvar(string $hash): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash, 'equipe');
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

    /** Salva os dados e as permissões do perfil selecionado. */
    public function salvarPerfil(string $hash): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash, 'perfis');
        $id = $this->texto('id');
        $nome = $this->texto('nome');
        $descricao = $this->texto('descricao');
        $permissoes = $this->request->getPost('permissoes') ?? [];
        if (($id !== '' && !$this->identificadorValido($id)) || !$nome || mb_strlen($nome) > 150
            || strlen($descricao) > 4000 || !is_array($permissoes)
            || array_filter($permissoes, fn ($valor) => !is_string($valor) || !$this->identificadorValido($valor))) {
            return $voltar->with('erroSimulacao', 'Confira os dados e as permissões do perfil.');
        }
        try {
            $gestao->salvarPerfil($id, $nome, $descricao, array_values(array_unique($permissoes)));
        } catch (DomainException $erro) {
            return $voltar->with('erroSimulacao', $erro->getMessage());
        } catch (DatabaseException $erro) {
            return $voltar->with('erroSimulacao', $erro->getCode() === 1062 ? 'Já existe um perfil com esse nome.' : 'Não foi possível salvar o perfil.');
        }
        return $voltar->with('sucessoSimulacao', 'Perfil salvo.');
    }

    /** Exclui apenas perfis comuns da organização autenticada. */
    public function excluirPerfil(string $hash, string $id): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash, 'perfis');
        try {
            $gestao->excluirPerfil($id);
        } catch (DomainException $erro) {
            return $voltar->with('erroSimulacao', $erro->getMessage());
        } catch (DatabaseException) {
            return $voltar->with('erroSimulacao', 'Não foi possível excluir o perfil. Confira seus vínculos.');
        }
        return $voltar->with('sucessoSimulacao', 'Perfil excluído.');
    }

    /** Resolve a empresa e exige o vínculo de fundador em toda gravação. */
    private function abrirGestao(string $hash): GestaoEquipe
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        return new GestaoEquipe($banco, (string) $contexto['empresa']);
    }

    private function voltar(string $hash, string $modulo): ResponseInterface
    {
        helper('url');
        return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes') . '#' . ($modulo === 'perfis' ? 'permissoes' : $modulo))->withCookies();
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
