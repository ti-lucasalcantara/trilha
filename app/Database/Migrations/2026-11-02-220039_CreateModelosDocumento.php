<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModelosDocumento extends Migration
{
    /** Cria a tabela modelos_documento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_65aea987d4e2715f538e');
        $this->forge->createTable('modelos_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0cfcacd516e6eddbc6ad');
        $this->forge->processIndexes('modelos_documento');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_32a30078eeca54c8f9de');
        $this->forge->processIndexes('modelos_documento');
    }

    /** Reverte a tabela modelos_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('modelos_documento', 'fk_32a30078eeca54c8f9de');
        $this->forge->dropForeignKey('modelos_documento', 'fk_0cfcacd516e6eddbc6ad');
        $this->forge->dropTable('modelos_documento');
    }
}
