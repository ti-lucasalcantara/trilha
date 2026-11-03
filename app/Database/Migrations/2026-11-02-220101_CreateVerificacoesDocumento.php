<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVerificacoesDocumento extends Migration
{
    /** Cria a tabela verificacoes_documento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'codigo_verificacao' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'politica_exibicao' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_19f6dd1cfb8c803b0b61');
        $this->forge->addUniqueKey(['codigo_verificacao'], 'uq_92490da1da4dad354d2a');
        $this->forge->createTable('verificacoes_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_b141e1e8ebe55c2b32e6');
        $this->forge->processIndexes('verificacoes_documento');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_848a85ea10f54b37bf1d');
        $this->forge->processIndexes('verificacoes_documento');
    }

    /** Reverte a tabela verificacoes_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('verificacoes_documento', 'fk_848a85ea10f54b37bf1d');
        $this->forge->dropForeignKey('verificacoes_documento', 'fk_b141e1e8ebe55c2b32e6');
        $this->forge->dropTable('verificacoes_documento');
    }
}
