<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoValoresCampo extends Migration
{
    /** Cria a tabela documento_valores_campo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'campo_configuracao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'valor_texto' => ['type' => 'TEXT', 'null' => true],
            'valor_numero' => ['type' => 'DECIMAL', 'constraint' => '24,6', 'null' => true],
            'valor_data' => ['type' => 'DATETIME', 'null' => true],
            'valor_booleano' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_f6edd0cd4c0d85f0d2a5');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_id', 'campo_configuracao_id'], 'uq_8eeb145e892aacd555ad');
        $this->forge->createTable('documento_valores_campo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_6c5637c216c620fef0bd');
        $this->forge->processIndexes('documento_valores_campo');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_2c09866cff21e748e3a6');
        $this->forge->processIndexes('documento_valores_campo');
        $this->forge->addForeignKey(['organizacao_id', 'campo_configuracao_id'], 'tipo_documento_campos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d5f4b59047a1bba764ac');
        $this->forge->processIndexes('documento_valores_campo');
    }

    /** Reverte a tabela documento_valores_campo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documento_valores_campo', 'fk_d5f4b59047a1bba764ac');
        $this->forge->dropForeignKey('documento_valores_campo', 'fk_2c09866cff21e748e3a6');
        $this->forge->dropForeignKey('documento_valores_campo', 'fk_6c5637c216c620fef0bd');
        $this->forge->dropTable('documento_valores_campo');
    }
}
