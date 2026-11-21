<?php

namespace App\Controllers\Organizacao;

use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\GestaoSituacoesProcesso;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Recebe alterações das situações pela administração da organização. */
class SituacoesProcesso extends Configuracoes
{
    public function exibir(string $hash): ResponseInterface
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        return $this->renderizarConfiguracao($hash, 'situacoes-processo', 'Situações de processo', $contexto, [
            'situacoesProcesso' => (new GestaoSituacoesProcesso($banco, (string) $contexto['empresa']))->listar(),
            'edicaoSituacaoProcesso' => $this->request->getGet('situacao'),
            'exclusaoSituacaoProcesso' => $this->request->getGet('excluir_situacao'),
        ]);
    }

    /** Valida os dados do cadastro e retorna à aba após gravar. */
    public function salvar(string $hash): ResponseInterface
    {
        $gestao = $this->autorizar($hash);
        $dados = [];
        foreach (['id', 'nome', 'descricao', 'cor', 'ordem', 'ativo'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        $id = $dados['id'];
        $idValido = $id === '' || preg_match('/^[1-9][0-9]{0,19}$/D', $id);
        try {
            if (!$idValido || $dados['nome'] === '' || mb_strlen($dados['nome']) > 100 || strlen($dados['descricao']) > 65535
                || ($dados['cor'] !== '' && !preg_match('/^#[0-9a-f]{6}$/iD', $dados['cor']))
                || filter_var($dados['ordem'], FILTER_VALIDATE_INT) === false || !in_array($dados['ativo'], ['0', '1'], true)) {
                throw new DomainException('Confira o nome, a ordem e a cor no formato #RRGGBB.');
            }
            $gestao->salvar($id, [
                'nome' => $dados['nome'], 'descricao' => $dados['descricao'] === '' ? null : $dados['descricao'],
                'cor' => $dados['cor'] === '' ? null : $dados['cor'], 'ordem' => (int) $dados['ordem'], 'ativo' => (int) $dados['ativo'],
            ]);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage()
                : ($erro->getCode() === 1062 ? 'Já existe uma situação com esse nome.' : 'Não foi possível salvar a situação.');
            return $this->voltar($hash, $idValido && $id !== '' ? $id : 'nova')
                ->with('dadosSituacaoProcesso', $dados)->with('erroSituacaoProcesso', $mensagem);
        }
        return $this->voltar($hash)->with('sucessoSituacaoProcesso', $id === '' ? 'Situação cadastrada.' : 'Situação atualizada.');
    }

    public function definirInicial(string $hash): ResponseInterface
    {
        $gestao = $this->autorizar($hash);
        $id = $this->request->getPost('situacao_inicial');
        try {
            if (!is_string($id) || !preg_match('/^[1-9][0-9]{0,19}$/D', $id)) {
                throw new DomainException('Selecione uma situação ativa para a abertura.');
            }
            $gestao->definirInicial($id);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage() : 'Não foi possível definir a situação inicial.';
            return $this->voltar($hash)->with('erroSituacaoProcesso', $mensagem);
        }
        return $this->voltar($hash)->with('sucessoSituacaoProcesso', 'Situação inicial definida.');
    }

    /** Exclui uma situação sem vínculos ou orienta a inativação. */
    public function excluir(string $hash, string $id): ResponseInterface
    {
        $gestao = $this->autorizar($hash);
        try {
            $gestao->excluir($id);
        } catch (DomainException | DatabaseException $erro) {
            $mensagem = $erro instanceof DomainException ? $erro->getMessage()
                : ($erro->getCode() === 1451 ? 'Esta situação está em uso. Inative-a para preservar o histórico.' : 'Não foi possível excluir a situação.');
            return $this->voltar($hash)->with('erroSituacaoProcesso', $mensagem);
        }
        return $this->voltar($hash)->with('sucessoSituacaoProcesso', 'Situação excluída.');
    }

    /** Exige o mesmo acesso administrativo usado na página de Configurações. */
    private function autorizar(string $hash): GestaoSituacoesProcesso
    {
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash, true);
        return new GestaoSituacoesProcesso($banco, (string) $contexto['empresa']);
    }

    /** Mantém a aba selecionada após operações de gravação. */
    private function voltar(string $hash, ?string $edicao = null): ResponseInterface
    {
        helper('url');
        $consulta = $edicao === null ? '' : '?' . http_build_query(['situacao' => $edicao]);
        return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes/situacoes-processo') . $consulta, 303)->withCookies();
    }
}
