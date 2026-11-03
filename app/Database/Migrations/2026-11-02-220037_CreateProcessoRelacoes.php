<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoRelacoes extends Migration
{
    /** Cria a tabela processo_relacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_destino_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_relacao' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'justificativa' => ['type' => 'TEXT', 'null' => true],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_5e0b6afdfb40172bbdc6');
        $this->forge->createTable('processo_relacoes', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('processo_relacoes') . '` ADD CONSTRAINT `ck_8d4eb0e091f06c9b9df1` CHECK (processo_origem_id <> processo_destino_id)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_15902555f6607c34732d');
        $this->forge->processIndexes('processo_relacoes');
        $this->forge->addForeignKey(['organizacao_id', 'processo_origem_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1472c48d0c931f379f75');
        $this->forge->processIndexes('processo_relacoes');
        $this->forge->addForeignKey(['organizacao_id', 'processo_destino_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_13e6be8699668d5465d9');
        $this->forge->processIndexes('processo_relacoes');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a7b681ab46a1d2c05f3d');
        $this->forge->processIndexes('processo_relacoes');
    }

    /** Reverte a tabela processo_relacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_relacoes', 'fk_a7b681ab46a1d2c05f3d');
        $this->forge->dropForeignKey('processo_relacoes', 'fk_13e6be8699668d5465d9');
        $this->forge->dropForeignKey('processo_relacoes', 'fk_1472c48d0c931f379f75');
        $this->forge->dropForeignKey('processo_relacoes', 'fk_15902555f6607c34732d');
        $this->forge->dropTable('processo_relacoes');
    }
}
