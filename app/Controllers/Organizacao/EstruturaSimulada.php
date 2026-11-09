<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\EstruturaSimulada as CadastroEstrutura;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;

/** Recebe as alterações autorizadas na estrutura durante a simulação local. */
class EstruturaSimulada extends BaseController
{
    /** Valida os campos e usa a empresa da sessão, nunca a empresa enviada pelo formulário. */
    public function salvar(string $hash): ResponseInterface
    {
        $contexto = SimulacaoEmpresa::contexto($this->request, $hash, true);
        return $this->gravar($hash, $contexto['empresa']);
    }

    /** Permite a mesma edição ao fundador autenticado da empresa. */
    public function salvarAutenticada(string $hash): ResponseInterface
    {
        $contexto = (new AcessoOrganizacao(db_connect()))->contexto($hash, true);
        return $this->gravar($hash, (string) $contexto['empresa']);
    }

    /** Compartilha a validação entre o acesso real e a simulação local. */
    private function gravar(string $hash, string $empresa): ResponseInterface
    {
        helper('url');
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/configuracoes') . '#estrutura')->withCookies();
        $campos = [];
        foreach (['tabela', 'id', 'nome', 'sigla', 'status', 'unidade'] as $campo) {
            $valor = $this->request->getPost($campo);
            if ($valor !== null && !is_string($valor)) return $voltar->with('erroSimulacao', 'Confira os campos informados.');
            $campos[$campo] = trim($valor ?? '');
        }
        $validacao = service('validation');
        $validacao->setRules([
            'tabela' => 'required|in_list[unidades,setores]',
            'id' => 'permit_empty|regex_match[/^[1-9][0-9]{0,19}$/D]',
            'nome' => 'required|max_length[200]',
            'sigla' => 'permit_empty|max_length[40]',
            'status' => 'required|in_list[ativo,inativo]',
            'unidade' => 'permit_empty|regex_match[/^[1-9][0-9]{0,19}$/D]',
        ]);
        if (!$validacao->run($campos)) return $voltar->with('erroSimulacao', 'Confira o nome, a sigla e a situação do cadastro.');

        try {
            (new CadastroEstrutura(db_connect(), $empresa))->salvar($campos['tabela'], $campos['id'], [
                'nome' => $campos['nome'], 'sigla' => $campos['sigla'] ?: null, 'status' => $campos['status'],
            ], $campos['unidade']);
        } catch (DatabaseException) {
            return $voltar->with('erroSimulacao', 'A estrutura mudou ou o cadastro não pôde ser salvo. Confira os dados e tente novamente.');
        }
        return $voltar->with('sucessoSimulacao', 'Alteração gravada no banco da organização.');
    }
}
