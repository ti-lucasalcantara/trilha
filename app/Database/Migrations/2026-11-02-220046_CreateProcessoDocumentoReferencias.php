<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoDocumentoReferencias extends Migration
{
    /** Cria a tabela processo_documento_referencias e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao_referenciada_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_f6e09a5109c1b30e90d7');
        $this->forge->createTable('processo_documento_referencias', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_ba505733c08278419bb8');
        $this->forge->processIndexes('processo_documento_referencias');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_8215f9a03d6ad89a7d51');
        $this->forge->processIndexes('processo_documento_referencias');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6473a71248d27fc6cb92');
        $this->forge->processIndexes('processo_documento_referencias');
        $this->forge->addForeignKey(['organizacao_id', 'versao_referenciada_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ee58cbdd8f7ce82337bf');
        $this->forge->processIndexes('processo_documento_referencias');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b676d6483a2f1c5b5ab1');
        $this->forge->processIndexes('processo_documento_referencias');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id', 'versao_referenciada_id'], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_987770d95df3213a659e');
        $this->forge->processIndexes('processo_documento_referencias');
    }

    /** Reverte a tabela processo_documento_referencias e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_987770d95df3213a659e');
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_b676d6483a2f1c5b5ab1');
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_ee58cbdd8f7ce82337bf');
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_6473a71248d27fc6cb92');
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_8215f9a03d6ad89a7d51');
        $this->forge->dropForeignKey('processo_documento_referencias', 'fk_ba505733c08278419bb8');
        $this->forge->dropTable('processo_documento_referencias');
    }
}
