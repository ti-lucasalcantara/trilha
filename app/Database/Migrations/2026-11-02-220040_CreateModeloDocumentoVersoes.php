<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateModeloDocumentoVersoes extends Migration
{
    /** Cria a tabela modelo_documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'modelo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'conteudo' => ['type' => 'LONGTEXT', 'null' => false],
            'variaveis' => ['type' => 'JSON', 'null' => true],
            'publicado_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_081a22a4e1815db21048');
        $this->forge->addUniqueKey(['organizacao_id', 'modelo_id', 'versao'], 'uq_6ad6d2f4c3fd852c5264');
        $this->forge->createTable('modelo_documento_versoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_bafac053fbd048f9b6c7');
        $this->forge->processIndexes('modelo_documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'modelo_id'], 'modelos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0bc2d0267dc766337994');
        $this->forge->processIndexes('modelo_documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_69e957cc87d88ab14685');
        $this->forge->processIndexes('modelo_documento_versoes');
    }

    /** Reverte a tabela modelo_documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('modelo_documento_versoes', 'fk_69e957cc87d88ab14685');
        $this->forge->dropForeignKey('modelo_documento_versoes', 'fk_0bc2d0267dc766337994');
        $this->forge->dropForeignKey('modelo_documento_versoes', 'fk_bafac053fbd048f9b6c7');
        $this->forge->dropTable('modelo_documento_versoes');
    }
}
