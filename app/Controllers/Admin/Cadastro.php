<?php

namespace App\Controllers\Admin;

use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\HTTP\ResponseInterface;
use DomainException;

/** Implementa os fluxos comuns de consulta, formulário e gravação. */
abstract class Cadastro extends BaseAdmin
{
    protected const CADASTRO = '';
    protected const TITULO = '';
    protected const SINGULAR = '';
    protected const DESCRICAO = '';

    /** Exibe os registros atuais da página administrativa. */
    public function exibir(): ResponseInterface
    {
        $banco = $this->abrirBanco();

        return $this->renderizar(static::CADASTRO, $this->contexto(static::TITULO) + [
            'registros' => $banco->listar(static::CADASTRO),
        ]);
    }

    /** Abre o formulário de inclusão. */
    public function novo(): ResponseInterface
    {
        return $this->formulario($this->abrirBanco(), null, [
            'status' => 'ativo', 'pais' => 'BR', 'idioma' => 'pt-BR', 'fuso_horario' => 'America/Sao_Paulo',
        ]);
    }

    /** Exibe os dados editáveis de um cadastro existente. */
    public function editar(string $id): ResponseInterface
    {
        $banco = $this->abrirBanco();
        return $this->formulario($banco, $id, $banco->obter(static::CADASTRO, $id));
    }

    /** Valida e grava um cadastro novo ou existente. */
    public function salvar(?string $id = null): ResponseInterface
    {
        $banco = $this->abrirBanco();
        $anterior = $id === null ? [] : $banco->obter(static::CADASTRO, $id);
        $campos = $this->receberCampos();
        $validacao = service('validation');
        $regras = $this->regras($id);
        if (!$validacao->setRules($regras)->run($campos)) {
            return $this->formulario($banco, $id, $campos, $validacao->getErrors(), $anterior);
        }

        if (static::CADASTRO === 'planos') {
            [$inteiro, $decimal] = array_pad(explode('.', $campos['preco']), 2, '');
            $campos['preco'] = (ltrim($inteiro, '0') ?: '0') . '.' . str_pad($decimal, 2, '0');
        }

        try {
            $banco->salvar(static::CADASTRO, $id, $campos);
        } catch (DomainException $erro) {
            return $this->formulario($banco, $id, $campos, [$erro->getMessage()], $anterior);
        } catch (DatabaseException $erro) {
            return $this->formulario($banco, $id, $campos, [$this->mensagemBanco($erro)], $anterior);
        }

        return redirect()->to(site_url('admin/' . static::CADASTRO))->withCookies()->with('sucessoAdmin', 'Cadastro salvo.');
    }

    /** Exclui o cadastro mediante POST e respeita as dependências do esquema. */
    public function excluir(string $id): ResponseInterface
    {
        $banco = $this->abrirBanco();
        $destino = redirect()->to(site_url('admin/' . static::CADASTRO))->withCookies();
        try {
            $banco->excluir(static::CADASTRO, $id);
        } catch (DatabaseException $erro) {
            return $destino->with('erroAdmin', $this->mensagemBanco($erro));
        }

        return $destino->with('sucessoAdmin', 'Cadastro excluído.');
    }

    /** Lê apenas os campos previstos, sem repassar valores arbitrários ao banco. */
    private function receberCampos(): array
    {
        $nomes = match (static::CADASTRO) {
            'clientes' => ['nome_fantasia', 'pais', 'idioma', 'fuso_horario', 'plano_versao_id'],
            'planos' => ['preco', 'armazenamento_gb', 'usuarios'],
            'usuarios' => ['email', 'idioma', 'senha'],
        };
        $campos = [];
        foreach (array_merge(['nome', 'status'], $nomes) as $nome) {
            $valor = $this->request->getPost($nome);
            $campos[$nome] = is_string($valor) ? ($nome === 'senha' ? $valor : trim($valor)) : '';
        }
        if (static::CADASTRO === 'clientes') $campos['pais'] = strtoupper($campos['pais']);
        if (static::CADASTRO === 'planos') $campos['preco'] = str_replace(',', '.', $campos['preco']);
        if (static::CADASTRO === 'usuarios') {
            $campos['email'] = strtolower($campos['email']);
            $organizacoes = $this->request->getPost('organizacoes') ?? [];
            $campos['organizacoes'] = is_array($organizacoes) ? array_values(array_unique(array_filter($organizacoes, static fn ($valor) => is_string($valor) && preg_match('/^[1-9][0-9]{0,19}$/D', $valor)))) : [];
        }
        return $campos;
    }

