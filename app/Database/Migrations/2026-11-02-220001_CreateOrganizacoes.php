<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganizacoes extends Migration
{
    /** Cria a tabela organizacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'nome_fantasia' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'pais' => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => false],
            'idioma' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'fuso_horario' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['status'], false, false, 'ix_c40d29ae0ea350d176b9');
        $this->forge->createTable('organizacoes', false, ['ENGINE' => 'InnoDB']);
    }

    /** Reverte a tabela organizacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropTable('organizacoes');
    }
}
