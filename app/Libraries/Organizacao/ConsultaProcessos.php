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

    /** Consulta os processos e metadados somente na organização corrente. */
    public function listar(?string $vinculo, ?string $id = null): array
    {
        $consulta = $this->banco->table('processos p')
            ->select('p.id, p.numero_completo AS numero, p.assunto, p.descricao, tipo.nome AS tipo, situacao.nome AS situacao, unidade.nome AS unidade, setor.nome AS setor, usuario.nome AS responsavel, p.unidade_origem_id AS unidadeEscopo, p.setor_origem_id AS setorEscopo, p.aberto_em AS abertura, p.atualizado_em AS atualizacao, p.encerrado_em')
            ->join('tipos_processo tipo', 'tipo.organizacao_id = p.organizacao_id AND tipo.id = p.tipo_processo_id')
            ->join('status_processo situacao', 'situacao.organizacao_id = p.organizacao_id AND situacao.id = p.status_id')
            ->join('unidades unidade', 'unidade.organizacao_id = p.organizacao_id AND unidade.id = p.unidade_origem_id')
            ->join('setores setor', 'setor.organizacao_id = p.organizacao_id AND setor.id = p.setor_origem_id')
            ->join('vinculos_organizacao autor', 'autor.organizacao_id = p.organizacao_id AND autor.id = p.aberto_por', 'left')
            ->join('usuarios usuario', 'usuario.id = autor.usuario_id', 'left')
            ->where('p.organizacao_id', $this->empresa)
            ->where(ContextoSetor::condicaoProcessos($this->banco, $this->setorAtivo, 'p'), null, false)
            ->select('(SELECT MIN(pr.vence_em) FROM prazos pr WHERE pr.organizacao_id = p.organizacao_id AND pr.processo_id = p.id AND pr.concluido_em IS NULL) AS prazo', false)
            ->select('(SELECT pa.nivel FROM politicas_acesso pa WHERE pa.organizacao_id = p.organizacao_id AND pa.processo_id = p.id AND pa.documento_id IS NULL ORDER BY pa.id DESC LIMIT 1) AS acesso', false)
            ->select("(SELECT GROUP_CONCAT(pp.pasta_id SEPARATOR ',') FROM pasta_processos pp WHERE pp.organizacao_id = p.organizacao_id AND pp.processo_id = p.id) AS pastas", false)
            ->select("(SELECT GROUP_CONCAT(i.nome SEPARATOR ', ') FROM processo_interessados pi JOIN interessados i ON i.organizacao_id = pi.organizacao_id AND i.id = pi.interessado_id WHERE pi.organizacao_id = p.organizacao_id AND pi.processo_id = p.id) AS interessado", false)
            ->select("(SELECT GROUP_CONCAT(DISTINCT s.nome SEPARATOR ',') FROM tramitacao_destinatarios d JOIN setores s ON s.organizacao_id = d.organizacao_id AND s.id = d.setor_id WHERE d.organizacao_id = p.organizacao_id AND d.tramitacao_id = (SELECT MAX(t.id) FROM tramitacoes t WHERE t.organizacao_id = p.organizacao_id AND t.processo_id = p.id AND t.documento_id IS NULL)) AS destinos_registrados", false)
            ->select('(SELECT MAX(t.enviada_em) FROM tramitacoes t WHERE t.organizacao_id = p.organizacao_id AND t.processo_id = p.id AND t.documento_id IS NULL) AS ultima_tramitacao', false);
        if ($id !== null) $consulta->where('p.id', $id);

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
            $processo['acompanhado'] = in_array((string) $processo['id'], array_map('strval', $acompanhados), true);
            foreach (['acesso', 'interessado', 'responsavel', 'pastas'] as $campo) $processo[$campo] ??= '';
            $processo['grupoPasta'] = $processo['pasta'] = '';
            return $processo;
        }, $consulta->orderBy('p.id', 'DESC')->get()->getResultArray());
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
    public function documentos(string $processo): array
    {
        return $this->banco->table('documentos')->select('numero_completo, titulo, criado_em')
            ->where(['organizacao_id' => $this->empresa, 'processo_principal_id' => $processo])
            ->orderBy('criado_em')->get()->getResultArray();
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
