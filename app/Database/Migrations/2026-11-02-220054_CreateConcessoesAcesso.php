<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConcessoesAcesso extends Migration
{
    /** Cria a tabela concessoes_acesso e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'politica_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'permite_visualizar' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'permite_download' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'permite_editar' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'permite_tramitar' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'expira_em' => ['type' => 'DATETIME', 'null' => true],
            'revogado_em' => ['type' => 'DATETIME', 'null' => true],
            'concedido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'motivo' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_ca64c2e7779ba40e3572');
        $this->forge->addKey(['organizacao_id', 'expira_em'], false, false, 'ix_27139b98535e5fbbdbe7');
        $this->forge->createTable('concessoes_acesso', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('concessoes_acesso') . '` ADD CONSTRAINT `ck_cd32de504611e482d95c` CHECK ((vinculo_id IS NOT NULL) + (setor_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_56abfa28981b7fe971b3');
        $this->forge->processIndexes('concessoes_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'politica_id'], 'politicas_acesso', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_4fde7113d51babaa3b24');
        $this->forge->processIndexes('concessoes_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d87865e16c4b5e705c2e');
        $this->forge->processIndexes('concessoes_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d52e57c527398eb0e2e9');
        $this->forge->processIndexes('concessoes_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'concedido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3b33b51a6f29ee1d020b');
        $this->forge->processIndexes('concessoes_acesso');
    }

    /** Reverte a tabela concessoes_acesso e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('concessoes_acesso', 'fk_3b33b51a6f29ee1d020b');
        $this->forge->dropForeignKey('concessoes_acesso', 'fk_d52e57c527398eb0e2e9');
        $this->forge->dropForeignKey('concessoes_acesso', 'fk_d87865e16c4b5e705c2e');
        $this->forge->dropForeignKey('concessoes_acesso', 'fk_4fde7113d51babaa3b24');
        $this->forge->dropForeignKey('concessoes_acesso', 'fk_56abfa28981b7fe971b3');
        $this->forge->dropTable('concessoes_acesso');
    }
}
