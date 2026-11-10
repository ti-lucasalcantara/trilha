<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use Throwable;

/** Adapta os registros de uma organização ao explorador de processos. */
class ConsultaProcessos
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa, private readonly string $setorAtivo)
    {
    }

    /** Serializa a marcação e evita registros duplicados em tentativas repetidas. */
    public function salvarAcompanhamento(string $hash, string $vinculo, bool $acompanhar): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar o acompanhamento.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $processo = $this->listar($vinculo, $hash)[0] ?? null;
            if (!$processo) throw new DomainException('Processo indisponível neste setor.');
            $chave = ['organizacao_id' => $this->empresa, 'vinculo_id' => $vinculo, 'processo_id' => $processo['id']];
            $acompanhamentos = $this->banco->table('acompanhamentos');
            if (!$acompanhar) {
                $acompanhamentos->where($chave)->delete();
            } elseif (!$processo['acompanhado']) {
                $acompanhamentos->insert($chave + ['criado_em' => date('Y-m-d H:i:s')]);
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                throw new DomainException('Não foi possível atualizar o acompanhamento.');
            }
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Consulta os processos e metadados somente na organização corrente. */
    public function listar(?string $vinculo, ?string $hash = null, bool $acessoDireto = false, string $caixa = 'ativos'): array
    {
        $nivel = '(SELECT pa.nivel FROM politicas_acesso pa WHERE pa.organizacao_id = p.organizacao_id AND pa.processo_id = p.id
            AND pa.documento_id IS NULL ORDER BY pa.id DESC LIMIT 1)';
        $escopo = ContextoSetor::condicaoProcessos($this->banco, $this->setorAtivo, 'p');
        // Um link direto também atende pessoas e setores autorizados fora da caixa de trabalho.
        if ($acessoDireto && $hash !== null && $vinculo !== null) {
            $escopo = "({$escopo} OR {$nivel} IN ('restrito', 'confidencial'))";
        }
        $consulta = $this->banco->table('processos p')
            ->select('p.id, p.numero_completo AS numero, p.assunto, p.descricao, tipo.nome AS tipo, situacao.nome AS situacao, unidade.nome AS unidade, setor.nome AS setor, usuario.nome AS responsavel, p.unidade_origem_id AS unidadeEscopo, p.setor_origem_id AS setorEscopo, p.aberto_em AS abertura, p.atualizado_em AS atualizacao, p.encerrado_em')
            ->join('tipos_processo tipo', 'tipo.organizacao_id = p.organizacao_id AND tipo.id = p.tipo_processo_id')
            ->join('status_processo situacao', 'situacao.organizacao_id = p.organizacao_id AND situacao.id = p.status_id')
            ->select('situacao.cor AS cor_situacao')
            ->select('p.hash_publico')
            ->join('unidades unidade', 'unidade.organizacao_id = p.organizacao_id AND unidade.id = p.unidade_origem_id')
            ->join('setores setor', 'setor.organizacao_id = p.organizacao_id AND setor.id = p.setor_origem_id')
            ->join('vinculos_organizacao autor', 'autor.organizacao_id = p.organizacao_id AND autor.id = p.aberto_por', 'left')
            ->join('usuarios usuario', 'usuario.id = autor.usuario_id', 'left')
            ->where('p.organizacao_id', $this->empresa)
            ->where($escopo, null, false)
            ->select('(SELECT MIN(pr.vence_em) FROM prazos pr WHERE pr.organizacao_id = p.organizacao_id AND pr.processo_id = p.id AND pr.concluido_em IS NULL) AS prazo', false)
            ->select($nivel . ' AS acesso', false)
            ->select("(SELECT GROUP_CONCAT(pp.pasta_id SEPARATOR ',') FROM pasta_processos pp WHERE pp.organizacao_id = p.organizacao_id AND pp.processo_id = p.id) AS pastas", false)
            ->select("(SELECT GROUP_CONCAT(i.nome SEPARATOR ', ') FROM processo_interessados pi JOIN interessados i ON i.organizacao_id = pi.organizacao_id AND i.id = pi.interessado_id WHERE pi.organizacao_id = p.organizacao_id AND pi.processo_id = p.id) AS interessado", false)
            ->select("(SELECT GROUP_CONCAT(DISTINCT s.nome SEPARATOR ',') FROM tramitacao_destinatarios d JOIN setores s ON s.organizacao_id = d.organizacao_id AND s.id = d.setor_id WHERE d.organizacao_id = p.organizacao_id AND d.tramitacao_id = (SELECT MAX(t.id) FROM tramitacoes t WHERE t.organizacao_id = p.organizacao_id AND t.processo_id = p.id AND t.documento_id IS NULL)) AS destinos_registrados", false)
            ->select('(SELECT MAX(t.enviada_em) FROM tramitacoes t WHERE t.organizacao_id = p.organizacao_id AND t.processo_id = p.id AND t.documento_id IS NULL) AS ultima_tramitacao', false);
        if ($vinculo !== null) $consulta->where(PoliticaProcesso::condicao($this->banco, 'p', $vinculo), null, false);
        if ($hash !== null) $consulta->where('p.hash_publico', $hash);
        $this->filtrarCaixa($consulta, $acessoDireto ? 'consultaveis' : $caixa);

        $etiquetas = $this->banco->table('processo_etiquetas pe')->select('pe.processo_id, e.nome')
            ->join('etiquetas e', 'e.organizacao_id = pe.organizacao_id AND e.id = pe.etiqueta_id')
            ->where('pe.organizacao_id', $this->empresa)->get()->getResultArray();
        $porProcesso = [];
        foreach ($etiquetas as $etiqueta) $porProcesso[$etiqueta['processo_id']][] = $etiqueta['nome'];

        $acompanhados = $vinculo === null ? [] : array_column($this->banco->table('acompanhamentos')
            ->select('processo_id')->where(['organizacao_id' => $this->empresa, 'vinculo_id' => $vinculo])
            ->where('processo_id IS NOT NULL', null, false)->get()->getResultArray(), 'processo_id');
        return array_map(static function (array $processo) use ($porProcesso, $acompanhados): array {
            $processo['destinos'] = $processo['destinos_registrados'] ? explode(',', $processo['destinos_registrados']) : [$processo['setor']];
            $processo['atualizacao'] = max($processo['ultima_tramitacao'] ?? '', $processo['atualizacao'] ?? '', $processo['abertura']);
            $processo['abertura'] = substr($processo['abertura'], 0, 10);
            $processo['prazo'] = $processo['encerrado_em'] ? '' : substr($processo['prazo'] ?? '', 0, 10);
            $processo['etiquetas'] = $porProcesso[$processo['id']] ?? [];
            $processo['etiqueta'] = '';
            $processo['acesso'] = PoliticaProcesso::NIVEIS[$processo['acesso'] ?? 'interno'] ?? $processo['acesso'];
            $processo['acompanhado'] = in_array((string) $processo['id'], array_map('strval', $acompanhados), true);
            foreach (['acesso', 'interessado', 'responsavel', 'pastas'] as $campo) $processo[$campo] ??= '';
            $processo['grupoPasta'] = $processo['pasta'] = '';
            return $processo;
        }, $consulta->orderBy('p.id', 'DESC')->get()->getResultArray());
    }

    /** Separa a caixa de trabalho, os arquivados e a lixeira. */
    private function filtrarCaixa(\CodeIgniter\Database\BaseBuilder $consulta, string $caixa): void
    {
        if ($caixa === 'lixeira') {
            $consulta->where('p.excluido_em IS NOT NULL', null, false);
            return;
        }
        $consulta->where('p.excluido_em', null);
        if ($caixa === 'arquivados') $consulta->where('p.arquivado_em IS NOT NULL', null, false);
        elseif ($caixa === 'ativos') $consulta->where('p.arquivado_em', null);
    }

    /** Executa o lote inteiro ou reverte todas as mudanças se uma seleção ficou indisponível. */
    public function executarLote(array $hashes, string $acao, string $caixa, string $destino, ?string $vinculo, string $autor): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a operação.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $consulta = $this->banco->table('processos p')->select('p.id')
                ->where('p.organizacao_id', $this->empresa)->whereIn('p.hash_publico', $hashes)
                ->where(ContextoSetor::condicaoProcessos($this->banco, $this->setorAtivo, 'p'), null, false);
            if ($vinculo !== null) $consulta->where(PoliticaProcesso::condicao($this->banco, 'p', $vinculo), null, false);
            $this->filtrarCaixa($consulta, $caixa);
            $processos = $consulta->get()->getResultArray();
            if (count($processos) !== count($hashes)) throw new DomainException('A seleção mudou. Confira a caixa e o setor e tente novamente.');

            $pastas = $acao === 'mover' ? $this->caminhosPastas($this->explorador($vinculo)['pastasReais']) : [];
            if ($acao === 'mover' && !isset($pastas[$destino])) throw new DomainException('A pasta de destino não está disponível neste setor.');
            $agora = date('Y-m-d H:i:s');
            foreach ($processos as $processo) {
                $chave = ['organizacao_id' => $this->empresa, 'processo_id' => $processo['id']];
                if ($acao === 'mover') {
                    $vinculos = $this->banco->table('pasta_processos')->where($chave);
                    $vinculos->whereIn('pasta_id', array_keys($pastas))->where('pasta_id !=', $destino)->delete();
                    if (!$vinculos->where($chave + ['pasta_id' => $destino])->countAllResults()) {
                        $vinculos->insert($chave + ['pasta_id' => $destino, 'incluido_por' => $autor, 'criado_em' => $agora]);
                    }
                } else {
                    $campo = $acao === 'excluir' || $caixa === 'lixeira' ? 'excluido_em' : 'arquivado_em';
                    $this->banco->table('processos')->where(['organizacao_id' => $this->empresa, 'id' => $processo['id']])
                        ->update([$campo => $acao === 'restaurar' ? null : $agora, 'atualizado_em' => $agora]);
                }
                $this->banco->table('historico_eventos')->insert($chave + [
                    'tipo' => 'processo.' . $acao, 'autor_id' => $autor, 'ocorrido_em' => $agora, 'criado_em' => $agora,
                    'visibilidade' => 'interno', 'detalhes' => json_encode(['caixa' => $caixa, 'pasta' => $destino ?: null], JSON_THROW_ON_ERROR),
                ]);
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível concluir a operação.');
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Reúne as pastas do setor ativo para o explorador. */
    public function explorador(?string $vinculo): array
    {
        $consulta = $this->consultaPastas($vinculo)
            ->select('pastas.id, pastas.pasta_pai_id, pastas.nome, pastas.visibilidade')
            ->select("(EXISTS (SELECT 1 FROM pasta_processos pp WHERE pp.organizacao_id = pastas.organizacao_id AND pp.pasta_id = pastas.id)
                OR EXISTS (SELECT 1 FROM pasta_documentos pd WHERE pd.organizacao_id = pastas.organizacao_id AND pd.pasta_id = pastas.id)) AS ocupada", false);
        return ['pastasReais' => $consulta->orderBy('pastas.nome')->orderBy('pastas.id')->get()->getResultArray()];
    }

    /** Limita a árvore e suas ações ao setor corrente e à visibilidade da pasta. */
    private function consultaPastas(?string $vinculo): \CodeIgniter\Database\BaseBuilder
    {
        $consulta = $this->banco->table('pastas')->where(['pastas.organizacao_id' => $this->empresa, 'pastas.setor_id' => $this->setorAtivo ?: '0']);
        if ($vinculo !== null) {
            $consulta->groupStart()->where('pastas.visibilidade !=', 'privada')->orWhere('pastas.criada_por', $vinculo)->groupEnd();
        }
        return $consulta;
    }

    /** Exibe o caminho completo apenas de pastas alcançáveis na árvore visível. */
    public function caminhosPastas(array $pastas): array
    {
        $porPai = [];
        foreach ($pastas as $pasta) $porPai[(string) $pasta['pasta_pai_id']][] = $pasta;
        $caminhos = [];
        $visitar = static function (string $pai, string $prefixo) use (&$visitar, &$caminhos, $porPai): void {
            foreach ($porPai[$pai] ?? [] as $pasta) {
                $id = (string) $pasta['id'];
                $caminhos[$id] = $prefixo . $pasta['nome'];
                $visitar($id, $caminhos[$id] . ' / ');
            }
        };
        $visitar('', '');
        return $caminhos;
    }

    /** Substitui somente a associação escolhida, preservando outras pastas do processo. */
    public function moverProcesso(string $hash, string $origem, string $destino, ?string $vinculo, string $autor): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a mudança de pasta.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $processo = $this->listar($vinculo, $hash)[0] ?? null;
            if (!$processo) throw new DomainException('Processo indisponível no setor selecionado.');
            $pastas = $this->caminhosPastas($this->explorador($vinculo)['pastasReais']);
            $atuais = array_intersect(explode(',', $processo['pastas']), array_map('strval', array_keys($pastas)));
            if (($origem === '' && $atuais) || ($origem !== '' && !in_array($origem, $atuais, true))
                || ($destino !== '' && !isset($pastas[$destino]))) {
                throw new DomainException('As pastas do processo mudaram ou o destino está indisponível. Confira a seleção.');
            }
            $chave = ['organizacao_id' => $this->empresa, 'processo_id' => $processo['id']];
            if ($destino !== $origem) {
                if ($origem !== '') $this->banco->table('pasta_processos')->where($chave + ['pasta_id' => $origem])->delete();
                if ($destino !== '' && !in_array($destino, $atuais, true)) {
                    $this->banco->table('pasta_processos')->insert($chave + [
                        'pasta_id' => $destino, 'incluido_por' => $autor, 'criado_em' => date('Y-m-d H:i:s'),
                    ]);
                }
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível atualizar a pasta.');
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Serializa alterações na árvore e devolve o estado gravado. */
    public function alterarPasta(array $dados, ?string $vinculo, ?string $autor): array
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a alteração.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $pasta = $this->consultaPastas($vinculo)->where('pastas.id', $dados['pasta'])->get()->getRowArray();
            if (!$pasta) throw new DomainException('Pasta indisponível neste setor.');

            $agora = date('Y-m-d H:i:s');
            $id = (string) $pasta['id'];
            $consulta = $this->consultaPastas($vinculo)->where('pastas.id', $id);
            $resultado = null;
            $compartilhamentos = [];
            switch ($dados['acao']) {
                case 'subpasta':
                    $resultado = [
                        'organizacao_id' => $this->empresa, 'setor_id' => $this->setorAtivo, 'pasta_pai_id' => $id,
                        'nome' => $this->nomeDisponivelPasta($dados['nome'], $id),
                        'visibilidade' => $dados['visibilidade'], 'criada_por' => $autor, 'criado_em' => $agora,
                    ];
                    $this->banco->table('pastas')->insert($resultado);
                    $resultado['id'] = (string) $this->banco->insertID();
                    break;
                case 'remover':
                    if ($this->consultaPastas(null)->where('pastas.pasta_pai_id', $id)->countAllResults()) {
                        throw new DomainException('Remova ou mova as subpastas antes de excluir esta pasta.');
                    }
                    $escopo = ['organizacao_id' => $this->empresa, 'pasta_id' => $id];
                    foreach (['pasta_processos', 'pasta_documentos'] as $tabela) {
                        if ($this->banco->table($tabela)->where($escopo)->countAllResults()) {
                            throw new DomainException('A pasta contém processos ou documentos vinculados.');
                        }
                    }
                    $compartilhamentos = $this->banco->table('pasta_compartilhamentos')->where($escopo)->orderBy('id')->get()->getResultArray();
                    $this->banco->table('pasta_compartilhamentos')->where($escopo)->delete();
                    $consulta->delete();
                    break;
                default:
                    $alteracoes = match ($dados['acao']) {
                        'renomear' => ['nome' => $this->nomeDisponivelPasta($dados['nome'], $pasta['pasta_pai_id'], $id)],
                        'visibilidade' => ['visibilidade' => $dados['visibilidade']],
                        'mover' => ['pasta_pai_id' => $this->validarDestinoPasta($pasta, $dados['origem'], $dados['destino'], $vinculo)],
                    };
                    $consulta->update($alteracoes + ['atualizado_em' => $agora]);
                    $resultado = array_replace($pasta, $alteracoes);
                    break;
            }
            $idResultado = (string) ($resultado['id'] ?? $id);
            $depois = $resultado === null ? null : $this->consultaPastas(null)->where('pastas.id', $idResultado)->get()->getRowArray();
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível salvar a alteração.');

            $resumo = $resultado === null ? null : [
                'id' => (string) $resultado['id'], 'nome' => $resultado['nome'], 'visibilidade' => $resultado['visibilidade'],
                'pasta_pai_id' => $resultado['pasta_pai_id'] === null ? null : (string) $resultado['pasta_pai_id'],
                'visivel' => $vinculo === null || $resultado['visibilidade'] !== 'privada' || (string) $resultado['criada_por'] === $vinculo,
            ];
            return ['pasta' => $resumo, 'historico' => [
                'id' => $idResultado, 'antes' => $dados['acao'] === 'subpasta' ? null : $pasta,
                'depois' => $depois, 'compartilhamentos' => $compartilhamentos,
            ]];
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Impede movimentos concorrentes, ciclos e destinos de outros setores. */
    private function validarDestinoPasta(array $pasta, string $origem, string $destino, ?string $vinculo): ?string
    {
        if ((string) $pasta['pasta_pai_id'] !== $origem) throw new DomainException('A pasta já mudou de lugar. Atualize a página.');
        $visitados = [(string) $pasta['id'] => true];
        for ($id = $destino; $id !== ''; $id = (string) $superior['pasta_pai_id']) {
            if (isset($visitados[$id])) throw new DomainException('Uma pasta não pode ser movida para dentro dela ou de suas subpastas.');
            $visitados[$id] = true;
            $superior = $this->consultaPastas($vinculo)->where('pastas.id', $id)->get()->getRowArray();
            if (!$superior) throw new DomainException('O destino não está disponível neste setor.');
        }
        return $destino === '' ? null : $destino;
    }

    /** Lista documentos do processo sem expor conteúdo ou chaves do armazenamento. */
    public function documentos(string $processo, ?string $vinculo): array
    {
        $consulta = $this->banco->table('documentos d')->select('d.id, d.numero_completo, d.titulo, d.criado_em,
            tipo.nome AS tipo, estado.nome AS situacao, usuario.nome AS autor, setor.nome AS setor, v.numero_versao,
            COALESCE(original.nome_original, final.nome_original) AS nome_arquivo')
            ->join('tipos_documento tipo', 'tipo.organizacao_id = d.organizacao_id AND tipo.id = d.tipo_documento_id')
            ->join('status_documento estado', 'estado.organizacao_id = d.organizacao_id AND estado.id = d.status_id')
            ->join('setores setor', 'setor.organizacao_id = d.organizacao_id AND setor.id = d.setor_origem_id')
            ->join('vinculos_organizacao autor', 'autor.organizacao_id = d.organizacao_id AND autor.id = d.criado_por', 'left')
            ->join('usuarios usuario', 'usuario.id = autor.usuario_id', 'left')
            ->join('documento_versoes v', 'v.organizacao_id = d.organizacao_id AND v.id = d.versao_atual_id AND v.documento_id = d.id', 'left')
            ->join('arquivos original', 'original.organizacao_id = d.organizacao_id AND original.id = v.arquivo_original_id', 'left')
            ->join('arquivos final', 'final.organizacao_id = d.organizacao_id AND final.id = v.arquivo_final_id', 'left')
            ->where(['d.organizacao_id' => $this->empresa, 'd.processo_principal_id' => $processo]);
        if ($vinculo !== null) $consulta->where(PoliticaProcesso::condicao($this->banco, 'd', $vinculo, true), null, false);
        $consulta->select('(SELECT CASE WHEN pa.herda_processo = 1 THEN NULL ELSE pa.nivel END FROM politicas_acesso pa
            WHERE pa.organizacao_id = d.organizacao_id AND pa.documento_id = d.id ORDER BY pa.id DESC LIMIT 1) AS nivel', false);
        return $consulta->orderBy('d.criado_em')->orderBy('d.id')->get()->getResultArray();
    }

    /** Grava a pasta no setor ativo, usando um vínculo da própria empresa. */
    public function cadastrarPasta(string $nome, string $vinculo): ?array
    {
        if ($this->setorAtivo === '') return null;
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) return null;
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $this->banco->table('pastas')->insert([
                'organizacao_id' => $this->empresa,
                'nome' => $this->nomeDisponivelPasta($nome, null),
                'setor_id' => $this->setorAtivo,
                'visibilidade' => 'setor',
                'criada_por' => $vinculo,
                'criado_em' => date('Y-m-d H:i:s'),
            ]);
            $id = (string) $this->banco->insertID();
            $pasta = $this->consultaPastas(null)->where('pastas.id', $id)->get()->getRowArray();
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) {
                $this->banco->transRollback();
                return null;
            }
            return ['id' => $id, 'antes' => null, 'depois' => $pasta, 'compartilhamentos' => []];
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Reserva o primeiro nome livre no mesmo nível, ignorando a própria pasta ao renomear. */
    private function nomeDisponivelPasta(string $nome, ?string $pai, ?string $pastaAtual = null): string
    {
        $consulta = $this->consultaPastas(null)->select('pastas.nome')->where('pastas.pasta_pai_id', $pai);
        if ($pastaAtual !== null) $consulta->where('pastas.id !=', $pastaAtual);
        $irmas = $consulta->get()->getResultArray();
        $ocupados = array_fill_keys(array_map('mb_strtolower', array_column($irmas, 'nome')), true);
        $candidato = $nome;
        for ($numero = 2; isset($ocupados[mb_strtolower($candidato)]); $numero++) {
            $sufixo = " ($numero)";
            $candidato = mb_substr($nome, 0, 200 - strlen($sufixo)) . $sufixo;
        }
        return $candidato;
    }

    /** Restaura um estado da última operação somente se o banco ainda estiver no estado esperado. */
    public function reverterPasta(array $historico, bool $refazer, ?string $vinculo): array
    {
        $esperado = $historico[$refazer ? 'antes' : 'depois'];
        $destino = $historico[$refazer ? 'depois' : 'antes'];
        $id = $historico['id'];
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new DomainException('Não foi possível iniciar a reversão.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $atual = $this->consultaPastas(null)->where('pastas.id', $id)->get()->getRowArray();
            if ($atual != $esperado) throw new DomainException('A pasta foi alterada em outra operação. Atualize a página.');
            $escopo = ['organizacao_id' => $this->empresa, 'pasta_id' => $id];
            $compartilhamentos = $this->banco->table('pasta_compartilhamentos');
            if ($destino === null) {
                if ($this->consultaPastas(null)->where('pastas.pasta_pai_id', $id)->countAllResults()) {
                    throw new DomainException('A pasta agora contém subpastas.');
                }
                foreach (['pasta_processos', 'pasta_documentos'] as $tabela) {
                    if ($this->banco->table($tabela)->where($escopo)->countAllResults()) throw new DomainException('A pasta agora contém itens vinculados.');
                }
                if ($compartilhamentos->where($escopo)->orderBy('id')->get()->getResultArray() != $historico['compartilhamentos']) {
                    throw new DomainException('O compartilhamento da pasta foi alterado.');
                }
                $compartilhamentos->where($escopo)->delete();
                $this->consultaPastas(null)->where('pastas.id', $id)->delete();
            } else {
                $pai = (string) $destino['pasta_pai_id'];
                if ($atual === null) {
                    if ($pai !== '' && !$this->consultaPastas($vinculo)->where('pastas.id', $pai)->countAllResults()) {
                        throw new DomainException('A pasta superior original não está mais disponível.');
                    }
                    $this->banco->table('pastas')->insert($destino);
                    foreach ($historico['compartilhamentos'] as $compartilhamento) $compartilhamentos->insert($compartilhamento);
                } else {
                    if ($pai !== (string) $atual['pasta_pai_id']) $this->validarDestinoPasta($atual, (string) $atual['pasta_pai_id'], $pai, $vinculo);
                    $this->consultaPastas(null)->where('pastas.id', $id)->update(array_intersect_key($destino, array_flip(['nome', 'visibilidade', 'pasta_pai_id', 'atualizado_em'])));
                }
            }
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new DomainException('Não foi possível concluir a reversão.');
            return $this->explorador($vinculo)['pastasReais'];
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }
}
