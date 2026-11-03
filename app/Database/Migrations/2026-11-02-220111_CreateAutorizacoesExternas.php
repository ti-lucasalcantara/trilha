<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAutorizacoesExternas extends Migration
{
    /** Cria a tabela autorizacoes_externas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_externo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'concedida_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'expira_em' => ['type' => 'DATETIME', 'null' => false],
            'revogada_em' => ['type' => 'DATETIME', 'null' => true],
            'motivo' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_cf43ed81dd46a8d59102');
        $this->forge->addKey(['organizacao_id', 'expira_em'], false, false, 'ix_7f0ce49871a1c21f95ec');
        $this->forge->createTable('autorizacoes_externas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2f149d789917b4100049');
        $this->forge->processIndexes('autorizacoes_externas');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_externo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_8d403c7816a59cc50572');
        $this->forge->processIndexes('autorizacoes_externas');
        $this->forge->addForeignKey(['organizacao_id', 'concedida_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_411fb35a4c9f869a2f4d');
        $this->forge->processIndexes('autorizacoes_externas');
    }

    /** Reverte a tabela autorizacoes_externas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('autorizacoes_externas', 'fk_411fb35a4c9f869a2f4d');
        $this->forge->dropForeignKey('autorizacoes_externas', 'fk_8d403c7816a59cc50572');
        $this->forge->dropForeignKey('autorizacoes_externas', 'fk_2f149d789917b4100049');
        $this->forge->dropTable('autorizacoes_externas');
    }
}
