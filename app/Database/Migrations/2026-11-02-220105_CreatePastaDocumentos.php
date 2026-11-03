<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePastaDocumentos extends Migration
{
    /** Cria a tabela pasta_documentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'pasta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'incluido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_ff8dcc8d4e5b1f2ed7f4');
        $this->forge->addUniqueKey(['organizacao_id', 'pasta_id', 'documento_id'], 'uq_eee0f875d361401cd521');
        $this->forge->createTable('pasta_documentos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0e1c44280d8bef2692c1');
        $this->forge->processIndexes('pasta_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'pasta_id'], 'pastas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_950b47ec81d8f984e768');
        $this->forge->processIndexes('pasta_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ebf8d9d7bda5612350ee');
        $this->forge->processIndexes('pasta_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'incluido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a8ef13ba88480ac9c93b');
        $this->forge->processIndexes('pasta_documentos');
    }

    /** Reverte a tabela pasta_documentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('pasta_documentos', 'fk_a8ef13ba88480ac9c93b');
        $this->forge->dropForeignKey('pasta_documentos', 'fk_ebf8d9d7bda5612350ee');
        $this->forge->dropForeignKey('pasta_documentos', 'fk_950b47ec81d8f984e768');
        $this->forge->dropForeignKey('pasta_documentos', 'fk_0e1c44280d8bef2692c1');
        $this->forge->dropTable('pasta_documentos');
    }
}
