<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoDocumentoCampos extends Migration
{
    /** Cria a tabela tipo_documento_campos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'campo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'obrigatorio' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'ordem' => ['type' => 'BIGINT', 'null' => false],
            'validacao' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_de53e23fd0996271d507');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_versao_id', 'campo_id'], 'uq_a4c3e98a7ddb424efa5a');
        $this->forge->createTable('tipo_documento_campos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_37cc7dd79a21c00531ee');
        $this->forge->processIndexes('tipo_documento_campos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_versao_id'], 'tipo_documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b4d30e1d01c94ceb0e44');
        $this->forge->processIndexes('tipo_documento_campos');
        $this->forge->addForeignKey(['organizacao_id', 'campo_id'], 'definicoes_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ce9b06f412c79cc9c5c5');
        $this->forge->processIndexes('tipo_documento_campos');
    }

    /** Reverte a tabela tipo_documento_campos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipo_documento_campos', 'fk_ce9b06f412c79cc9c5c5');
        $this->forge->dropForeignKey('tipo_documento_campos', 'fk_b4d30e1d01c94ceb0e44');
        $this->forge->dropForeignKey('tipo_documento_campos', 'fk_37cc7dd79a21c00531ee');
        $this->forge->dropTable('tipo_documento_campos');
    }
}
