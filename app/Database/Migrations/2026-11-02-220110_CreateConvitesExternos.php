<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConvitesExternos extends Migration
{
    /** Cria a tabela convites_externos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => false],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'enviado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'expira_em' => ['type' => 'DATETIME', 'null' => false],
            'aceito_em' => ['type' => 'DATETIME', 'null' => true],
            'revogado_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_278862692baa0eb25190');
        $this->forge->addUniqueKey(['token_hash'], 'uq_61700af59894de9b2aa6');
        $this->forge->addKey(['organizacao_id', 'expira_em'], false, false, 'ix_677c2fd27d08d76fdaf3');
        $this->forge->createTable('convites_externos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2b79bb5fb0ab90b171df');
        $this->forge->processIndexes('convites_externos');
        $this->forge->addForeignKey(['organizacao_id', 'enviado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_39d58a3c69a01231740c');
        $this->forge->processIndexes('convites_externos');
    }

    /** Reverte a tabela convites_externos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('convites_externos', 'fk_39d58a3c69a01231740c');
        $this->forge->dropForeignKey('convites_externos', 'fk_2b79bb5fb0ab90b171df');
        $this->forge->dropTable('convites_externos');
    }
}
