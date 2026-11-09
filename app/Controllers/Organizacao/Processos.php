<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\AberturaProcesso;
use App\Libraries\Organizacao\ConsultaProcessos;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\Database\Exceptions\DatabaseException;
use DomainException;
use DateTimeImmutable;
use DateTimeZone;
use CodeIgniter\HTTP\ResponseInterface;

/** Exibe a consulta visual de processos com exemplos inteiramente locais. */
class Processos extends BaseController
{
    /** Exibe a página de abertura com os cadastros e o setor da organização real. */
    public function novo(string $hash): ResponseInterface
    {
        if ($hash === '1') throw PageNotFoundException::forPageNotFound();
        [, $layout, $vinculo] = $this->contextoReal($hash);
        $banco = db_connect();
        $empresa = $layout['empresaSimulada'];
        $opcoes = (new AberturaProcesso($banco, $empresa))->opcoes();
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos/novo', $layout + $opcoes + [
            'tituloPagina' => 'Novo processo',
            'responsaveis' => $vinculo === null ? (new ConsultaEmpresa($banco, $empresa))->usuarios() : [],
        ]));
    }

    /** Valida a abertura e redireciona para o processo gravado. */
    public function abrir(string $hash): ResponseInterface
    {
        if ($hash === '1') throw PageNotFoundException::forPageNotFound();
        [, $layout, $vinculo] = $this->contextoReal($hash);
        $endereco = site_url('organizacao/' . $hash . '/processos');
        $voltar = redirect()->to($endereco . '/novo', 303)->withCookies();
        $dados = [];
        foreach (['tipo', 'situacao', 'assunto', 'descricao', 'setor_contexto', 'responsavel'] as $campo) {
            $valor = $this->request->getPost($campo);
            $dados[$campo] = is_string($valor) ? trim($valor) : '';
        }
        try {
            $setor = $layout['contextoTrabalho']['setor'];
            if (!$setor || $setor['id'] !== $dados['setor_contexto']) {
                throw new DomainException('O setor ativo mudou. Confira a origem do processo e tente novamente.');
            }
            if (!preg_match('/^[1-9][0-9]{0,19}$/D', $dados['tipo']) || !preg_match('/^[1-9][0-9]{0,19}$/D', $dados['situacao'])) {
                throw new DomainException('Selecione o tipo e a situação inicial.');
            }
            if ($dados['assunto'] === '' || mb_strlen($dados['assunto']) > 500 || strlen($dados['descricao']) > 65535) {
                throw new DomainException('Informe um assunto com até 500 caracteres e confira o tamanho da descrição.');
            }
            $banco = db_connect();
            $empresa = $layout['empresaSimulada'];
            if ($vinculo === null) {
                $usuarios = array_column((new ConsultaEmpresa($banco, $empresa))->usuarios(), null, 'vinculo_id');
                if (!isset($usuarios[$dados['responsavel']])) {
                    throw new DomainException('Selecione um usuário interno ativo para registrar a abertura.');
                }
                $vinculo = $dados['responsavel'];
            }
            $ano = (int) (new DateTimeImmutable('now', new DateTimeZone($layout['fusoOrganizacao'])))->format('Y');
            $processo = (new AberturaProcesso($banco, $empresa))->abrir($dados, $setor, $vinculo, $ano);
            return redirect()->to($endereco . '/' . $processo, 303)->withCookies()->with('processoAberto', 'Processo aberto com sucesso.');
        } catch (DomainException $erro) {
            return $voltar->withInput()->with('erroAbertura', $erro->getMessage());
        } catch (DatabaseException $erro) {
            log_message('error', 'Falha ao abrir processo: {mensagem}', ['mensagem' => $erro->getMessage()]);
            return $voltar->withInput()->with('erroAbertura', 'Não foi possível gravar o processo. Tente novamente.');
        }
    }

    /** Prepara a organização demonstrativa e as opções de navegação e filtragem. */
    public function exibir(string $identificadorOrganizacao): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            return $this->exibirReais($identificadorOrganizacao);
        }
        helper('url');
        $processos = $this->obterProcessosDemonstrativos();
        $opcoes = [];
        foreach (['tipo', 'situacao', 'unidade', 'setor', 'responsavel', 'acesso', 'etiqueta'] as $campo) {
            $opcoes[$campo] = array_values(array_unique(array_column($processos, $campo)));
            sort($opcoes[$campo]);
        }
        $unidades = [];
        $pastas = [];
        foreach ($processos as $processo) {
            $unidades[$processo['unidade']][$processo['setor']] = true;
            $pastas[$processo['grupoPasta']][$processo['pasta']] = true;
        }

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos', [
            'tituloPagina' => 'Processos', 'paginaAtiva' => 'processos',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte', 'nomeUsuario' => 'Lucas',
            'processos' => $processos, 'opcoes' => $opcoes, 'unidades' => $unidades, 'pastas' => $pastas,
        ]));
    }

    /** Abre um exemplo pelo identificador provisório, que futuramente será um hash. */
    public function visualizar(string $identificadorOrganizacao, string $identificadorProcesso): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            [$consulta, $layout, $vinculo] = $this->contextoReal($identificadorOrganizacao);
            if (!preg_match('/^[1-9][0-9]{0,19}$/D', $identificadorProcesso)) throw PageNotFoundException::forPageNotFound();
            $processo = $consulta->listar($vinculo, $identificadorProcesso)[0] ?? null;
            if (!$processo) throw PageNotFoundException::forPageNotFound();
            return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processo_consulta', $layout + [
                'tituloPagina' => $processo['numero'], 'processo' => $processo, 'documentos' => $consulta->documentos($identificadorProcesso),
            ]));
        }
        $processoSelecionado = null;
        foreach ($this->obterProcessosDemonstrativos() as $processo) {
            if ((string) $processo['id'] === $identificadorProcesso) {
                $processoSelecionado = $processo;
                break;
            }
        }
        if ($processoSelecionado === null) {
            throw PageNotFoundException::forPageNotFound();
        }
        helper('url');
        $pdfDisponivel = is_file(FCPATH . 'pdf/teste.pdf');

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processo', [
            'tituloPagina' => $processoSelecionado['numero'], 'paginaAtiva' => 'processos',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte', 'nomeUsuario' => 'Lucas',
            'processo' => $processoSelecionado,
            'documentos' => $this->prepararFormatosDocumentos($this->obterDocumentosDemonstrativos($processoSelecionado)),
            'pdfDisponivel' => $pdfDisponivel,
            'enderecoPdf' => base_url('pdf/teste.pdf'),
        ]));
    }

    /** Usa os componentes aprovados do explorador com registros do banco. */
    private function exibirReais(string $hash): ResponseInterface
    {
        [$consulta, $layout, $vinculo] = $this->contextoReal($hash);
        $processos = $consulta->listar($vinculo);
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

        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/processos', $layout + [
            'tituloPagina' => 'Processos', 'processos' => $processos, 'opcoes' => $opcoes,
            'mostrarAcompanhados' => $vinculo !== null,
            'responsaveisPasta' => $vinculo === null ? (new ConsultaEmpresa(db_connect(), $layout['empresaSimulada']))->usuarios() : [],
            'pastaRealCriada' => $pastaCriada,
            'historicoPasta' => $pastaCriada && ($historico['escopo'] ?? null) === $escopo
                ? $this->avisoHistoricoPasta($historico) : null,
        ] + $consulta->explorador($vinculo)));
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
    private function contextoReal(string $hash): array
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $simulado = (session()->get(SimulacaoEmpresa::SESSAO)['hash'] ?? null) === $hash;
        $contexto = $simulado
            ? SimulacaoEmpresa::contexto($this->request, $hash, true)
            : (new AcessoOrganizacao($banco))->contexto($hash, true);
        $empresaId = (string) $contexto['empresa'];
        $empresa = (new Cadastros($banco))->obter('clientes', $empresaId);
        if (!hash_equals($empresa['hash_publico'], $hash)) throw PageNotFoundException::forPageNotFound();

        $contextoTrabalho = (new ContextoSetor($banco, $empresaId, null))->obter();
        return [new ConsultaProcessos($banco, $empresaId, $contextoTrabalho['setor']['id'] ?? ''), [
            'paginaAtiva' => 'processos', 'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $empresa['nome'], 'nomeUsuario' => $simulado ? 'Administrador da organização' : $contexto['usuario'],
            'modoSimulacao' => $simulado, 'contextoEmpresaReal' => !$simulado,
            'administradorSimulado' => true, 'empresaSimulada' => $empresaId, 'consultaReal' => true,
            'contextoTrabalho' => $contextoTrabalho,
            'fusoOrganizacao' => $empresa['fuso_horario'],
        ], $simulado ? null : (string) $contexto['vinculo']];
    }

    /** Identifica os formatos originais ilustrativos, mantendo o mesmo PDF na prévia. */
    private function prepararFormatosDocumentos(array $documentos): array
    {
        $formatos = ['pdf', 'xlsx', 'jpg', 'pdf', 'docx'];
        foreach ($documentos as $indice => &$documento) {
            $documento['formato'] = $formatos[$indice];
            $documento['nomeArquivo'] = $documento['titulo'] . '.' . $documento['formato'];
        }
        unset($documento);
        return $documentos;
    }

    /** Fornece documentos fictícios que compartilham o PDF disponibilizado pelo usuário. */
    private function obterDocumentosDemonstrativos(array $processo): array
    {
        return [
            ['id' => '1', 'numero' => 'DOC-0001/2026', 'titulo' => 'Documento de abertura', 'tipo' => 'Solicitação', 'autor' => $processo['responsavel'], 'setor' => $processo['setor'], 'data' => '02/11/2026 · 09:15', 'situacao' => 'Assinado', 'cor' => 'verde', 'acesso' => 'Interno', 'versao' => '1', 'descricao' => 'Apresentação do assunto e da necessidade que originou o processo.'],
            ['id' => '2', 'numero' => 'DOC-0002/2026', 'titulo' => 'Documentação complementar', 'tipo' => 'Documento externo', 'autor' => $processo['responsavel'], 'setor' => $processo['setor'], 'data' => '02/11/2026 · 10:20', 'situacao' => 'Anexado', 'cor' => 'azul', 'acesso' => 'Interno', 'versao' => '1', 'descricao' => 'Informações complementares enviadas para instruir a análise.'],
            ['id' => '3', 'numero' => 'DOC-0003/2026', 'titulo' => 'Anexo de referência', 'tipo' => 'Anexo', 'autor' => 'Mariana Costa', 'setor' => 'Financeiro', 'data' => '02/11/2026 · 11:05', 'situacao' => 'Anexado', 'cor' => 'azul', 'acesso' => 'Restrito por setor', 'versao' => '1', 'descricao' => 'Material de apoio e referência para os setores envolvidos.'],
            ['id' => '4', 'numero' => 'DOC-0004/2026', 'titulo' => 'Despacho de análise', 'tipo' => 'Despacho', 'autor' => 'Lucas', 'setor' => 'Administrativo', 'data' => '02/11/2026 · 14:30', 'situacao' => 'Aguardando assinatura', 'cor' => 'laranja', 'acesso' => 'Interno', 'versao' => '2', 'descricao' => 'Registro da análise e das providências propostas pelo setor.'],
            ['id' => '5', 'numero' => 'DOC-0005/2026', 'titulo' => 'Parecer do setor', 'tipo' => 'Parecer', 'autor' => 'Beatriz Alves', 'setor' => 'Jurídico', 'data' => '02/11/2026 · 16:10', 'situacao' => 'Em elaboração', 'cor' => 'roxo', 'acesso' => 'Confidencial', 'versao' => '1', 'descricao' => 'Manifestação técnica demonstrativa sobre os documentos do processo.'],
        ];
    }

    /** Monta cenários fictícios suficientes para avaliar filtros e várias páginas. */
    private function obterProcessosDemonstrativos(): array
    {
        $exemplos = [
            ['COMPRAS-0008/2026', 'Aquisição de equipamentos para a equipe', 'Compras', 'Matriz', 'Financeiro', 'Mariana Costa', 'Em análise', 'Interno', 'Horizonte Tecnologia', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0012/2026', 'Admissão de novo colaborador', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em andamento', 'Confidencial', 'João Martins', 'Pessoas', 'Pessoas', 'Admissões'],
            ['COMPRAS-0007/2026', 'Renovação de licenças de software', 'Compras', 'Matriz', 'Tecnologia', 'Pedro Lima', 'Em análise', 'Restrito por setor', 'Equipe de Tecnologia', 'Tecnologia', 'Administrativo', 'Compras'],
            ['CONTRATOS-0003/2026', 'Revisão do contrato de manutenção', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Em andamento', 'Restrito por setor', 'Oficina Central', 'Manutenção', 'Administrativo', 'Contratos'],
            ['COMPRAS-0009/2026', 'Materiais para a nova unidade', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Interno', 'Equipe de Operações', 'Nova unidade', 'Administrativo', 'Compras'],
            ['ADM-0011/2026', 'Atualização de documentos admissionais', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em análise', 'Confidencial', 'Carla Oliveira', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0004/2026', 'Prestação de serviços de limpeza', 'Contratos', 'Filial Centro', 'Operações', 'Rafael Santos', 'Concluído', 'Interno', 'Limpeza Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0001/2026', 'Organização do arquivo administrativo', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Aberto', 'Interno', 'Equipe Administrativa', 'Organização', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0010/2026', 'Compra de cadeiras ergonômicas', 'Compras', 'Matriz', 'Administrativo', 'Lucas', 'Em andamento', 'Interno', 'Equipe Administrativa', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0013/2026', 'Admissão de assistente financeiro', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Aberto', 'Confidencial', 'Paula Ribeiro', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0005/2026', 'Aditivo do serviço de internet', 'Contratos', 'Matriz', 'Tecnologia', 'Pedro Lima', 'Em análise', 'Restrito por setor', 'Provedor Exemplo', 'Tecnologia', 'Administrativo', 'Contratos'],
            ['INTERNO-0002/2026', 'Atualização do cadastro de fornecedores', 'Administrativo', 'Matriz', 'Financeiro', 'Mariana Costa', 'Concluído', 'Interno', 'Equipe Financeira', 'Cadastro', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0011/2026', 'Reposição de materiais de escritório', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Concluído', 'Interno', 'Papelaria Exemplo', 'Materiais', 'Administrativo', 'Compras'],
            ['ADM-0014/2026', 'Admissão de analista de suporte', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Em andamento', 'Confidencial', 'André Ferreira', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0006/2026', 'Revisão do contrato de locação', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Suspenso', 'Restrito por setor', 'Imobiliária Exemplo', 'Locação', 'Administrativo', 'Contratos'],
            ['INTERNO-0003/2026', 'Planejamento da mudança de setor', 'Administrativo', 'Filial Centro', 'Operações', 'Rafael Santos', 'Em análise', 'Interno', 'Equipe de Operações', 'Nova unidade', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0012/2026', 'Aquisição de ferramentas de manutenção', 'Compras', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Interno', 'Manutenção Exemplo', 'Manutenção', 'Administrativo', 'Compras'],
            ['ADM-0015/2026', 'Integração documental de colaborador', 'Admissão', 'Matriz', 'Recursos Humanos', 'Ana Souza', 'Concluído', 'Confidencial', 'Fernanda Alves', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0007/2026', 'Serviço de armazenamento documental', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Aberto', 'Restrito por setor', 'Arquivo Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0004/2026', 'Revisão das orientações administrativas', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Em andamento', 'Interno', 'Equipe Administrativa', 'Organização', 'Administrativo', 'Rotinas internas'],
            ['COMPRAS-0013/2026', 'Contratação de equipamento de impressão', 'Compras', 'Matriz', 'Financeiro', 'Mariana Costa', 'Suspenso', 'Interno', 'Impressão Exemplo', 'Equipamentos', 'Administrativo', 'Compras'],
            ['ADM-0016/2026', 'Admissão de auxiliar de operações', 'Admissão', 'Filial Centro', 'Operações', 'Rafael Santos', 'Aberto', 'Confidencial', 'Diego Santos', 'Pessoas', 'Pessoas', 'Admissões'],
            ['CONTRATOS-0008/2026', 'Encerramento do contrato de consultoria', 'Contratos', 'Matriz', 'Jurídico', 'Beatriz Alves', 'Concluído', 'Restrito por setor', 'Consultoria Exemplo', 'Serviços', 'Administrativo', 'Contratos'],
            ['INTERNO-0005/2026', 'Conferência dos inventários das unidades', 'Administrativo', 'Matriz', 'Administrativo', 'Lucas', 'Em análise', 'Interno', 'Equipes das Unidades', 'Organização', 'Administrativo', 'Rotinas internas'],
        ];
        $processos = [];
        foreach ($exemplos as $indice => $exemplo) {
            [$numero, $assunto, $tipo, $unidade, $setor, $responsavel, $situacao, $acesso, $interessado, $etiqueta, $grupoPasta, $pasta] = $exemplo;
            $processos[] = [
                'id' => $indice + 1, 'numero' => $numero, 'assunto' => $assunto, 'tipo' => $tipo,
                'unidade' => $unidade, 'setor' => $setor, 'responsavel' => $responsavel,
                'situacao' => $situacao, 'acesso' => $acesso, 'interessado' => $interessado,
                'etiqueta' => $etiqueta, 'grupoPasta' => $grupoPasta, 'pasta' => $pasta,
                'abertura' => '2026-10-' . str_pad((string) (28 - $indice), 2, '0', STR_PAD_LEFT),
                'atualizacao' => '2026-11-02 ' . str_pad((string) (17 - intdiv($indice, 2)), 2, '0', STR_PAD_LEFT) . ':' . ($indice % 2 === 0 ? '40' : '15'),
                'prazo' => $situacao === 'Concluído' ? '' : '2026-11-' . str_pad((string) (3 + $indice), 2, '0', STR_PAD_LEFT),
                'destinos' => $indice % 4 === 0 ? ['Administrativo', 'Financeiro', 'Jurídico'] : [$setor],
                'acompanhado' => in_array($indice, [0, 3, 8, 14, 20], true),
            ];
        }
        return $processos;
    }
}
