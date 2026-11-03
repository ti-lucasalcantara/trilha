<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInteressados extends Migration
{
    /** Cria a tabela interessados e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_pessoa' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'email' => ['type' => 'VARCHAR', 'constraint' => 254, 'null' => true],
            'telefone' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_c31cf58795003068ea08');
        $this->forge->createTable('interessados', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_701ef2520f1c57e160cf');
        $this->forge->processIndexes('interessados');
    }

    /** Reverte a tabela interessados e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('interessados', 'fk_701ef2520f1c57e160cf');
        $this->forge->dropTable('interessados');
    }
}
