<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConvitesOrganizacao extends Migration
{
    /** Cria a tabela convites_organizacao e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => false],
            'tipo_vinculo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'expira_em' => ['type' => 'DATETIME', 'null' => false],
            'aceito_em' => ['type' => 'DATETIME', 'null' => true],
            'revogado_em' => ['type' => 'DATETIME', 'null' => true],
            'convidado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1100ac3c9bb2173ae784');
        $this->forge->addUniqueKey(['token_hash'], 'uq_eabdcc040c7076a0520b');
        $this->forge->addKey(['organizacao_id', 'expira_em'], false, false, 'ix_36b38c2d86a7f8c10184');
        $this->forge->createTable('convites_organizacao', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_b46a0766ea605057adfa');
        $this->forge->processIndexes('convites_organizacao');
        $this->forge->addForeignKey(['organizacao_id', 'convidado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_32df8c3d562fb06b3510');
        $this->forge->processIndexes('convites_organizacao');
    }

    /** Reverte a tabela convites_organizacao e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('convites_organizacao', 'fk_32df8c3d562fb06b3510');
        $this->forge->dropForeignKey('convites_organizacao', 'fk_b46a0766ea605057adfa');
        $this->forge->dropTable('convites_organizacao');
    }
}
