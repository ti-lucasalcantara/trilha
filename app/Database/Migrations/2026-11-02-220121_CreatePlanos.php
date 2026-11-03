<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanos extends Migration
{
    /** Cria a tabela planos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status'], false, false, 'ix_05b5b990bc1cedfc6c33');
        $this->forge->createTable('planos', false, ['ENGINE' => 'InnoDB']);
    }

    /** Reverte a tabela planos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropTable('planos');
    }
}