    /** Define os formatos aceitos pelo formulário e pelo esquema. */
    private function regras(?string $id): array
    {
        $rotulo = static fn (string $nome, string $regras): array => ['label' => $nome, 'rules' => $regras];
        return ['nome' => $rotulo('Nome', 'required|max_length[' . (static::CADASTRO === 'planos' ? '150' : '200') . ']'),
            'status' => $rotulo('Situação', 'required|in_list[ativo,inativo]')] + match (static::CADASTRO) {
            'clientes' => [
                'nome_fantasia' => $rotulo('Nome fantasia', 'permit_empty|max_length[200]'),
                'pais' => $rotulo('País', 'required|regex_match[/^[A-Z]{2}$/D]'),
                'idioma' => $rotulo('Idioma', 'required|max_length[20]'),
                'fuso_horario' => $rotulo('Fuso horário', 'required|timezone|max_length[80]'),
                'plano_versao_id' => $rotulo('Plano', 'permit_empty|regex_match[/^[1-9][0-9]{0,19}$/D]'),
            ],
            'planos' => [
                'preco' => $rotulo('Preço', 'required|regex_match[/^[0-9]{1,17}(\.[0-9]{1,2})?$/D]'),
                'armazenamento_gb' => $rotulo('Armazenamento', 'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]'),
                'usuarios' => $rotulo('Usuários', 'required|integer|greater_than_equal_to[0]|less_than_equal_to[2147483647]'),
            ],
            'usuarios' => [
                'email' => $rotulo('E-mail', 'required|valid_email|max_length[254]'),
                'idioma' => $rotulo('Idioma', 'permit_empty|max_length[20]'),
                'senha' => ['label' => 'Senha', 'rules' => [($id === null ? 'required' : 'permit_empty'), 'min_length[8]', static function ($valor, $dados, &$erro): bool {
                    if (strlen($valor) <= 72 && !str_contains($valor, "\0")) return true;
                    $erro = 'A senha deve ter até 72 bytes e não pode conter caracteres nulos.';
                    return false;
                }]],
            ],
        };
    }

    /** Prepara as opções de relacionamento e mantém a senha fora da resposta. */
    private function formulario(\App\Libraries\Admin\Cadastros $banco, ?string $id, array $campos, array $erros = [], array $anterior = []): ResponseInterface
    {
        unset($campos['senha']);
        if ($erros) $this->response->setStatusCode(422);
        return $this->renderizar('formulario', $this->contexto(($id === null ? 'Novo cadastro' : 'Editar cadastro') . ' · ' . static::SINGULAR) + [
            'id' => $id,
            'campos' => $campos,
            'erros' => $erros,
            'planoVinculado' => $anterior['plano_versao_id'] ?? $campos['plano_versao_id'] ?? '',
            'planos' => static::CADASTRO === 'clientes' ? $banco->listar('planos') : [],
            'organizacoes' => static::CADASTRO === 'usuarios' ? $banco->listar('clientes') : [],
        ]);
    }

    /** Fornece os rótulos comuns ao layout e aos formulários. */
    private function contexto(string $titulo): array
    {
        return [
            'tituloPagina' => $titulo, 'descricaoPagina' => static::DESCRICAO, 'paginaAtiva' => static::CADASTRO,
            'cadastro' => static::CADASTRO, 'singular' => static::SINGULAR,
        ];
    }

    /** Apresenta falhas previsíveis sem expor consultas ou dados sensíveis. */
    private function mensagemBanco(DatabaseException $erro): string
    {
        return match ($erro->getCode()) {
            1062 => 'Já existe um cadastro com esse valor único. Confira os campos informados.',
            1451 => 'O cadastro possui vínculos ou histórico. Use a situação Inativo para desativá-lo.',
            1452 => 'Uma das referências selecionadas deixou de existir. Reabra o formulário e confira as opções.',
            default => 'A operação não pôde ser concluída. Tente novamente.',
        };
    }
}
