<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAutorizacaoDocumentos extends Migration
{
    /** Cria a tabela autorizacao_documentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'autorizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'autorizacao_processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'origem_concessao' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'permite_download' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_40c9df0938f7cedcb9d5');
        $this->forge->addUniqueKey(['organizacao_id', 'autorizacao_id', 'versao_id'], 'uq_c7523d2fd74fb2871fcc');
        $this->forge->createTable('autorizacao_documentos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_291cdfe07b0b86c62587');
        $this->forge->processIndexes('autorizacao_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'autorizacao_id'], 'autorizacoes_externas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5e6c8fdff5f39374457b');
        $this->forge->processIndexes('autorizacao_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_fb00c15d87fc8c1def80');
        $this->forge->processIndexes('autorizacao_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ca7f989352b0ca1db819');
        $this->forge->processIndexes('autorizacao_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'autorizacao_processo_id'], 'autorizacao_processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_47b9b7e29d21aee3f68e');
        $this->forge->processIndexes('autorizacao_documentos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id', 'versao_id'], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_061d1c704f9f10814887');
        $this->forge->processIndexes('autorizacao_documentos');
    }

    /** Reverte a tabela autorizacao_documentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_061d1c704f9f10814887');
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_47b9b7e29d21aee3f68e');
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_ca7f989352b0ca1db819');
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_fb00c15d87fc8c1def80');
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_5e6c8fdff5f39374457b');
        $this->forge->dropForeignKey('autorizacao_documentos', 'fk_291cdfe07b0b86c62587');
        $this->forge->dropTable('autorizacao_documentos');
    }
}
