<?php

namespace App\Controllers\Organizacao;

use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoTiposDocumento;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

class TiposDocumento extends Configuracoes
{
    public function exibir(string $hash): ResponseInterface
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        $tipos = (new GestaoTiposDocumento($banco, (string) $contexto['empresa']))->listar();
        $edicao = $this->request->getGet('tipo');
        $tipo = null;
        if ($edicao === 'novo') {
            $tipo = ['id' => '', 'nome' => '', 'sigla' => '', 'descricao' => '', 'ativo' => 1];
        } elseif ($edicao !== null) {
            $tipo = is_string($edicao) ? (array_column($tipos, null, 'id')[$edicao] ?? null) : null;
            if (!$tipo) throw PageNotFoundException::forPageNotFound();
        }
        return $this->renderizarConfiguracao($hash, 'tipos-documento', 'Tipos de Documentos', $contexto, [
            'tiposDocumento' => $tipos, 'tipoDocumentoEditado' => $tipo,
        ]);
    }

    public function salvar(string $hash): ResponseInterface
    {
        helper('url');
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        $endereco = site_url('organizacao/' . $hash . '/configuracoes/tipos-documento');
        $dados = [];
        foreach (['id', 'nome', 'sigla', 'descricao', 'ativo'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        $id = $dados['id'];
        if ($id !== '' && !preg_match('/^[1-9][0-9]{0,19}$/D', $id)) throw PageNotFoundException::forPageNotFound();
        try {
            if ($dados['nome'] === '' || mb_strlen($dados['nome']) > 150 || $dados['sigla'] === ''
                || mb_strlen($dados['sigla']) > 40 || strlen($dados['descricao']) > 65535 || !in_array($dados['ativo'], ['0', '1'], true)) {
                throw new DomainException('Confira o nome, a sigla e a disponibilidade do tipo.');
            }
            $autor = $contexto['vinculo'] !== null ? (string) $contexto['vinculo'] : null;
            (new GestaoTiposDocumento($banco, (string) $contexto['empresa']))->salvar($id, [
                'nome' => $dados['nome'], 'sigla' => $dados['sigla'], 'descricao' => $dados['descricao'] ?: null,
                'ativo' => (int) $dados['ativo'],
            ], $autor);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage()
                : ($erro->getCode() === 1062 ? 'Já existe um tipo de documento com esse nome.' : 'Não foi possível salvar o tipo de documento.');
            return redirect()->to($endereco . '?tipo=' . ($id ?: 'novo'), 303)->withCookies()
                ->withInput()->with('erroTipoDocumento', $mensagem);
        }
        return redirect()->to($endereco, 303)->withCookies()
            ->with('sucessoTipoDocumento', $id === '' ? 'Tipo de documento cadastrado.' : 'Tipo de documento atualizado.');
    }
}
