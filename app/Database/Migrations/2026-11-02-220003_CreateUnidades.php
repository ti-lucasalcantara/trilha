<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnidades extends Migration
{
    /** Cria a tabela unidades e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'sigla' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'unidade_superior_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9c85e30b828f5549dc21');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_fd2938fdaf0c356bea96');
        $this->forge->createTable('unidades', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_32a315406f81bc294c89');
        $this->forge->processIndexes('unidades');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_superior_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_08e0835cfa55dae791ec');
        $this->forge->processIndexes('unidades');
    }

    /** Reverte a tabela unidades e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('unidades', 'fk_08e0835cfa55dae791ec');
        $this->forge->dropForeignKey('unidades', 'fk_32a315406f81bc294c89');
        $this->forge->dropTable('unidades');
    }
}
