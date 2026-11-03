<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTramitacoes extends Migration
{
    /** Cria a tabela tramitacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'unidade_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'remetente_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'finalidade' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'mensagem' => ['type' => 'TEXT', 'null' => true],
            'enviada_em' => ['type' => 'DATETIME', 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_8a6ce5c46d9ee3a0a8ee');
        $this->forge->createTable('tramitacoes', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('tramitacoes') . '` ADD CONSTRAINT `ck_e10878ad3ce8624ad8a8` CHECK ((processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0c12b242fbaa88e529b6');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_fca672ecd6b2a5480d9f');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_85d2aad46966e7c7dc4a');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_fca6cfe2653bee915f45');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_530111653f1db07d51fa');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'remetente_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d9359efdcd6f97f21b46');
        $this->forge->processIndexes('tramitacoes');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c21971937b300aec2f0c');
        $this->forge->processIndexes('tramitacoes');
    }

    /** Reverte a tabela tramitacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tramitacoes', 'fk_c21971937b300aec2f0c');
        $this->forge->dropForeignKey('tramitacoes', 'fk_d9359efdcd6f97f21b46');
        $this->forge->dropForeignKey('tramitacoes', 'fk_530111653f1db07d51fa');
        $this->forge->dropForeignKey('tramitacoes', 'fk_fca6cfe2653bee915f45');
        $this->forge->dropForeignKey('tramitacoes', 'fk_85d2aad46966e7c7dc4a');
        $this->forge->dropForeignKey('tramitacoes', 'fk_fca672ecd6b2a5480d9f');
        $this->forge->dropForeignKey('tramitacoes', 'fk_0c12b242fbaa88e529b6');
        $this->forge->dropTable('tramitacoes');
    }
}
