<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoValoresOpcao extends Migration
{
    /** Cria a tabela processo_valores_opcao e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_valor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'opcao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_51828e3d62399d750129');
        $this->forge->addUniqueKey(['organizacao_id', 'processo_valor_id', 'opcao_id'], 'uq_36bdc96b6f6cd6639c7a');
        $this->forge->createTable('processo_valores_opcao', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_a83926e7550a6791586b');
        $this->forge->processIndexes('processo_valores_opcao');
        $this->forge->addForeignKey(['organizacao_id', 'processo_valor_id'], 'processo_valores_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ad29cac4cc7b305e4fe9');
        $this->forge->processIndexes('processo_valores_opcao');
        $this->forge->addForeignKey(['organizacao_id', 'opcao_id'], 'opcoes_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_15df9abe4dbf5da810f4');
        $this->forge->processIndexes('processo_valores_opcao');
    }

    /** Reverte a tabela processo_valores_opcao e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_valores_opcao', 'fk_15df9abe4dbf5da810f4');
        $this->forge->dropForeignKey('processo_valores_opcao', 'fk_ad29cac4cc7b305e4fe9');
        $this->forge->dropForeignKey('processo_valores_opcao', 'fk_a83926e7550a6791586b');
        $this->forge->dropTable('processo_valores_opcao');
    }
}
