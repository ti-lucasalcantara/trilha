<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;

/** Centraliza os níveis de acesso e as condições de leitura das consultas internas. */
class PoliticaProcesso
{
    public const NIVEIS = ['interno' => 'Interno', 'restrito' => 'Restrito', 'confidencial' => 'Confidencial'];

    /** Usa a política mais recente e valida as concessões que ainda estão vigentes. */
    public static function condicao(BaseConnection $banco, string $alias, string $vinculo, bool $documento = false): string
    {
        $pessoa = $banco->escape($vinculo);
        $agora = $banco->escape(date('Y-m-d H:i:s'));
        $alvo = $documento ? 'documento_id' : 'processo_id';
        $processo = $documento ? '' : 'AND politica.documento_id IS NULL';
        $heranca = $documento ? 'OR vigente.herda_processo = 1' : '';
        return "COALESCE((SELECT (
            vigente.nivel = 'interno' {$heranca}
            OR EXISTS (SELECT 1 FROM concessoes_acesso c
                WHERE c.organizacao_id = vigente.organizacao_id AND c.politica_id = vigente.id
                AND c.permite_visualizar = 1 AND c.inicio_em <= {$agora}
                AND c.revogado_em IS NULL AND (c.expira_em IS NULL OR c.expira_em > {$agora})
                AND ((vigente.nivel = 'confidencial' AND c.vinculo_id = {$pessoa})
                    OR (vigente.nivel = 'restrito' AND EXISTS (
                        SELECT 1 FROM vinculos_setor vs JOIN setores s ON s.organizacao_id = vs.organizacao_id AND s.id = vs.setor_id
                        JOIN unidades u ON u.organizacao_id = s.organizacao_id AND u.id = s.unidade_id
                        WHERE vs.organizacao_id = c.organizacao_id AND vs.setor_id = c.setor_id AND vs.vinculo_id = {$pessoa}
                        AND s.status = 'ativo' AND u.status = 'ativo'
                        AND vs.inicio_em <= {$agora} AND (vs.fim_em IS NULL OR vs.fim_em > {$agora})))))
            ) FROM politicas_acesso vigente WHERE vigente.id = (
                SELECT MAX(politica.id) FROM politicas_acesso politica
                WHERE politica.organizacao_id = {$alias}.organizacao_id AND politica.{$alvo} = {$alias}.id {$processo}
            ) AND vigente.organizacao_id = {$alias}.organizacao_id), 1) = 1";
    }
}
