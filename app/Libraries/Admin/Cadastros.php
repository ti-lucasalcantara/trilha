<?php

namespace App\Libraries\Admin;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;
use DomainException;
use RuntimeException;
use Throwable;

/** Persiste os cadastros administrativos no esquema existente. */
class Cadastros
{
    private const TABELAS = ['clientes' => 'organizacoes', 'planos' => 'planos', 'usuarios' => 'usuarios'];

    public function __construct(private readonly BaseConnection $banco)
    {
    }

    /** Conta os registros sem carregar detalhes no dashboard. */
    public function contar(string $cadastro): int
    {
        return $this->banco->table(self::TABELAS[$cadastro])->countAllResults();
    }

    /** Lista registros com as relações usadas no painel. */
    public function listar(string $cadastro): array
    {
        $consulta = $this->banco->table(self::TABELAS[$cadastro] . ' registro');
        $consulta->select(match ($cadastro) {
            'clientes' => 'registro.id, registro.nome, registro.nome_fantasia, registro.pais, registro.idioma, registro.fuso_horario, registro.status',
            'planos' => 'registro.id, registro.nome, registro.status',
            'usuarios' => 'registro.id, registro.nome, registro.email, registro.idioma, registro.status',
        });

        if ($cadastro === 'clientes') {
            $consulta->select('plano.nome AS plano, versao.id AS plano_versao_id')
                ->join('contratacoes contrato', "contrato.id = (SELECT MAX(c.id) FROM contratacoes c WHERE c.organizacao_id = registro.id AND c.encerramento_em IS NULL AND c.status = 'ativo')", 'left', false)
                ->join('plano_versoes versao', 'versao.id = contrato.plano_versao_id', 'left')
                ->join('planos plano', 'plano.id = versao.plano_id', 'left')
                ->select('(SELECT COUNT(*) FROM vinculos_organizacao v WHERE v.organizacao_id = registro.id AND v.fim_em IS NULL) AS usuarios', false);
        } elseif ($cadastro === 'planos') {
            $consulta->select('versao.id AS plano_versao_id, versao.preco, versao.moeda, versao.periodicidade, versao.limites')
                ->join('plano_versoes versao', 'versao.id = (SELECT MAX(pv.id) FROM plano_versoes pv WHERE pv.plano_id = registro.id AND pv.vigente_ate IS NULL AND pv.vigente_desde <= ' . $this->banco->escape(date('Y-m-d H:i:s')) . ')', 'left', false);
        } else {
            $consulta->select('(SELECT COUNT(*) FROM vinculos_organizacao v WHERE v.usuario_id = registro.id AND v.fim_em IS NULL) AS organizacoes', false);
        }

        return $consulta->orderBy('registro.nome')->orderBy('registro.id')->get()->getResultArray();
    }

    /** Obtém um registro e prepara as relações editáveis. */
    public function obter(string $cadastro, string $id): array
    {
        $registro = $this->banco->table(self::TABELAS[$cadastro])->where('id', $id)->get()->getRowArray();
        if (!$registro) throw PageNotFoundException::forPageNotFound();

        if ($cadastro === 'clientes') {
            $registro['plano_versao_id'] = $this->contratoAtual($id)['plano_versao_id'] ?? '';
        } elseif ($cadastro === 'planos') {
            $versao = $this->versaoAtual($id);
            $registro['preco'] = $versao['preco'] ?? '';
            $limites = json_decode($versao['limites'] ?? '{}', true);
            $registro['armazenamento_gb'] = $limites['armazenamento_gb'] ?? '';
            $registro['usuarios'] = $limites['usuarios'] ?? '';
        } else {
            unset($registro['senha_hash']);
            $registro['organizacoes'] = array_map('strval', array_column($this->banco->table('vinculos_organizacao')
                ->select('organizacao_id')->where(['usuario_id' => $id, 'tipo_vinculo' => 'interno', 'fim_em' => null])
                ->get()->getResultArray(), 'organizacao_id'));
        }

        return $registro;
    }

