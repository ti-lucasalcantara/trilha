<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSequenciasProcesso extends Migration
{
    /** Cria a tabela sequencias_processo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ano' => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false],
            'ultimo_numero' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1b529c8c84550e2439ec');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'ano'], 'uq_d974be5589057c0bb772');
        $this->forge->createTable('sequencias_processo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_56a2b8351016de63a899');
        $this->forge->processIndexes('sequencias_processo');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id'], 'tipos_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0f2f70c6da593a92c0d8');
        $this->forge->processIndexes('sequencias_processo');
    }

    /** Reverte a tabela sequencias_processo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('sequencias_processo', 'fk_0f2f70c6da593a92c0d8');
        $this->forge->dropForeignKey('sequencias_processo', 'fk_56a2b8351016de63a899');
        $this->forge->dropTable('sequencias_processo');
    }
}
