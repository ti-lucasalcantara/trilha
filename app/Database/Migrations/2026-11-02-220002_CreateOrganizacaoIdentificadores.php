<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganizacaoIdentificadores extends Migration
{
    /** Cria a tabela organizacao_identificadores e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'valor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'pais' => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1ffacea43dc8a0a4e417');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo', 'pais', 'valor'], 'uq_d96a0f8ce6051362e709');
        $this->forge->createTable('organizacao_identificadores', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_29c834971907d683b905');
        $this->forge->processIndexes('organizacao_identificadores');
    }

    /** Reverte a tabela organizacao_identificadores e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('organizacao_identificadores', 'fk_29c834971907d683b905');
        $this->forge->dropTable('organizacao_identificadores');
    }
}
