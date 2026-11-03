<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUnidadeIdentificadores extends Migration
{
    /** Cria a tabela unidade_identificadores e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'valor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'pais' => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_4ae4163c4757217d5920');
        $this->forge->createTable('unidade_identificadores', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_adbbd7673c4e6be7a5b7');
        $this->forge->processIndexes('unidade_identificadores');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c83b67cbcf8fe2776c49');
        $this->forge->processIndexes('unidade_identificadores');
    }

    /** Reverte a tabela unidade_identificadores e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('unidade_identificadores', 'fk_c83b67cbcf8fe2776c49');
        $this->forge->dropForeignKey('unidade_identificadores', 'fk_adbbd7673c4e6be7a5b7');
        $this->forge->dropTable('unidade_identificadores');
    }
}
