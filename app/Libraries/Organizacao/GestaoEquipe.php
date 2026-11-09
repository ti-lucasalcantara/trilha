<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;
use RuntimeException;
use Throwable;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Admin\EstruturaSimulada;

/** Gerencia a equipe e os perfis sem alterar contas de outras organizações. */
class GestaoEquipe
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa)
    {
    }

    /** Lista o catálogo de permissões e os perfis configurados pela organização. */
    public function painel(): array
    {
        $perfis = new PerfisOrganizacao($this->banco);
        $atribuicoes = $this->banco->table('perfil_permissoes')->where('organizacao_id', $this->empresa)->get()->getResultArray();
        $porPerfil = [];
        foreach ($atribuicoes as $atribuicao) $porPerfil[$atribuicao['perfil_id']][] = (string) $atribuicao['permissao_id'];

        return [
            'perfisCadastrados' => $perfis->listar($this->empresa),
            'fundador' => $perfis->administrador($this->empresa),
            'catalogo' => $this->banco->table('permissoes')->orderBy('modulo')->orderBy('descricao')->get()->getResultArray(),
            'permissoesConfiguradas' => $porPerfil,
        ];
    }

    /** Adapta os cadastros reais ao mesmo formato usado na tela demonstrativa. */
    public function dadosConfiguracoes(): array
    {
        $painel = $this->painel();
        $estrutura = (new EstruturaSimulada($this->banco, $this->empresa))->listar();
        $pessoas = [];
        foreach ((new ConsultaEmpresa($this->banco, $this->empresa))->usuarios() as $usuario) {
            $pessoas[(string) $usuario['vinculo_id']] = [
                'id' => (string) $usuario['id'], 'nome' => $usuario['nome'], 'email' => $usuario['email'], 'setores' => [],
            ];
        }
        $agora = date('Y-m-d H:i:s');
        $vinculos = $this->banco->table('vinculos_setor')->select('vinculo_id, setor_id')->where('organizacao_id', $this->empresa)
            ->where('inicio_em <=', $agora)->groupStart()->where('fim_em', null)->orWhere('fim_em >', $agora)->groupEnd()->get()->getResultArray();
        foreach ($vinculos as $vinculo) {
            if (isset($pessoas[$vinculo['vinculo_id']])) $pessoas[$vinculo['vinculo_id']]['setores'][] = (string) $vinculo['setor_id'];
        }
        $atribuicoes = [];
        foreach ($this->banco->table('atribuicoes_perfil')->where('organizacao_id', $this->empresa)->get()->getResultArray() as $atribuicao) {
            $pessoa = $pessoas[$atribuicao['vinculo_id']] ?? null;
            if (!$pessoa) continue;
            $atribuicoes[] = [
                'id' => (string) $atribuicao['id'], 'pessoa' => $pessoa['id'], 'perfil' => (string) $atribuicao['perfil_id'],
                'escopo' => $atribuicao['setor_id'] ? 'setor:' . $atribuicao['setor_id'] : ($atribuicao['unidade_id'] ? 'unidade:' . $atribuicao['unidade_id'] : 'organizacao'),
            ];
        }
        $formatarEstrutura = static fn (array $registro, string $superior) => [
            'id' => (string) $registro['id'], 'nome' => $registro['nome'], 'sigla' => $registro['sigla'],
            'status' => $registro['status'], 'superior' => (string) $registro[$superior],
        ];
        return [
            'dados' => [
                'unidades' => array_map(static fn ($unidade) => $formatarEstrutura($unidade, 'unidade_superior_id'), $estrutura['unidades']),
                'setores' => array_map(static fn ($setor) => $formatarEstrutura($setor, 'setor_superior_id') + ['unidade' => (string) $setor['unidade_id']], $estrutura['setores']),
                'pessoas' => array_values($pessoas),
                'perfis' => array_map(static fn ($perfil) => [
                    'id' => (string) $perfil['id'], 'nome' => $perfil['nome'], 'descricao' => $perfil['descricao'] ?? '',
                    'protegido' => (string) $perfil['id'] === (string) ($painel['fundador']['perfil_id'] ?? ''),
                    'status' => $perfil['status'], 'permissoes' => $painel['permissoesConfiguradas'][$perfil['id']] ?? [],
                ], $painel['perfisCadastrados']),
                'atribuicoes' => $atribuicoes,
            ],
            'catalogo' => array_map(static fn ($permissao) => ['id' => (string) $permissao['id'], 'nome' => $permissao['descricao'], 'modulo' => $permissao['modulo']], $painel['catalogo']),
        ];
    }

    /** Cria uma conta nova e atribui o perfil selecionado em uma transação. */
    public function cadastrarUsuario(string $nome, string $email, string $senha, string $perfil): void
    {
        $this->gravar(function (string $agora) use ($nome, $email, $senha, $perfil): void {
            $this->banco->table('usuarios')->insert([
                'nome' => $nome, 'email' => $email, 'senha_hash' => password_hash($senha, PASSWORD_DEFAULT),
                'status' => 'ativo', 'criado_em' => $agora,
            ]);
            $usuario = $this->banco->insertID();
            $this->banco->table('vinculos_organizacao')->insert([
                'organizacao_id' => $this->empresa, 'usuario_id' => $usuario, 'tipo_vinculo' => 'interno',
                'status' => 'ativo', 'inicio_em' => $agora, 'criado_em' => $agora,
            ]);
            (new PerfisOrganizacao($this->banco))->atribuir($this->empresa, (string) $this->banco->insertID(), $perfil, 'ativo', $agora);
        });
    }

    /** Salva um perfil comum e sincroniza as permissões de catálogo escolhidas. */
    public function salvarPerfil(string $id, string $nome, string $descricao, array $permissoes): void
    {
        $this->gravar(function (string $agora) use ($id, $nome, $descricao, $permissoes): void {
            if (strcasecmp($nome, 'Administrador') === 0) throw new DomainException('O nome Administrador é reservado ao fundador.');
            $tabela = $this->banco->table('perfis');
            $dados = ['nome' => $nome, 'descricao' => $descricao ?: null];
            if ($id === '') {
                $tabela->insert($dados + ['organizacao_id' => $this->empresa, 'status' => 'ativo', 'criado_em' => $agora]);
                $id = (string) $this->banco->insertID();
            } else {
                $this->validarPerfil($id);
                $tabela->where(['id' => $id, 'organizacao_id' => $this->empresa])->update($dados + ['atualizado_em' => $agora]);
            }

            if ($permissoes && $this->banco->table('permissoes')->whereIn('id', $permissoes)->countAllResults() !== count($permissoes)) {
                throw new DomainException('Uma das permissões selecionadas não está disponível.');
            }
            $vinculos = $this->banco->table('perfil_permissoes');
            $escopo = ['organizacao_id' => $this->empresa, 'perfil_id' => $id];
            $vinculos->where($escopo)->delete();
            foreach ($permissoes as $permissao) $vinculos->insert($escopo + ['permissao_id' => $permissao, 'criado_em' => $agora]);
        });
    }

    /** Remove perfis comuns sem usuários vinculados. */
    public function excluirPerfil(string $id): void
    {
        $this->gravar(function () use ($id): void {
            $this->validarPerfil($id);
            $escopo = ['organizacao_id' => $this->empresa, 'perfil_id' => $id];
            if ($this->banco->table('atribuicoes_perfil')->where($escopo)->countAllResults()) {
                throw new DomainException('O perfil ainda possui usuários vinculados.');
            }
            $this->banco->table('perfil_permissoes')->where($escopo)->delete();
            $this->banco->table('perfis')->where(['organizacao_id' => $this->empresa, 'id' => $id])->delete();
        });
    }

    /** Impede alterações no perfil reservado e em registros de outras empresas. */
    private function validarPerfil(string $id): void
    {
        $fundador = (new PerfisOrganizacao($this->banco))->administrador($this->empresa);
        if ((string) ($fundador['perfil_id'] ?? '') === $id) throw new DomainException('O perfil Administrador fundador é protegido.');
        if (!$this->banco->table('perfis')->where(['organizacao_id' => $this->empresa, 'id' => $id])->countAllResults()) {
            throw new DomainException('Perfil indisponível nesta organização.');
        }
    }

    /** Serializa a gestão da empresa e reverte a operação inteira em caso de falha. */
    private function gravar(callable $operacao): void
    {
        $this->banco->transException(true);
        if (!$this->banco->transBegin()) throw new RuntimeException('Falha ao iniciar a gravação.');
        try {
            $this->banco->query('SELECT id FROM organizacoes WHERE id = ? FOR UPDATE', [$this->empresa]);
            $operacao(date('Y-m-d H:i:s'));
            if (!$this->banco->transStatus() || !$this->banco->transCommit()) throw new RuntimeException('Falha ao concluir a gravação.');
        } catch (Throwable $erro) {
            $this->banco->transRollback();
            throw $erro;
        }
    }
}
