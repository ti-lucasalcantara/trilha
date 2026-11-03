<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAutorizacaoProcessos extends Migration
{
    /** Cria a tabela autorizacao_processos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'autorizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'incluir_documentos_futuros' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'permite_download' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_78480fc12798db04b809');
        $this->forge->addUniqueKey(['organizacao_id', 'autorizacao_id', 'processo_id'], 'uq_fb97563582c56d73cf33');
        $this->forge->createTable('autorizacao_processos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_decf9fca1ad3b7c382e5');
        $this->forge->processIndexes('autorizacao_processos');
        $this->forge->addForeignKey(['organizacao_id', 'autorizacao_id'], 'autorizacoes_externas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_65d8474c9ae28091cd31');
        $this->forge->processIndexes('autorizacao_processos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5e0ff2f3010c8a99060b');
        $this->forge->processIndexes('autorizacao_processos');
    }

    /** Reverte a tabela autorizacao_processos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('autorizacao_processos', 'fk_5e0ff2f3010c8a99060b');
        $this->forge->dropForeignKey('autorizacao_processos', 'fk_65d8474c9ae28091cd31');
        $this->forge->dropForeignKey('autorizacao_processos', 'fk_decf9fca1ad3b7c382e5');
        $this->forge->dropTable('autorizacao_processos');
    }
}
