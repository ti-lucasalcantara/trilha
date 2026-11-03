<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoValoresOpcao extends Migration
{
    /** Cria a tabela documento_valores_opcao e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_valor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'opcao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_fdd2932401af4eaa5ac7');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_valor_id', 'opcao_id'], 'uq_14ce362c02136abde14e');
        $this->forge->createTable('documento_valores_opcao', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_46e80fbca1f6b6a8a3f9');
        $this->forge->processIndexes('documento_valores_opcao');
        $this->forge->addForeignKey(['organizacao_id', 'documento_valor_id'], 'documento_valores_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ce0ecf9c66c265867312');
        $this->forge->processIndexes('documento_valores_opcao');
        $this->forge->addForeignKey(['organizacao_id', 'opcao_id'], 'opcoes_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0687fd168b967fc6a71b');
        $this->forge->processIndexes('documento_valores_opcao');
    }

    /** Reverte a tabela documento_valores_opcao e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documento_valores_opcao', 'fk_0687fd168b967fc6a71b');
        $this->forge->dropForeignKey('documento_valores_opcao', 'fk_ce0ecf9c66c265867312');
        $this->forge->dropForeignKey('documento_valores_opcao', 'fk_46e80fbca1f6b6a8a3f9');
        $this->forge->dropTable('documento_valores_opcao');
    }
}
