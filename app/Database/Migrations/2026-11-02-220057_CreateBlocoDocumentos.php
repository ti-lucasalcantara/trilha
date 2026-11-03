<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlocoDocumentos extends Migration
{
    /** Cria a tabela bloco_documentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'bloco_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ordem' => ['type' => 'BIGINT', 'null' => false],
            'incluido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_80c9a56966aa13a50f55');
        $this->forge->addUniqueKey(['organizacao_id', 'bloco_id', 'documento_versao_id'], 'uq_937b1a1d29c3058c7796');
        $this->forge->createTable('bloco_documentos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_20b4ca6b80f6d7bfa022');
        $this->forge->processIndexes('bloco_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'bloco_id'], 'blocos_assinatura', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5defbb37fc4ecb9ea54a');
        $this->forge->processIndexes('bloco_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_481f50501aca0028b192');
        $this->forge->processIndexes('bloco_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'incluido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7f59f3f62fb7d649b21b');
        $this->forge->processIndexes('bloco_documentos');
    }

    /** Reverte a tabela bloco_documentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('bloco_documentos', 'fk_7f59f3f62fb7d649b21b');
        $this->forge->dropForeignKey('bloco_documentos', 'fk_481f50501aca0028b192');
        $this->forge->dropForeignKey('bloco_documentos', 'fk_5defbb37fc4ecb9ea54a');
        $this->forge->dropForeignKey('bloco_documentos', 'fk_20b4ca6b80f6d7bfa022');
        $this->forge->dropTable('bloco_documentos');
    }
}
