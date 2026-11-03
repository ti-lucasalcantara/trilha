<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentosExigidos extends Migration
{
    /** Cria a tabela documentos_exigidos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'quantidade_minima' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'etapa_validacao' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_948e46f568d40eaa33fc');
        $this->forge->createTable('documentos_exigidos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_cb7c9c88640ab45160b4');
        $this->forge->processIndexes('documentos_exigidos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_versao_id'], 'tipo_processo_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_10d0074066158c13bcf2');
        $this->forge->processIndexes('documentos_exigidos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1defc7e51d01e0acbe2d');
        $this->forge->processIndexes('documentos_exigidos');
    }

    /** Reverte a tabela documentos_exigidos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documentos_exigidos', 'fk_1defc7e51d01e0acbe2d');
        $this->forge->dropForeignKey('documentos_exigidos', 'fk_10d0074066158c13bcf2');
        $this->forge->dropForeignKey('documentos_exigidos', 'fk_cb7c9c88640ab45160b4');
        $this->forge->dropTable('documentos_exigidos');
    }
}
