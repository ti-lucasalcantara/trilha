<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\AberturaProcesso;
use App\Libraries\Organizacao\EdicaoProcesso;
use App\Libraries\Organizacao\EnvioDocumento;
use App\Libraries\Organizacao\EditorDocumento;
use App\Libraries\Organizacao\ConsultaProcessos;
use App\Libraries\Organizacao\ContextoSetor;
use App\Libraries\Organizacao\PoliticaProcesso;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DomainException;
use DateTimeImmutable;
use DateTimeZone;
use CodeIgniter\HTTP\ResponseInterface;

/** Gerencia os processos da organização acessível na sessão. */
class Processos extends BaseController
{
    private const ACOES_POR_CAIXA = [
        'ativos' => ['encaminhar', 'mover', 'arquivar', 'excluir'],
        'arquivados' => ['restaurar', 'excluir'],
        'lixeira' => ['restaurar'],
    ];

    /** Valida a caixa, os processos e a autoria antes de executar a operação em lote. */
    public function executarLote(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $caixa = $this->request->getPost('caixa');
        $caixa = is_string($caixa) && isset(self::ACOES_POR_CAIXA[$caixa]) ? $caixa : 'ativos';
        $endereco = site_url('organizacao/' . $hash . '/processos');
        $voltar = redirect()->to($endereco . ($caixa === 'ativos' ? '' : '?caixa=' . $caixa), 303)->withCookies();
        $acao = $this->request->getPost('acao');
        $hashes = $this->request->getPost('processos');
        $destino = $this->request->getPost('destino') ?? '';
        $setoresDestino = $acao === 'encaminhar' ? ($this->request->getPost('setor_destino') ?? []) : [];
        $manter = $acao === 'encaminhar' ? ($this->request->getPost('manter') ?? []) : [];
        $setor = $layout['contextoTrabalho']['setor']['id'] ?? null;
        if ($setor === null || $this->request->getPost('setor_contexto') !== $setor || !in_array($acao, self::ACOES_POR_CAIXA[$caixa], true)
            || !is_array($hashes) || !$hashes || count($hashes) > 20
            || array_filter($hashes, static fn ($item) => !is_string($item) || !preg_match('/^[a-f0-9]{48}$/D', $item))
            || ($acao === 'mover' && (!is_string($destino) || !preg_match('/^[1-9][0-9]{0,19}$/D', $destino)))
            || ($acao === 'encaminhar' && (!is_array($setoresDestino) || !$setoresDestino
                || array_filter($setoresDestino, static fn ($id) => !is_string($id) || !preg_match('/^[1-9][0-9]{0,19}$/D', $id))))
            || !is_array($manter) || array_filter($manter, static fn ($item) => !in_array($item, $hashes, true))) {
            return $voltar->with('erroPastaProcesso', 'Confira a seleção de processos, a ação e o setor ativo.');
        }
        $autor = $vinculo;
        if ($autor === null) {
            $responsavel = $this->request->getPost('responsavel');
            $usuarios = array_column((new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios(), null, 'vinculo_id');
            if (!is_string($responsavel) || !isset($usuarios[$responsavel])) {
                return $voltar->with('erroPastaProcesso', 'Selecione um usuário interno ativo.');
            }
            $autor = $responsavel;
        }
        try {
            $consulta->executarLote(array_values(array_unique($hashes)), $acao, $caixa,
                match ($acao) { 'mover' => $destino, 'encaminhar' => array_values(array_unique($setoresDestino)), default => '' },
                $vinculo, $autor, $manter);
            $mensagens = [
                'mover' => 'Seleção movida para a pasta de destino.', 'arquivar' => 'Seleção enviada para Arquivados.',
                'excluir' => 'Seleção enviada para a Lixeira.', 'restaurar' => 'Seleção restaurada.',
                'encaminhar' => count($setoresDestino) === 1 ? 'Processos encaminhados ao setor de destino.' : 'Processos encaminhados aos setores selecionados.',
            ];
            return $voltar->with('sucessoPastaProcesso', $mensagens[$acao]);
        } catch (DomainException $erro) {
            return $voltar->with('erroPastaProcesso', $erro->getMessage());
        } catch (DatabaseException $erro) {
            log_message('error', 'Falha na operação em lote: {mensagem}', ['mensagem' => $erro->getMessage()]);
            return $voltar->with('erroPastaProcesso', 'Não foi possível concluir a ação em lote.');
        }
    }

    /** Grava o estado desejado de acompanhamento para o vínculo da sessão. */
    public function acompanhar(string $hash, string $processo): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, , $vinculo] = $this->contextoReal($hash, false);
        $estado = $this->request->getPost('acompanhar');
        if (!in_array($estado, ['0', '1'], true)) {
            return $this->response->setStatusCode(422)->setJSON(['erro' => 'Estado de acompanhamento inválido.', 'csrf' => csrf_hash()]);
        }
        $acompanhado = $estado === '1';
        try {
            if ($vinculo !== null) {
                $consulta->salvarAcompanhamento($processo, $vinculo, $acompanhado);
            } else {
                // A simulação administrativa não representa um vínculo pessoal.
                if (!$consulta->listar(null, $processo)) throw new DomainException('Processo indisponível neste setor.');
                $chave = 'acompanhamentos_simulados_' . $hash;
                $marcados = session()->get($chave) ?? [];
                if ($acompanhado) $marcados[$processo] = true;
                else unset($marcados[$processo]);
                session()->set($chave, $marcados);
            }
        } catch (DomainException $erro) {
            return $this->response->setStatusCode(422)->setJSON(['erro' => $erro->getMessage(), 'csrf' => csrf_hash()]);
        } catch (DatabaseException) {
            return $this->response->setStatusCode(409)->setJSON(['erro' => 'Não foi possível atualizar o acompanhamento.', 'csrf' => csrf_hash()]);
        }
        return $this->response->setJSON(['acompanhado' => $acompanhado, 'csrf' => csrf_hash()]);
    }

    /** Exibe a página de abertura com os cadastros e o setor da organização real. */
    public function novo(string $hash): ResponseInterface
    {
        [, $layout, $vinculo] = $this->contextoReal($hash);
        return $this->formularioProcesso($layout, $vinculo);
    }

    public function editar(string $hash, string $processo): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $edicao = (new EdicaoProcesso(db_connect(), $layout['empresaSimulada'], $consulta))->obter($processo, $vinculo);
        return $this->formularioProcesso($layout, $vinculo, $edicao);
    }

    private function formularioProcesso(array $layout, ?string $vinculo, ?array $edicao = null, string $confirmacao = ''): ResponseInterface
    {
        $banco = db_connect();
        $empresa = $layout['empresaSimulada'];
        $opcoes = $edicao === null ? (new AberturaProcesso($banco, $empresa))->opcoes() : [];
        $usuarios = (new ConsultaEmpresa($banco, $empresa))->usuarios();
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos/novo', $layout + $opcoes + [
            'tituloPagina' => $edicao === null ? 'Novo processo' : 'Editar processo',
            'edicao' => $edicao,
            'confirmacao' => $confirmacao,
            'responsaveis' => $vinculo === null ? $usuarios : [],
            'pessoasAcesso' => $usuarios,
            'vinculoLogado' => $vinculo,
            'niveisAcesso' => PoliticaProcesso::NIVEIS,
        ]));
    }

    /** Valida a abertura e redireciona para o processo gravado. */
    public function abrir(string $hash): ResponseInterface
    {
        return $this->gravarProcesso($hash);
    }

    public function atualizar(string $hash, string $processo): ResponseInterface
    {
        return $this->gravarProcesso($hash, $processo);
    }

    private function gravarProcesso(string $hash, ?string $processoEditado = null): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $leitor = $vinculo;
        $endereco = site_url('organizacao/' . $hash . '/processos');
        $banco = db_connect();
        $empresa = $layout['empresaSimulada'];
        $edicao = $processoEditado === null ? null : new EdicaoProcesso($banco, $empresa, $consulta);
        $edicaoAjax = $edicao !== null && $this->request->isAJAX();
        if ($edicao !== null) $edicao->obter($processoEditado, $leitor);
        $voltar = redirect()->to($endereco . ($edicao === null ? '/novo' : '/' . $processoEditado . '/editar'), 303)->withCookies();
        $dados = [];
        foreach (['tipo', 'assunto', 'descricao', 'descricao_formatada', 'setor_contexto', 'responsavel', 'acesso', 'revisao'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        try {
            $setor = $layout['contextoTrabalho']['setor'];
            if (!$setor || $setor['id'] !== $dados['setor_contexto']) {
                throw new DomainException('O setor ativo mudou. Confira a origem do processo e tente novamente.');
            }
            if ($edicao === null && !preg_match('/^[1-9][0-9]{0,19}$/D', $dados['tipo'])) {
                throw new DomainException('Selecione o tipo de processo.');
            }
            if ($dados['assunto'] === '' || mb_strlen($dados['assunto']) > 500) {
                throw new DomainException('Informe um assunto com até 500 caracteres.');
            }
            if (strlen($dados['descricao']) > 65535 || strlen($dados['descricao_formatada']) > 65535) {
                throw new DomainException('A descrição excede o tamanho permitido.');
            }
            $usuarios = array_column((new ConsultaEmpresa($banco, $empresa))->usuarios(), null, 'vinculo_id');
            if (!isset(PoliticaProcesso::NIVEIS[$dados['acesso']])) throw new DomainException('Selecione o nível de acesso.');
            if ($vinculo === null) {
                if (!isset($usuarios[$dados['responsavel']])) {
                    throw new DomainException('Selecione um usuário interno ativo para registrar a abertura.');
                }
                $vinculo = $dados['responsavel'];
            }
            $dados['autorizados'] = [];
            $permitidos = [];
            if ($dados['acesso'] !== 'interno') {
                $restrito = $dados['acesso'] === 'restrito';
                $selecionados = $this->request->getPost($restrito ? 'setores_autorizados' : 'pessoas_autorizadas') ?? [];
                $permitidos = $restrito ? $layout['contextoTrabalho']['setores'] : $usuarios;
                if (!is_array($selecionados)) {
                    throw new DomainException('Confira as autorizações selecionadas para esta organização.');
                }
                foreach ($selecionados as $selecionado) {
                    if (!is_string($selecionado) || !isset($permitidos[$selecionado])) {
                        throw new DomainException('Confira as autorizações selecionadas para esta organização.');
                    }
                    $dados['autorizados'][$selecionado] = $selecionado;
                }
                $autorizadoPadrao = $restrito ? $setor['id'] : $vinculo;
                $dados['autorizados'][$autorizadoPadrao] = $autorizadoPadrao;
            }
            if ($edicao !== null) {
                $edicaoAtual = $edicao->salvar($processoEditado, $leitor, $vinculo, $dados, $permitidos);
                $mensagem = $edicaoAtual ? 'Alterações salvas.' : 'Alterações salvas. O processo não está mais disponível para edição neste contexto.';
                if ($edicaoAjax) {
                    return $this->response->setJSON([
                        'mensagem' => $mensagem, 'revisao' => $edicaoAtual['revisao'] ?? null, 'csrf' => csrf_hash(),
                    ]);
                }
                return $edicaoAtual
                    ? $this->formularioProcesso($layout, $leitor, $edicaoAtual, $mensagem)
                    : $this->response->setContentType('text/plain')->setBody($mensagem);
            }
            $ano = (int) (new DateTimeImmutable('now', new DateTimeZone($layout['fusoOrganizacao'])))->format('Y');
            $processo = (new AberturaProcesso($banco, $empresa))->abrir($dados, $setor, $vinculo, $ano);
            $podeConsultar = $leitor === null || $consulta->listar($leitor, $processo, true);
            $mensagem = $podeConsultar ? 'Processo aberto com sucesso.' : 'Processo aberto. A consulta está disponível às pessoas autorizadas.';
            return redirect()->to($podeConsultar ? $endereco . '/' . $processo : $endereco, 303)->withCookies()
                ->with('processoAberto', $mensagem);
        } catch (DomainException $erro) {
            if ($edicaoAjax) {
                $alterado = $erro->getMessage() === EdicaoProcesso::ERRO_REVISAO;
                return $this->response->setStatusCode($alterado ? 409 : 422)->setJSON(['erro' => $erro->getMessage(), 'csrf' => csrf_hash()]);
            }
            if ($edicao !== null && $erro->getMessage() === EdicaoProcesso::ERRO_REVISAO) {
                return $voltar->with('erroAbertura', $erro->getMessage());
            }
            return $voltar->withInput()->with('erroAbertura', $erro->getMessage());
        } catch (DatabaseException $erro) {
            log_message('error', 'Falha ao gravar processo: {mensagem}', ['mensagem' => $erro->getMessage()]);
            if ($edicaoAjax) {
                return $this->response->setStatusCode(500)->setJSON(['erro' => 'Não foi possível gravar o processo. Tente novamente.', 'csrf' => csrf_hash()]);
            }
            return $voltar->withInput()->with('erroAbertura', 'Não foi possível gravar o processo. Tente novamente.');
        }
    }

    /** Troca o vínculo de pasta indicado na listagem, no setor ativo. */
    public function moverParaPasta(string $hash, string $processo): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/processos'), 303)->withCookies();
        $origem = $this->request->getPost('origem');
        $destino = $this->request->getPost('destino');
        $setor = $layout['contextoTrabalho']['setor']['id'] ?? null;
        if ($setor === null || $this->request->getPost('setor_contexto') !== $setor
            || !is_string($origem) || !is_string($destino)
            || !preg_match('/^(?:[1-9][0-9]{0,19})?$/D', $origem) || !preg_match('/^(?:[1-9][0-9]{0,19})?$/D', $destino)) {
            return $voltar->with('erroPastaProcesso', 'Confira a pasta e o setor selecionado.');
        }
        $autor = $vinculo;
        if ($autor === null) {
            $responsavel = $this->request->getPost('responsavel');
            $usuarios = array_column((new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios(), null, 'vinculo_id');
            if (!is_string($responsavel) || !isset($usuarios[$responsavel])) {
                return $voltar->with('erroPastaProcesso', 'Selecione um usuário interno ativo.');
            }
            $autor = $responsavel;
        }
        try {
            $consulta->moverProcesso($processo, $origem, $destino, $vinculo, $autor);
            return $voltar->with('sucessoPastaProcesso', 'Pasta do processo atualizada.');
        } catch (DomainException $erro) {
            return $voltar->with('erroPastaProcesso', $erro->getMessage());
        } catch (DatabaseException) {
            return $voltar->with('erroPastaProcesso', 'Não foi possível alterar a pasta do processo.');
        }
    }

    public function visualizar(string $identificadorOrganizacao, string $identificadorProcesso): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($identificadorOrganizacao, false);
        if (!preg_match('/^[a-f0-9]{48}$/D', $identificadorProcesso)) throw PageNotFoundException::forPageNotFound();
        $processo = $consulta->listar($vinculo, $identificadorProcesso, true)[0] ?? null;
        if (!$processo) throw PageNotFoundException::forPageNotFound();
        $documentos = $consulta->documentos((string) $processo['id'], $vinculo);
        $enderecoProcesso = site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . $identificadorProcesso);
        $envio = new EnvioDocumento(db_connect(), $layout['empresaSimulada']);
        $documentos = $this->prepararDocumentosReais($documentos, $processo['acesso'], $enderecoProcesso, $identificadorOrganizacao, $vinculo, $layout['administradorSimulado']);
        $documentosAtivos = array_filter($documentos, static fn (array $documento): bool => !$documento['excluido']);
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processo', $layout + [
            'tituloPagina' => $processo['numero'], 'processo' => $processo,
            'enderecoProcesso' => $enderecoProcesso,
            'documentos' => $documentos, 'documentoAtivo' => reset($documentosAtivos) ?: null,
            'quantidadeDocumentosAtivos' => count($documentosAtivos),
            'tiposDocumento' => $layout['administradorSimulado'] ? $envio->opcoes() : [],
            'autoresDocumento' => $vinculo === null ? (new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios() : [],
        ]));
    }

    public function editorDocumento(string $hash, string $processo): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $registro = $consulta->listar($vinculo, $processo)[0] ?? null;
        $setor = $layout['contextoTrabalho']['setor'] ?? null;
        if (!$registro || !$setor) throw PageNotFoundException::forPageNotFound();
        $empresa = $layout['empresaSimulada'];
        $editor = new EditorDocumento(db_connect(), $empresa);
        $processoId = (string) $registro['id'];
        $enderecoProcesso = site_url('organizacao/' . $hash . '/processos/' . $processo);
        $enderecoEditor = $enderecoProcesso . '/editor-documento';
        $post = $this->request->is('post');
        $chave = $post ? $this->request->getPost('chave') : $this->request->getGet('rascunho');
        if ($chave !== null && (!is_string($chave) || !preg_match('/^[a-f0-9]{48}$/D', $chave))) {
            throw PageNotFoundException::forPageNotFound();
        }
        $rascunho = $chave ? $editor->obter($processoId, $setor['id'], $vinculo, $chave) : null;
        if (!$post && $chave && (!$rascunho || $rascunho['documento_id'] !== null)) throw PageNotFoundException::forPageNotFound();
        $tipos = (new EnvioDocumento(db_connect(), $empresa))->opcoes();
        $autores = $vinculo === null ? (new ConsultaEmpresa(db_connect(), $empresa))->usuarios() : [];
        $dados = [
            'chave' => $chave ?? bin2hex(random_bytes(24)), 'revisao' => $rascunho['revisao'] ?? '',
            'tipo' => (string) ($rascunho['tipo_id'] ?? ''), 'titulo' => $rascunho['titulo'] ?? '',
            'conteudo' => EditorDocumento::limpar($rascunho['conteudo'] ?? ''), 'autor' => (string) ($rascunho['autor_id'] ?? ''),
            'hash_processo' => $processo,
        ];
        $erro = '';
        if ($post) {
            foreach (['revisao', 'tipo', 'titulo', 'conteudo', 'autor'] as $campo) {
                $valor = $this->request->getPost($campo);
                $dados[$campo] = is_string($valor) ? $valor : '';
            }
            $dados['titulo'] = trim($dados['titulo']);
            $dados['conteudo'] = EditorDocumento::limpar($dados['conteudo']);
            $autor = $vinculo ?? $dados['autor'];
            $finalizar = $this->request->getPost('acao') === 'finalizar';
            try {
                if (!$chave || $this->request->getPost('setor_contexto') !== $setor['id']) {
                    throw new DomainException('Abra o editor novamente no setor selecionado.');
                }
                if ($dados['titulo'] === '' || mb_strlen($dados['titulo']) > 500 || !preg_match('/^[1-9][0-9]{0,19}$/D', $dados['tipo'])) {
                    throw new DomainException('Selecione o tipo e informe um título de até 500 caracteres.');
                }
                if ($vinculo === null && !isset(array_column($autores, null, 'vinculo_id')[$autor])) {
                    throw new DomainException('Selecione um usuário interno ativo.');
                }
                if (strlen((string) $this->request->getPost('conteudo')) > 65535 || strlen($dados['conteudo']) > 65535) {
                    throw new DomainException('O conteúdo ultrapassou o limite do editor.');
                }
                if ($finalizar && trim(html_entity_decode(strip_tags($dados['conteudo']), ENT_QUOTES, 'UTF-8')) === '') {
                    throw new DomainException('Escreva o conteúdo antes de finalizar.');
                }
                $ano = (new DateTimeImmutable('now', new DateTimeZone($layout['fusoOrganizacao'])))->format('Y');
                $id = $editor->salvar($processoId, $setor, $vinculo, $autor, $dados, $ano, $finalizar);
                if ($id !== null) {
                    return redirect()->to($enderecoProcesso . '?documento=' . $id, 303)->withCookies()
                        ->with('sucessoEnvioDocumento', 'Documento finalizado e incluído no processo.');
                }
                return redirect()->to($enderecoEditor . '?rascunho=' . $chave, 303)->withCookies();
            } catch (DomainException $excecao) {
                $erro = $excecao->getMessage();
            } catch (DatabaseException $excecao) {
                log_message('error', 'Falha no editor de documentos: {erro}', ['erro' => $excecao->getMessage()]);
                $erro = 'Não foi possível salvar. Seu texto continua no editor.';
            }
        }
        return $this->response->setHeader('Cache-Control', 'private, no-store')->setBody(view('organizacao/editor_documento/editor', $layout + [
            'tituloPagina' => 'Editor de documento', 'processo' => $registro, 'enderecoProcesso' => $enderecoProcesso,
            'enderecoEditor' => $enderecoEditor, 'tipos' => $tipos, 'autores' => $autores, 'dados' => $dados, 'erro' => $erro,
            'rascunhos' => $editor->listar($processoId, $setor['id'], $vinculo), 'setorEditor' => $setor,
        ]));
    }

    public function lerDocumentoEditado(string $hash, string $processo, string $documento): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, , $vinculo] = $this->contextoReal($hash, false);
        $registro = $consulta->listar($vinculo, $processo, true)[0] ?? null;
        $item = $registro ? ($consulta->documentos((string) $registro['id'], $vinculo, $documento, true)[0] ?? null) : null;
        if (!$item || $item['excluido_em'] !== null || $item['origem'] !== 'editor_trilha') throw PageNotFoundException::forPageNotFound();
        $item['conteudo_editor'] = EditorDocumento::limpar($item['conteudo_editor'] ?? '');
        return $this->response->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('Content-Security-Policy', "default-src 'none'; style-src 'self'; frame-ancestors 'self'; base-uri 'none'")
            ->setBody(view('organizacao/editor_documento/leitura', ['documento' => $item]));
    }

    public function enviarDocumento(string $hash, string $processo): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $registro = $consulta->listar($vinculo, $processo, true)[0] ?? null;
        if (!$registro) throw PageNotFoundException::forPageNotFound();
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/processos/' . $processo), 303)->withCookies();
        $setor = $layout['contextoTrabalho']['setor'];
        $tipo = $this->request->getPost('tipo_documento');
        $titulo = $this->request->getPost('titulo_documento') ?? '';
        if (!$setor || $this->request->getPost('setor_contexto') !== $setor['id']
            || !is_string($tipo) || !preg_match('/^[1-9][0-9]{0,19}$/D', $tipo)) {
            return $voltar->with('erroEnvioDocumento', 'Confira o setor ativo e selecione o tipo de documento.');
        }
        if (!is_string($titulo) || mb_strlen(trim($titulo)) > 500) {
            return $voltar->with('erroEnvioDocumento', 'Informe um nome de exibição de até 500 caracteres.');
        }
        $autor = $vinculo;
        if ($autor === null) {
            $autor = $this->request->getPost('responsavel');
            $usuarios = array_column((new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios(), null, 'vinculo_id');
            if (!is_string($autor) || !isset($usuarios[$autor])) {
                return $voltar->with('erroEnvioDocumento', 'Selecione um usuário interno ativo.');
            }
        }
        try {
            $ano = (new DateTimeImmutable('now', new DateTimeZone($layout['fusoOrganizacao'])))->format('Y');
            (new EnvioDocumento(db_connect(), $layout['empresaSimulada']))->salvar(
                $hash, (string) $registro['id'], $tipo, trim($titulo), $ano, $setor, $autor, $vinculo, $this->request->getFile('documento'),
            );
        } catch (DomainException $erro) {
            return $voltar->with('erroEnvioDocumento', $erro->getMessage());
        } catch (DatabaseException | \CodeIgniter\HTTP\Exceptions\HTTPException $erro) {
            log_message('error', 'Falha no envio de documento: {mensagem}', ['mensagem' => $erro->getMessage()]);
            return $voltar->with('erroEnvioDocumento', 'Não foi possível salvar o documento. Tente novamente.');
        }
        return $voltar->with('sucessoEnvioDocumento', 'Documento adicionado ao processo.');
    }

    public function renomearDocumento(string $hash, string $processo, string $documento): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $registro = $consulta->listar($vinculo, $processo, true)[0] ?? null;
        $item = $registro ? ($consulta->documentos((string) $registro['id'], $vinculo, $documento)[0] ?? null) : null;
        if (!$item || $item['excluido_em'] !== null) {
            throw PageNotFoundException::forPageNotFound();
        }
        $titulo = $this->request->getPost('titulo');
        $titulo = is_string($titulo) ? trim($titulo) : '';
        if ($titulo === '' || !mb_check_encoding($titulo, 'UTF-8') || mb_strlen($titulo) > 500 || preg_match('/[\x00-\x1f\x7f]/', $titulo)) {
            return $this->response->setStatusCode(422)->setJSON(['erro' => 'Informe um nome de até 500 caracteres.', 'csrf' => csrf_hash()]);
        }
        try {
            db_connect()->table('documentos')->where([
                'organizacao_id' => $layout['empresaSimulada'], 'processo_principal_id' => $registro['id'], 'id' => $documento, 'excluido_em' => null,
            ])->update(['titulo' => $titulo, 'atualizado_em' => date('Y-m-d H:i:s')]);
        } catch (DatabaseException) {
            return $this->response->setStatusCode(409)->setJSON(['erro' => 'Não foi possível atualizar o nome.', 'csrf' => csrf_hash()]);
        }
        return $this->response->setJSON(['titulo' => $titulo, 'csrf' => csrf_hash()]);
    }

    public function alterarExclusaoDocumento(string $hash, string $processo, string $documento): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        $acao = $this->request->getPost('acao');
        if (!in_array($acao, ['excluir', 'restaurar', 'definitiva'], true)) throw PageNotFoundException::forPageNotFound();
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash, $acao !== 'excluir');
        $registro = $consulta->listar($vinculo, $processo, true)[0] ?? null;
        $item = $registro ? ($consulta->documentos((string) $registro['id'], $vinculo, $documento)[0] ?? null) : null;
        if (!$item || (!$layout['administradorSimulado'] && $vinculo !== (string) $item['criado_por'])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $endereco = site_url('organizacao/' . $hash . '/processos/' . $processo);
        $voltar = redirect()->to($endereco . '?documento=' . rawurlencode($documento), 303)->withCookies();
        $banco = db_connect();
        $escopo = ['organizacao_id' => $layout['empresaSimulada'], 'id' => $documento, 'processo_principal_id' => $registro['id']];
        $tabela = $banco->table('documentos');
        try {
            if ($acao === 'definitiva') {
                $banco->transException(true);
                if (!$banco->transBegin()) throw new DatabaseException('Falha ao iniciar transação.');
                try {
                    $bloqueado = $banco->query(
                        'SELECT excluido_em FROM documentos WHERE organizacao_id = ? AND id = ? AND processo_principal_id = ? FOR UPDATE',
                        [$escopo['organizacao_id'], $escopo['id'], $escopo['processo_principal_id']],
                    )->getRowArray();
                    if (empty($bloqueado['excluido_em'])) throw new DatabaseException('Documento não está excluído.');
                    $tabela->where($escopo)->update(['versao_atual_id' => null]);
                    $banco->table('documento_versoes')->where([
                        'organizacao_id' => $layout['empresaSimulada'], 'documento_id' => $documento,
                    ])->delete();
                    $tabela->where($escopo)->where('excluido_em IS NOT NULL', null, false)->delete();
                    if (!$banco->transStatus() || !$banco->transCommit()) throw new DatabaseException('Falha ao confirmar exclusão.');
                } catch (\Throwable $erro) {
                    $banco->transRollback();
                    throw $erro;
                }
            } else {
                $agora = date('Y-m-d H:i:s');
                $condicao = $acao === 'excluir' ? 'excluido_em IS NULL' : 'excluido_em IS NOT NULL';
                $tabela->where($escopo)->where($condicao, null, false)->update([
                    'excluido_em' => $acao === 'excluir' ? $agora : null, 'atualizado_em' => $agora,
                ]);
            }
        } catch (DatabaseException $erro) {
            log_message('error', 'Falha ao alterar exclusão do documento: {mensagem}', ['mensagem' => $erro->getMessage()]);
            return $voltar->with('erroEnvioDocumento', 'Não foi possível concluir a ação. O documento pode ter vínculos com outros registros.');
        }

        return $voltar->with('sucessoEnvioDocumento', match ($acao) {
            'excluir' => 'Documento marcado como excluído. Somente um administrador pode restaurá-lo.',
            'restaurar' => 'Documento restaurado.',
            'definitiva' => 'Documento removido do processo.',
        });
    }

    private const MIMES_IMAGEM_VISUALIZAVEL = [
        'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/avif', 'image/bmp', 'image/x-ms-bmp', 'image/x-icon', 'image/vnd.microsoft.icon',
    ];

    public function baixarDocumento(string $hash, string $processo, string $documento, string $modo = ''): ResponseInterface
    {
        if (!preg_match('/^[a-f0-9]{48}$/D', $processo)) throw PageNotFoundException::forPageNotFound();
        $inline = $modo === 'visualizar';
        [$consulta, , $vinculo] = $this->contextoReal($hash, !$inline);
        $registro = $consulta->listar($vinculo, $processo, true)[0] ?? null;
        if (!$registro) throw PageNotFoundException::forPageNotFound();
        $arquivo = $consulta->documentos((string) $registro['id'], $vinculo, $documento)[0] ?? null;
        $chave = $arquivo['chave_arquivo'] ?? '';
        $mime = $arquivo['mime_arquivo'] ?? '';
        $padraoChave = '~^(?:documentos|' . preg_quote($hash, '~') . ')/[a-f0-9]{48}$~D';
        if (!empty($arquivo['excluido_em']) || ($inline && $mime !== 'application/pdf' && !in_array($mime, self::MIMES_IMAGEM_VISUALIZAVEL, true))
            || !preg_match($padraoChave, $chave) || !is_file(WRITEPATH . 'uploads/' . $chave)) {
            throw PageNotFoundException::forPageNotFound();
        }
        $resposta = $this->response->download(WRITEPATH . 'uploads/' . $chave, null)
            ->setFileName($arquivo['nome_arquivo'])->setHeader('Cache-Control', 'private, no-store')
            ->setHeader('X-Content-Type-Options', 'nosniff');
        return $inline ? $resposta->setContentType($mime, '')->inline() : $resposta;
    }

    /** Usa os componentes aprovados do explorador com registros do banco. */
    public function exibir(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash, false);
        $caixa = $this->request->getGet('caixa');
        if (!in_array($caixa, ['arquivados', 'lixeira'], true)) $caixa = 'ativos';
        if ($caixa === 'lixeira' && !$layout['administradorSimulado']) throw PageNotFoundException::forPageNotFound();
        $processos = $consulta->listar($vinculo, null, false, $caixa);
        if ($vinculo === null) {
            $marcados = session()->get('acompanhamentos_simulados_' . $hash) ?? [];
            foreach ($processos as &$processo) $processo['acompanhado'] = isset($marcados[$processo['hash_publico']]);
            unset($processo);
        }
        $opcoes = [];
        foreach (['tipo', 'situacao', 'unidade', 'setor', 'responsavel', 'acesso'] as $campo) {
            $opcoes[$campo] = array_values(array_unique(array_filter(array_column($processos, $campo), static fn ($valor) => $valor !== '')));
            sort($opcoes[$campo]);
        }
        $opcoes['etiqueta'] = array_values(array_unique(array_merge(...array_column($processos, 'etiquetas'))));
        sort($opcoes['etiqueta']);
        $historico = session()->get('historicoExplorador');
        $escopo = [$hash, $layout['contextoTrabalho']['setor']['id'] ?? null, $vinculo];
        $pastaCriada = session()->getFlashdata('sucessoPastaReal');

        $explorador = $consulta->explorador($vinculo);
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos', $layout + [
            'tituloPagina' => 'Processos', 'processos' => $processos, 'opcoes' => $opcoes,
            'caixaProcessos' => $caixa, 'acoesLote' => self::ACOES_POR_CAIXA[$caixa],
            'responsaveisPasta' => $vinculo === null ? (new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios() : [],
            'pastaRealCriada' => $pastaCriada,
            'caminhosPastas' => $consulta->caminhosPastas($explorador['pastasReais']),
            'historicoPasta' => $pastaCriada && ($historico['escopo'] ?? null) === $escopo
                ? $this->avisoHistoricoPasta($historico) : null,
        ] + $explorador));
    }

    /** Cadastra uma pasta no contexto administrativo já autorizado. */
    public function cadastrarPasta(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/processos'))->withCookies();
        $nome = $this->request->getPost('nome');
        if (!is_string($nome) || trim($nome) === '' || mb_strlen(trim($nome)) > 200) {
            return $voltar->with('erroPastaReal', 'Informe um nome válido para a pasta.');
        }
        $setor = $layout['contextoTrabalho']['setor']['id'] ?? null;
        $ator = $vinculo;
        if ($setor === null || $this->request->getPost('setor_contexto') !== $setor) {
            return $voltar->with('erroPastaReal', 'Selecione um setor no cabeçalho e reabra o cadastro de pasta.');
        }
        if ($vinculo === null) {
            $responsavel = $this->request->getPost('responsavel');
            $vinculos = array_column((new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios(), null, 'vinculo_id');
            if (!is_string($responsavel) || !isset($vinculos[$responsavel])) {
                return $voltar->with('erroPastaReal', 'Selecione um usuário interno ativo para registrar a criação em seu nome.');
            }
            $vinculo = $responsavel;
        }

        try {
            $historico = $consulta->cadastrarPasta(trim($nome), $vinculo);
            if (!$historico) {
                return $voltar->with('erroPastaReal', 'O setor selecionado não pertence a esta organização ou deixou de existir.');
            }
        } catch (DatabaseException) {
            return $voltar->with('erroPastaReal', 'Não foi possível cadastrar a pasta. Confira os vínculos e tente novamente.');
        }
        $this->guardarHistoricoPasta([$hash, $setor, $ator], $historico);
        return $voltar->with('sucessoPastaReal', 'Pasta cadastrada.');
    }

    /** Executa as ações do explorador sem recarregar a árvore em caso de erro. */
    public function alterarPasta(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $dados = [];
        foreach (['acao', 'pasta', 'destino', 'origem', 'nome', 'visibilidade', 'setor_contexto', 'token'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        $acao = $dados['acao'];
        try {
            if ($dados['setor_contexto'] === '' || $dados['setor_contexto'] !== ($layout['contextoTrabalho']['setor']['id'] ?? null)) {
                throw new DomainException('O setor ativo mudou. Atualize a página antes de tentar novamente.');
            }
            $escopo = [$hash, $dados['setor_contexto'], $vinculo];
            if (in_array($acao, ['desfazer', 'refazer'], true)) {
                $registro = session()->get('historicoExplorador');
                if (!$this->avisoHistoricoPasta($registro) || $registro['escopo'] !== $escopo
                    || $registro['refazer'] !== ($acao === 'refazer') || !hash_equals($registro['token'], $dados['token'])) {
                    throw new DomainException('O prazo para esta ação terminou ou outra alteração foi feita.');
                }
                $pastas = $consulta->reverterPasta($registro['estado'], $registro['refazer'], $vinculo);
                $registro = $this->guardarHistoricoPasta($escopo, $registro['estado'], !$registro['refazer']);
                return $this->response->setJSON(['pastas' => $pastas, 'pastaId' => $registro['estado']['id'],
                    'historico' => $this->avisoHistoricoPasta($registro), 'csrf' => csrf_hash()]);
            }
            if (!in_array($acao, ['subpasta', 'renomear', 'visibilidade', 'mover', 'remover'], true)
                || !preg_match('/^[1-9][0-9]{0,19}$/D', $dados['pasta'])) {
                throw new DomainException('Ação ou pasta inválida.');
            }
            foreach (['destino', 'origem'] as $campo) {
                if ($dados[$campo] !== '' && !preg_match('/^[1-9][0-9]{0,19}$/D', $dados[$campo])) {
                    throw new DomainException('Destino inválido.');
                }
            }
            if (in_array($acao, ['subpasta', 'renomear'], true) && ($dados['nome'] === '' || mb_strlen($dados['nome']) > 200)) {
                throw new DomainException('Informe um nome com até 200 caracteres.');
            }
            if (in_array($acao, ['subpasta', 'visibilidade'], true) && !in_array($dados['visibilidade'], ['privada', 'setor'], true)) {
                throw new DomainException('Selecione uma visibilidade válida.');
            }

            $autor = $vinculo;
            if ($acao === 'subpasta' && $autor === null) {
                $responsavel = $this->request->getPost('responsavel');
                $opcoes = array_column((new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios(), null, 'vinculo_id');
                if (!is_string($responsavel) || !isset($opcoes[$responsavel])) throw new DomainException('Selecione um usuário interno ativo.');
                $autor = $responsavel;
            }
            $resultado = $consulta->alterarPasta($dados, $vinculo, $autor);
            $registro = $this->guardarHistoricoPasta($escopo, $resultado['historico']);
            return $this->response->setJSON(['pasta' => $resultado['pasta'], 'historico' => $this->avisoHistoricoPasta($registro), 'csrf' => csrf_hash()]);
        } catch (DomainException $erro) {
            return $this->response->setStatusCode(422)->setJSON(['erro' => $erro->getMessage(), 'csrf' => csrf_hash()]);
        } catch (DatabaseException) {
            return $this->response->setStatusCode(409)->setJSON(['erro' => 'Não foi possível concluir. Confira os vínculos da pasta e tente novamente.', 'csrf' => csrf_hash()]);
        }
    }

    /** Guarda na sessão a última operação, vinculada à organização, setor e usuário. */
    private function guardarHistoricoPasta(array $escopo, array $estado, bool $refazer = false): array
    {
        $registro = ['escopo' => $escopo, 'estado' => $estado, 'refazer' => $refazer,
            'token' => bin2hex(random_bytes(16)), 'expira' => microtime(true) + 5];
        session()->set('historicoExplorador', $registro);
        return $registro;
    }

    /** Expõe somente o token, a próxima ação e o tempo restante. */
    private function avisoHistoricoPasta(?array $registro): ?array
    {
        $tempo = (int) (($registro['expira'] ?? 0) * 1000 - microtime(true) * 1000);
        if ($tempo <= 0) return null;
        return ['token' => $registro['token'], 'refazer' => $registro['refazer'], 'tempo' => $tempo];
    }

    /** Mantém a consulta real no mesmo escopo administrativo do dashboard. */
    private function contextoReal(string $hash, bool $exigirAdministrador = true): array
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $acesso = new AcessoOrganizacao($banco);
        $contexto = $acesso->contexto($hash, $exigirAdministrador);
        $vinculo = $contexto['vinculo'] !== null ? (string) $contexto['vinculo'] : null;
        $empresaId = (string) $contexto['empresa'];
        $empresa = (new Cadastros($banco))->obter('clientes', $empresaId);
        if (!hash_equals($empresa['hash_publico'], $hash)) throw PageNotFoundException::forPageNotFound();

        $administrador = (bool) $contexto['fundador'];
        $contextoTrabalho = (new ContextoSetor($banco, $empresaId, $administrador ? null : (string) $contexto['vinculo']))->obter();
        return [new ConsultaProcessos($banco, $empresaId, $contextoTrabalho['setor']['id'] ?? ''), [
            'paginaAtiva' => 'processos', 'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $empresa['nome'], 'nomeUsuario' => $contexto['usuario'],
            'usuarioTemAvatar' => !empty($contexto['tem_avatar']),
            'modoSimulacao' => $contexto['simulacao'], 'simulacaoSemVinculo' => $vinculo === null, 'vinculoPreferencias' => $vinculo,
            'administradorSimulado' => $administrador, 'empresaSimulada' => $empresaId,
            'consultasPermitidas' => $administrador ? [] : $acesso->consultas($contexto),
            'contextoTrabalho' => $contextoTrabalho,
            'fusoOrganizacao' => $empresa['fuso_horario'],
        ], $vinculo];
    }

    /** Adapta os metadados reais ao painel de documentos, sem expor chaves de armazenamento. */
    private function prepararDocumentosReais(array $documentos, string $acesso, string $enderecoProcesso, string $hash, ?string $vinculo, bool $administrador): array
    {
        $padraoChave = '~^(?:documentos|' . preg_quote($hash, '~') . ')/[a-f0-9]{48}$~D';
        return array_map(static function (array $documento) use ($acesso, $enderecoProcesso, $padraoChave, $vinculo, $administrador): array {
            $nome = $documento['nome_arquivo'] ?: $documento['titulo'];
            $excluido = $documento['excluido_em'] !== null;
            $endereco = $enderecoProcesso . '/documentos/' . $documento['id'];
            $disponivel = !$excluido && preg_match($padraoChave, $documento['chave_arquivo'] ?? '');
            return [
                'id' => (string) $documento['id'], 'titulo' => $documento['titulo'], 'nomeArquivo' => $nome,
                'excluido' => $excluido, 'podeExcluir' => $administrador || $vinculo === (string) $documento['criado_por'],
                'download' => $disponivel && $administrador ? $endereco . '/arquivo' : '',
                'pdf' => $disponivel && $documento['mime_arquivo'] === 'application/pdf' ? $endereco . '/visualizar' : '',
                'leitura' => !$excluido && $documento['origem'] === 'editor_trilha' ? $endereco . '/leitura' : '',
                'imagem' => $disponivel && in_array($documento['mime_arquivo'], self::MIMES_IMAGEM_VISUALIZAVEL, true) ? $endereco . '/visualizar' : '',
                'numero' => $documento['numero_sequencial'] !== null && $documento['periodo_numeracao'] !== null
                    ? str_pad((string) $documento['numero_sequencial'], 4, '0', STR_PAD_LEFT) . '/' . $documento['periodo_numeracao']
                    : ($documento['numero_completo'] ?? 'Sem número'),
                'formato' => strtolower(pathinfo($documento['nome_arquivo'] ?? '', PATHINFO_EXTENSION)),
                'tipo' => $documento['tipo'], 'autor' => $documento['autor'] ?? '—', 'setor' => $documento['setor'],
                'data' => date('d/m/Y · H:i', strtotime($documento['criado_em'])),
                'situacao' => $excluido ? 'Excluído' : $documento['situacao'], 'cor' => $excluido ? 'vermelho' : 'neutro',
                'acesso' => PoliticaProcesso::NIVEIS[$documento['nivel']] ?? $documento['nivel'] ?? $acesso,
                'versao' => (string) ($documento['numero_versao'] ?? '—'), 'descricao' => '',
            ];
        }, $documentos);
    }
}