    /** Salva o cadastro e seus vínculos como uma única operação. */
    public function salvar(string $cadastro, ?string $id, array $campos): void
    {
        $this->transacionar(function () use ($cadastro, $id, $campos): void {
            $agora = date('Y-m-d H:i:s');
            $tabela = self::TABELAS[$cadastro];
            $valores = ['nome' => $campos['nome'], 'status' => $campos['status']];
            if ($cadastro === 'clientes') {
                $valores += array_intersect_key($campos, array_flip(['nome_fantasia', 'pais', 'idioma', 'fuso_horario']));
            } elseif ($cadastro === 'usuarios') {
                $valores += ['email' => $campos['email'], 'idioma' => $campos['idioma'] ?: null];
                if ($campos['senha'] !== '') $valores['senha_hash'] = password_hash($campos['senha'], PASSWORD_DEFAULT);
            }

            if ($id === null) {
                $this->executar($this->banco->table($tabela)->insert($valores + ['criado_em' => $agora]));
                $id = (string) $this->banco->insertID();
            } else {
                $anterior = $this->bloquearRegistro($tabela, $id);
                if ($cadastro === 'usuarios' && $anterior['email'] !== $valores['email']) $valores['email_verificado_em'] = null;
                $this->executar($this->banco->table($tabela)->where('id', $id)->update($valores + ['atualizado_em' => $agora]));
            }

            match ($cadastro) {
                'clientes' => $this->atualizarContratacao($id, $campos['plano_versao_id'], $agora),
                'planos' => $this->atualizarVersao($id, $campos, $agora),
                'usuarios' => $this->atualizarVinculos($id, $campos['organizacoes'], $agora),
            };
        });
    }

    /** Remove somente cadastros sem dependências protegidas pelo banco. */
    public function excluir(string $cadastro, string $id): void
    {
        $this->transacionar(function () use ($cadastro, $id): void {
            $tabela = self::TABELAS[$cadastro];
            $this->bloquearRegistro($tabela, $id);
            if ($cadastro === 'planos') {
                $this->executar($this->banco->table('plano_versoes')->where('plano_id', $id)->delete());
            }
            $this->executar($this->banco->table($tabela)->where('id', $id)->delete());
        });
    }

    /** Mantém contratos existentes em sua versão original do plano. */
    private function atualizarVersao(string $id, array $campos, string $agora): void
    {
        $atual = $this->versaoAtual($id, true);
        $limites = json_decode($atual['limites'] ?? '{}', true) ?: [];
        $novosLimites = array_replace($limites, ['armazenamento_gb' => (int) $campos['armazenamento_gb'], 'usuarios' => (int) $campos['usuarios']]);
        if ($atual && $atual['preco'] === $campos['preco'] && $limites === $novosLimites) return;

        if ($atual) {
            $this->executar($this->banco->table('plano_versoes')->where('id', $atual['id'])->update(['vigente_ate' => $agora, 'atualizado_em' => $agora]));
        }
        $this->executar($this->banco->table('plano_versoes')->insert([
            'plano_id' => $id, 'preco' => $campos['preco'], 'moeda' => $atual['moeda'] ?? 'BRL',
            'periodicidade' => $atual['periodicidade'] ?? 'mensal', 'limites' => json_encode($novosLimites, JSON_THROW_ON_ERROR),
            'vigente_desde' => $agora, 'criado_em' => $agora,
        ]));
    }

    /** Encerra o contrato anterior antes de registrar uma troca de plano. */
    private function atualizarContratacao(string $id, string $versao, string $agora): void
    {
        $atual = $this->contratoAtual($id);
        if ((string) ($atual['plano_versao_id'] ?? '') === $versao) return;

        if ($versao !== '') {
            $registro = $this->banco->table('plano_versoes v')->select('v.plano_id')
                ->join('planos p', 'p.id = v.plano_id')->where(['v.id' => $versao, 'v.vigente_ate' => null, 'p.status' => 'ativo'])
                ->where('v.vigente_desde <=', $agora)->get()->getRowArray();
            if (!$registro) throw new DomainException('Selecione uma versão vigente de um plano ativo.');
            $plano = $this->bloquearRegistro('planos', (string) $registro['plano_id']);
            if ($plano['status'] !== 'ativo' || (string) ($this->versaoAtual((string) $plano['id'], true)['id'] ?? '') !== $versao) {
                throw new DomainException('O plano mudou. Reabra o cadastro e selecione a versão vigente.');
            }
        }
        if ($atual) {
            $this->executar($this->banco->table('contratacoes')->where('id', $atual['id'])
                ->update(['status' => 'encerrado', 'encerramento_em' => $agora, 'atualizado_em' => $agora]));
        }
        if ($versao !== '') {
            $this->executar($this->banco->table('contratacoes')->insert([
                'organizacao_id' => $id, 'plano_versao_id' => $versao, 'status' => 'ativo',
                'inicio_em' => $agora, 'criado_em' => $agora,
            ]));
        }
    }

