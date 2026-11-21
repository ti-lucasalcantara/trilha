<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class NumerarDocumentosExistentes extends Migration
{
    public function up()
    {
        $documentos = $this->db->protectIdentifiers('documentos', true);
        $sequencias = $this->db->protectIdentifiers('sequencias_documento', true);
        $this->db->query("
            UPDATE {$documentos} d
            JOIN (
                SELECT pendentes.id, pendentes.ano,
                    GREATEST(COALESCE(emitidos.ultimo, 0), COALESCE(s.ultimo_numero, 0)) + pendentes.posicao AS numero
                FROM (
                    SELECT id, organizacao_id, tipo_documento_id, YEAR(criado_em) AS ano,
                        ROW_NUMBER() OVER (
                            PARTITION BY organizacao_id, tipo_documento_id, YEAR(criado_em) ORDER BY criado_em, id
                        ) AS posicao
                    FROM {$documentos}
                    WHERE numero_sequencial IS NULL AND numero_completo IS NULL
                ) pendentes
                LEFT JOIN (
                    SELECT organizacao_id, tipo_documento_id, periodo_numeracao, MAX(numero_sequencial) AS ultimo
                    FROM {$documentos} GROUP BY organizacao_id, tipo_documento_id, periodo_numeracao
                ) emitidos ON emitidos.organizacao_id = pendentes.organizacao_id
                    AND emitidos.tipo_documento_id = pendentes.tipo_documento_id
                    AND CAST(emitidos.periodo_numeracao AS BINARY) = CAST(pendentes.ano AS BINARY)
                LEFT JOIN {$sequencias} s ON s.organizacao_id = pendentes.organizacao_id
                    AND s.tipo_documento_id = pendentes.tipo_documento_id
                    AND CAST(s.periodo AS BINARY) = CAST(pendentes.ano AS BINARY)
            ) novos ON novos.id = d.id
            SET d.numero_sequencial = novos.numero, d.periodo_numeracao = novos.ano,
                d.numero_completo = CONCAT('DOC-T', d.tipo_documento_id, '-',
                    LPAD(novos.numero, GREATEST(4, LENGTH(novos.numero)), '0'), '/', novos.ano)
            WHERE d.numero_sequencial IS NULL AND d.numero_completo IS NULL
        ");
    }

    public function down()
    {
        // Números já emitidos devem permanecer vinculados aos documentos.
    }
}
