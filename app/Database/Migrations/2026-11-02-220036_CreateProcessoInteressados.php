<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoInteressados extends Migration
{
    /** Cria a tabela processo_interessados e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'interessado_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'papel' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_53946e033ed250ef43f4');
        $this->forge->createTable('processo_interessados', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_d3f0a320786ef794da6c');
        $this->forge->processIndexes('processo_interessados');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5d3af919657b16ce278c');
        $this->forge->processIndexes('processo_interessados');
        $this->forge->addForeignKey(['organizacao_id', 'interessado_id'], 'interessados', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_cb97036251d5e1da24b8');
        $this->forge->processIndexes('processo_interessados');
    }

    /** Reverte a tabela processo_interessados e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_interessados', 'fk_cb97036251d5e1da24b8');
        $this->forge->dropForeignKey('processo_interessados', 'fk_5d3af919657b16ce278c');
        $this->forge->dropForeignKey('processo_interessados', 'fk_d3f0a320786ef794da6c');
        $this->forge->dropTable('processo_interessados');
    }
}
