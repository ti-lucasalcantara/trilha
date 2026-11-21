<?php

namespace App\Controllers\Organizacao;

use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoEquipe;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Gerencia a seção de perfis e permissões da organização. */
class Perfis extends Configuracoes
{
    public function exibir(string $hash): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        $dados = (new GestaoEquipe($banco, (string) $contexto['empresa']))->dadosConfiguracoes();
        return $this->renderizarConfiguracao($hash, 'perfis', 'Perfis e permissões', $contexto, [
            'configuracoes' => $dados + [
                'perfis' => $contexto['vinculo'] === null ? null : site_url('organizacao/' . $hash . '/perfis'),
                'csrfNome' => csrf_token(), 'csrfValor' => csrf_hash(),
            ],
        ]);
    }

    /** Salva os dados e as permissões do perfil selecionado. */
    public function salvar(string $hash): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash);
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
    public function excluir(string $hash, string $id): ResponseInterface
    {
        $gestao = $this->abrirGestao($hash);
        $voltar = $this->voltar($hash);
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
        if ($contexto['vinculo'] === null) throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        return new GestaoEquipe($banco, (string) $contexto['empresa']);
    }

    private function voltar(string $hash): ResponseInterface
    {
        helper('url');
        return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes/perfis'), 303)->withCookies();
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
