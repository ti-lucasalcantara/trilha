<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUsuarios extends Migration
{
    /** Cria a tabela usuarios e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => false],
            'email_verificado_em' => ['type' => 'DATETIME', 'null' => true],
            'senha_hash' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'idioma' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['email'], 'uq_82a77442fd41e634ae9f');
        $this->forge->addKey(['status'], false, false, 'ix_cc781994fc9aa75cefee');
        $this->forge->createTable('usuarios', false, ['ENGINE' => 'InnoDB']);
    }

    /** Reverte a tabela usuarios e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropTable('usuarios');
    }
}
