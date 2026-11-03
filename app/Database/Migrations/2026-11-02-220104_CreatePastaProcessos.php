<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePastaProcessos extends Migration
{
    /** Cria a tabela pasta_processos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'pasta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'incluido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_4fcdf2bff48a71177cb5');
        $this->forge->addUniqueKey(['organizacao_id', 'pasta_id', 'processo_id'], 'uq_f2070b421b27d3025121');
        $this->forge->createTable('pasta_processos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_218d1524d86c4b0f9c1d');
        $this->forge->processIndexes('pasta_processos');
        $this->forge->addForeignKey(['organizacao_id', 'pasta_id'], 'pastas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3725947467ecd7ca6846');
        $this->forge->processIndexes('pasta_processos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e6831f865f1aa9743078');
        $this->forge->processIndexes('pasta_processos');
        $this->forge->addForeignKey(['organizacao_id', 'incluido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_27871935f05ee1c778ad');
        $this->forge->processIndexes('pasta_processos');
    }

    /** Reverte a tabela pasta_processos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('pasta_processos', 'fk_27871935f05ee1c778ad');
        $this->forge->dropForeignKey('pasta_processos', 'fk_e6831f865f1aa9743078');
        $this->forge->dropForeignKey('pasta_processos', 'fk_3725947467ecd7ca6846');
        $this->forge->dropForeignKey('pasta_processos', 'fk_218d1524d86c4b0f9c1d');
        $this->forge->dropTable('pasta_processos');
    }
}