    /** Sincroniza os vínculos internos e preserva os vínculos de outros tipos. */
    private function atualizarVinculos(string $id, array $organizacoes, string $agora): void
    {
        $vinculos = $this->banco->table('vinculos_organizacao')->where('usuario_id', $id)->get()->getResultArray();
        $porOrganizacao = array_column($vinculos, null, 'organizacao_id');

        foreach ($organizacoes as $organizacao) {
            $vinculo = $porOrganizacao[$organizacao] ?? null;
            if ($vinculo && $vinculo['tipo_vinculo'] !== 'interno') {
                throw new DomainException('Já existe um vínculo de outro tipo com uma das organizações selecionadas.');
            }
            if (!$vinculo) {
                if (!$this->banco->table('organizacoes')->where('id', $organizacao)->countAllResults()) {
                    throw new DomainException('Uma das organizações selecionadas não existe.');
                }
                $this->executar($this->banco->table('vinculos_organizacao')->insert([
                    'organizacao_id' => $organizacao, 'usuario_id' => $id, 'tipo_vinculo' => 'interno',
                    'status' => 'ativo', 'inicio_em' => $agora, 'criado_em' => $agora,
                ]));
            } elseif ($vinculo['fim_em'] !== null) {
                $this->executar($this->banco->table('vinculos_organizacao')->where('id', $vinculo['id'])
                    ->update(['status' => 'ativo', 'fim_em' => null, 'atualizado_em' => $agora]));
            }
        }

        foreach ($vinculos as $vinculo) {
            if ($vinculo['tipo_vinculo'] !== 'interno' || $vinculo['fim_em'] !== null || in_array((string) $vinculo['organizacao_id'], $organizacoes, true)) continue;
            $this->executar($this->banco->table('vinculos_organizacao')->where('id', $vinculo['id'])
                ->update(['status' => 'inativo', 'fim_em' => $agora, 'atualizado_em' => $agora]));
        }
    }

    /** Busca o contrato em uso pela organização. */
    private function contratoAtual(string $id): ?array
    {
        return $this->banco->table('contratacoes')->where(['organizacao_id' => $id, 'encerramento_em' => null, 'status' => 'ativo'])
            ->orderBy('id', 'DESC')->get(1)->getRowArray();
    }

    /** Busca a versão vigente utilizada em novas contratações. */
    private function versaoAtual(string $id, bool $bloquear = false): ?array
    {
        $consulta = $this->banco->table('plano_versoes')->where(['plano_id' => $id, 'vigente_ate' => null])
            ->where('vigente_desde <=', date('Y-m-d H:i:s'))->orderBy('id', 'DESC')->limit(1);
        $resultado = $bloquear
            ? $this->banco->query($consulta->getCompiledSelect() . ' FOR UPDATE')
            : $consulta->get();
        return $resultado->getRowArray();
    }

    /** Serializa alterações no mesmo cadastro. */
    private function bloquearRegistro(string $tabela, string $id): array
    {
        $registro = $this->banco->query("SELECT * FROM {$tabela} WHERE id = ? FOR UPDATE", [$id])->getRowArray();
        if (!$registro) throw PageNotFoundException::forPageNotFound();
        return $registro;
    }

    /** Garante que falhas no banco revertam todas as etapas da operação. */
    private function transacionar(callable $operacao): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new RuntimeException('Falha ao abrir transação administrativa.');

        try {
            $operacao();
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new RuntimeException('Falha ao concluir transação administrativa.');
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }

    /** Trata também drivers que retornam false em vez de lançar exceção. */
    private function executar(bool $resultado): void
    {
        if (!$resultado) {
            $erro = $this->banco->error();
            throw new \CodeIgniter\Database\Exceptions\DatabaseException('Falha na gravação administrativa.', (int) $erro['code']);
        }
    }
}
