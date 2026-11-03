<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePermissoes extends Migration
{
    /** Cria a tabela permissoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'codigo' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'descricao' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'modulo' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['codigo'], 'uq_03d2c42ad204d4984a22');
        $this->forge->createTable('permissoes', false, ['ENGINE' => 'InnoDB']);
    }

    /** Reverte a tabela permissoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropTable('permissoes');
    }
}
