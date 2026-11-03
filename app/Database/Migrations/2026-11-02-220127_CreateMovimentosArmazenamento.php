<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateMovimentosArmazenamento extends Migration
{
    /** Cria a tabela movimentos_armazenamento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'arquivo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'variacao_bytes' => ['type' => 'BIGINT', 'null' => false],
            'motivo' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a8c382151f2dabe49c5c');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_e5ffadf5f4bc3e6b9b93');
        $this->forge->createTable('movimentos_armazenamento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2b72eee6d3813959223b');
        $this->forge->processIndexes('movimentos_armazenamento');
        $this->forge->addForeignKey(['organizacao_id', 'arquivo_id'], 'arquivos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3d4ccc5811b86ce52e5d');
        $this->forge->processIndexes('movimentos_armazenamento');
    }

    /** Reverte a tabela movimentos_armazenamento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('movimentos_armazenamento', 'fk_3d4ccc5811b86ce52e5d');
        $this->forge->dropForeignKey('movimentos_armazenamento', 'fk_2b72eee6d3813959223b');
        $this->forge->dropTable('movimentos_armazenamento');
    }
}
