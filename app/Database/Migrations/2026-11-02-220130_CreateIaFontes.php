<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIaFontes extends Migration
{
    /** Cria a tabela ia_fontes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'execucao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'evento_historico_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'trecho_referencia' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_fdef48e3efc0b563dd86');
        $this->forge->createTable('ia_fontes', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('ia_fontes') . '` ADD CONSTRAINT `ck_e7f2ed26728ee36a205c` CHECK ((documento_versao_id IS NOT NULL) + (evento_historico_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_6759e48f02598501e0c9');
        $this->forge->processIndexes('ia_fontes');
        $this->forge->addForeignKey(['organizacao_id', 'execucao_id'], 'ia_execucoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d81f93bebf11b5cd2ee1');
        $this->forge->processIndexes('ia_fontes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f6867469e003f8fd3fff');
        $this->forge->processIndexes('ia_fontes');
        $this->forge->addForeignKey(['organizacao_id', 'evento_historico_id'], 'historico_eventos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3869d4ea5eeecdb17693');
        $this->forge->processIndexes('ia_fontes');
    }

    /** Reverte a tabela ia_fontes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('ia_fontes', 'fk_3869d4ea5eeecdb17693');
        $this->forge->dropForeignKey('ia_fontes', 'fk_f6867469e003f8fd3fff');
        $this->forge->dropForeignKey('ia_fontes', 'fk_d81f93bebf11b5cd2ee1');
        $this->forge->dropForeignKey('ia_fontes', 'fk_6759e48f02598501e0c9');
        $this->forge->dropTable('ia_fontes');
    }
}
