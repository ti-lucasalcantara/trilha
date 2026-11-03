<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoDocumentoVersoes extends Migration
{
    /** Cria a tabela tipo_documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'instrucoes' => ['type' => 'TEXT', 'null' => true],
            'configuracao' => ['type' => 'JSON', 'null' => true],
            'publicado_em' => ['type' => 'DATETIME', 'null' => true],
            'publicado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_e0ccf6dd9da15d01135c');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'versao'], 'uq_7f17a800e3b57e0f1d6c');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'id'], 'uq_8095b1248d27155cd23a');
        $this->forge->createTable('tipo_documento_versoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8ea86fd9ddc50e8678bd');
        $this->forge->processIndexes('tipo_documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ce039483979fa93d0bb3');
        $this->forge->processIndexes('tipo_documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'publicado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f049e94687a7ecdf9dc1');
        $this->forge->processIndexes('tipo_documento_versoes');
    }

    /** Reverte a tabela tipo_documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipo_documento_versoes', 'fk_f049e94687a7ecdf9dc1');
        $this->forge->dropForeignKey('tipo_documento_versoes', 'fk_ce039483979fa93d0bb3');
        $this->forge->dropForeignKey('tipo_documento_versoes', 'fk_8ea86fd9ddc50e8678bd');
        $this->forge->dropTable('tipo_documento_versoes');
    }
}
