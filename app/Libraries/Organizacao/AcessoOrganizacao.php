<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Exceptions\PageNotFoundException;

/** Resolve os vínculos válidos da conta autenticada a cada acesso. */
class AcessoOrganizacao
{
    public const SESSAO = 'conta_organizacao_trilha';

    public function __construct(private readonly BaseConnection $banco)
    {
    }

    /** Lista as organizações acessíveis pelo usuário interno ativo. */
    public function vinculos(string $usuario, ?string $hash = null): array
    {
        $consulta = $this->banco->table('vinculos_organizacao v')
            ->select('o.id AS empresa, o.hash_publico AS hash, o.nome AS organizacao, u.nome AS usuario, v.id AS vinculo, a.vinculo_id AS fundador')
            ->join('organizacoes o', 'o.id = v.organizacao_id')
            ->join('usuarios u', 'u.id = v.usuario_id')
            ->join('administradores_organizacao a', 'a.organizacao_id = v.organizacao_id AND a.vinculo_id = v.id', 'left')
            ->where(['v.usuario_id' => $usuario, 'v.status' => 'ativo', 'v.tipo_vinculo' => 'interno', 'v.fim_em' => null, 'o.status' => 'ativo', 'u.status' => 'ativo'])
            ->where('v.inicio_em <=', date('Y-m-d H:i:s'));
        if ($hash !== null) $consulta->where('o.hash_publico', $hash);
        return $consulta->orderBy('o.nome')->get()->getResultArray();
    }

    /** Exige uma sessão real e valida o administrador pelo vínculo protegido. */
    public function contexto(string $hash, bool $exigirAdministrador = false): array
    {
        $usuario = session()->get(self::SESSAO);
        $contexto = is_string($usuario) && preg_match('/^[a-f0-9]{48}$/D', $hash) ? ($this->vinculos($usuario, $hash)[0] ?? null) : null;
        if (!$contexto || ($exigirAdministrador && !$contexto['fundador'])) throw PageNotFoundException::forPageNotFound();
        return $contexto;
    }

    /** Resolve somente permissões concedidas no âmbito da organização inteira. */
    public function consultas(array $contexto): array
    {
        $registros = $this->banco->table('atribuicoes_perfil a')
            ->select('permissao.codigo')->distinct()
            ->join('perfis p', 'p.organizacao_id = a.organizacao_id AND p.id = a.perfil_id')
            ->join('perfil_permissoes pp', 'pp.organizacao_id = p.organizacao_id AND pp.perfil_id = p.id')
            ->join('permissoes permissao', 'permissao.id = pp.permissao_id')
            ->where(['a.organizacao_id' => $contexto['empresa'], 'a.vinculo_id' => $contexto['vinculo'],
                'a.unidade_id' => null, 'a.setor_id' => null, 'p.status' => 'ativo'])
            ->get()->getResultArray();
        $codigos = array_column($registros, 'codigo');
        return ['processos', ...array_values(array_filter(['unidades', 'setores'],
            static fn ($modulo) => in_array('organizacao.' . $modulo . '.consultar', $codigos, true)))];
    }
}
