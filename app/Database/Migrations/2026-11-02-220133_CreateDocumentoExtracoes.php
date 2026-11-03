<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoExtracoes extends Migration
{
    /** Cria a tabela documento_extracoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'texto_extraido' => ['type' => 'LONGTEXT', 'null' => true],
            'metodo' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_2291bcc997d98f143510');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_8a15012d90061882a99e');
        $this->forge->createTable('documento_extracoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_365b3cea34b39c464cd8');
        $this->forge->processIndexes('documento_extracoes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ae4a3d34cfa2ace850d2');
        $this->forge->processIndexes('documento_extracoes');
    }

    /** Reverte a tabela documento_extracoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documento_extracoes', 'fk_ae4a3d34cfa2ace850d2');
        $this->forge->dropForeignKey('documento_extracoes', 'fk_365b3cea34b39c464cd8');
        $this->forge->dropTable('documento_extracoes');
    }
}
