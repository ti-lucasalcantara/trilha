<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;

/** Resolve o setor ativo por organização e por vínculo de trabalho. */
class ContextoSetor
{
    public function __construct(private readonly BaseConnection $banco, private readonly string $empresa, private readonly ?string $vinculo)
    {
    }

    /** Agrupa setores ativos e recupera a seleção que ainda é válida. */
    public function obter(): array
    {
        $consulta = $this->banco->table('setores s')
            ->select('s.id, s.nome, s.sigla, u.id AS unidade_id, u.nome AS unidade')
            ->join('unidades u', 'u.organizacao_id = s.organizacao_id AND u.id = s.unidade_id')
            ->where(['s.organizacao_id' => $this->empresa, 's.status' => 'ativo', 'u.status' => 'ativo']);
        if ($this->vinculo !== null) {
            $agora = date('Y-m-d H:i:s');
            $consulta->distinct()->join('vinculos_setor vs', 'vs.organizacao_id = s.organizacao_id AND vs.setor_id = s.id')
                ->where('vs.vinculo_id', $this->vinculo)->where('vs.inicio_em <=', $agora)
                ->groupStart()->where('vs.fim_em', null)->orWhere('vs.fim_em >', $agora)->groupEnd();
        }

        $unidades = [];
        $setores = [];
        foreach ($consulta->orderBy('u.nome')->orderBy('s.nome')->orderBy('s.id')->get()->getResultArray() as $registro) {
            $unidade = (string) $registro['unidade_id'];
            $id = (string) $registro['id'];
            $unidades[$unidade] ??= ['id' => $unidade, 'nome' => $registro['unidade'], 'setores' => []];
            $setores[$id] = ['id' => $id, 'nome' => $registro['nome'], 'sigla' => $registro['sigla'], 'unidade' => $unidade];
            $unidades[$unidade]['setores'][] = $setores[$id];
        }
        $selecionado = session()->get($this->chave());
        return ['unidades' => array_values($unidades), 'setores' => $setores,
            'setor' => $setores[$selecionado] ?? (reset($setores) ?: null)];
    }

    /** Valida a combinação antes de persistir a seleção na sessão. */
    public function selecionar(string $unidade, string $setor): bool
    {
        $opcoes = $this->obter();
        if (($opcoes['setores'][$setor]['unidade'] ?? null) !== $unidade) return false;
        session()->set($this->chave(), $setor);
        return true;
    }

    /** Considera os destinatários da última tramitação e, na ausência deles, a origem. */
    public static function condicaoProcessos(BaseConnection $banco, string $setor, string $alias): string
    {
        if ($setor === '') return '1 = 0';
        $valor = $banco->escape($setor);
        $destinatarios = "SELECT 1 FROM tramitacao_destinatarios destino
            WHERE destino.organizacao_id = {$alias}.organizacao_id
            AND destino.tramitacao_id = (SELECT MAX(t.id) FROM tramitacoes t
                WHERE t.organizacao_id = {$alias}.organizacao_id AND t.processo_id = {$alias}.id AND t.documento_id IS NULL)";
        return "(EXISTS ({$destinatarios} AND destino.setor_id = {$valor})
            OR ({$alias}.setor_origem_id = {$valor} AND NOT EXISTS ({$destinatarios})))";
    }

    private function chave(): string
    {
        return 'setor_trabalho_' . $this->empresa . '_' . ($this->vinculo ?? 'administracao');
    }
}
