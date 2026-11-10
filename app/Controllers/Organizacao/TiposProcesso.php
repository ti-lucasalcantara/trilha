<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoTiposProcesso;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Recebe gravações do catálogo pela administração da organização. */
class TiposProcesso extends BaseController
{
    /** Valida os campos e grava o tipo e sua máscara. */
    public function salvar(string $hash): ResponseInterface
    {
        [$gestao, $autor] = $this->autorizar($hash);
        $dados = [];
        foreach (['id', 'nome', 'sigla', 'descricao', 'mascara', 'sigla_numeracao', 'digitos_minimos', 'ativo'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        $id = $dados['id'];
        $idValido = $id === '' || preg_match('/^[1-9][0-9]{0,19}$/D', $id);
        try {
            if (!$idValido || $dados['nome'] === '' || mb_strlen($dados['nome']) > 150
                || $dados['sigla'] === '' || mb_strlen($dados['sigla']) > 40
                || mb_strlen($dados['sigla_numeracao']) > 40 || strlen($dados['descricao']) > 65535
                || mb_strlen($dados['mascara']) > 200 || !str_contains($dados['mascara'], '{ano}')
                || !preg_match('/^(?:0|[1-9][0-9]{0,2})$/D', $dados['digitos_minimos'])
                || (int) $dados['digitos_minimos'] > 200 || !in_array($dados['ativo'], ['0', '1'], true)) {
                throw new DomainException('Confira nome, sigla e dígitos mínimos. A máscara anual precisa conter {ano}.');
            }
            $dados['sigla_numeracao'] = $dados['sigla_numeracao'] === '' ? $dados['sigla'] : $dados['sigla_numeracao'];
            $gestao->salvar($id, $dados, $autor);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage()
                : ($erro->getCode() === 1062 ? 'Já existe um tipo com esse nome.' : 'Não foi possível salvar o tipo de processo.');
            return $this->voltar($hash, $idValido && $id !== '' ? $id : 'novo')
                ->with('dadosTipoProcesso', $dados)->with('erroTipoProcesso', $mensagem);
        }
        return $this->voltar($hash)->with('sucessoTipoProcesso', $id === '' ? 'Tipo de processo cadastrado.' : 'Tipo de processo atualizado.');
    }

    /** Exclui o tipo quando seus vínculos permitem. */
    public function excluir(string $hash, string $id): ResponseInterface
    {
        [$gestao] = $this->autorizar($hash);
        try {
            $gestao->excluir($id);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage()
                : 'Este tipo tem configurações vinculadas. Inative-o para preservar os dados.';
            return $this->voltar($hash)->with('erroTipoProcesso', $mensagem);
        }
        return $this->voltar($hash)->with('sucessoTipoProcesso', 'Tipo de processo excluído.');
    }

    /** Reaproveita as permissões administrativas da tela de Configurações. */
    private function autorizar(string $hash): array
    {
        $banco = db_connect();
        $simulando = (session()->get(SimulacaoEmpresa::SESSAO)['hash'] ?? null) === $hash;
        $contexto = $simulando
            ? SimulacaoEmpresa::contexto($this->request, $hash, true)
            : (new AcessoOrganizacao($banco))->contexto($hash, true);
        return [new GestaoTiposProcesso($banco, (string) $contexto['empresa']), $simulando ? null : (string) $contexto['vinculo']];
    }

    /** Retorna à aba do catálogo após o envio do formulário. */
    private function voltar(string $hash, ?string $edicao = null): ResponseInterface
    {
        helper('url');
        $consulta = $edicao === null ? '' : '?' . http_build_query(['tipo' => $edicao]);
        return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes') . $consulta . '#tipos-processo', 303)->withCookies();
    }
}
