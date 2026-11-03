<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSequenciasDocumento extends Migration
{
    /** Cria a tabela sequencias_documento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'periodo' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => false],
            'ultimo_numero' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_72a79f49e0d57b93937c');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'periodo'], 'uq_10045b15bc1440c10341');
        $this->forge->createTable('sequencias_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_cbbc13fbfb78decaf07f');
        $this->forge->processIndexes('sequencias_documento');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_708980cc7961bd58fc4c');
        $this->forge->processIndexes('sequencias_documento');
    }

    /** Reverte a tabela sequencias_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('sequencias_documento', 'fk_708980cc7961bd58fc4c');
        $this->forge->dropForeignKey('sequencias_documento', 'fk_cbbc13fbfb78decaf07f');
        $this->forge->dropTable('sequencias_documento');
    }
}
