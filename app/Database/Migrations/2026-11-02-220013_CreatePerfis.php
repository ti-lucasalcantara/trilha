<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerfis extends Migration
{
    /** Cria a tabela perfis e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_302192cec9b9a16b2977');
        $this->forge->addUniqueKey(['organizacao_id', 'nome'], 'uq_170474622689a0fbb76d');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_d624026d4882ca46ab5b');
        $this->forge->createTable('perfis', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_4fa1a34653468af670e5');
        $this->forge->processIndexes('perfis');
    }

    /** Reverte a tabela perfis e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('perfis', 'fk_4fa1a34653468af670e5');
        $this->forge->dropTable('perfis');
    }
}
