<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoProcessoCampos extends Migration
{
    /** Cria a tabela tipo_processo_campos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'campo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'obrigatorio' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'ordem' => ['type' => 'BIGINT', 'null' => false],
            'validacao' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9d4946d604d9d640dcf9');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_versao_id', 'campo_id'], 'uq_f43781ecbc1a6ce01671');
        $this->forge->createTable('tipo_processo_campos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_6b126215a29a4684f399');
        $this->forge->processIndexes('tipo_processo_campos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_versao_id'], 'tipo_processo_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d3aeab4c1b3d3a60bcc6');
        $this->forge->processIndexes('tipo_processo_campos');
        $this->forge->addForeignKey(['organizacao_id', 'campo_id'], 'definicoes_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c5c26d454c5d6221ef9d');
        $this->forge->processIndexes('tipo_processo_campos');
    }

    /** Reverte a tabela tipo_processo_campos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipo_processo_campos', 'fk_c5c26d454c5d6221ef9d');
        $this->forge->dropForeignKey('tipo_processo_campos', 'fk_d3aeab4c1b3d3a60bcc6');
        $this->forge->dropForeignKey('tipo_processo_campos', 'fk_6b126215a29a4684f399');
        $this->forge->dropTable('tipo_processo_campos');
    }
}
