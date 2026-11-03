<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePoliticasAcesso extends Migration
{
    /** Cria a tabela politicas_acesso e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'nivel' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'herda_processo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'alterada_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1d2fdde74b0b6f1aea13');
        $this->forge->addUniqueKey(['organizacao_id', 'processo_id'], 'uq_636b113fa2ac78372862');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_id'], 'uq_95f8d140f4461006a926');
        $this->forge->createTable('politicas_acesso', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('politicas_acesso') . '` ADD CONSTRAINT `ck_89de882886478f0fd5ee` CHECK ((processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2afdfb0ee0838b377e6c');
        $this->forge->processIndexes('politicas_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_eb183768afe117cf3c3b');
        $this->forge->processIndexes('politicas_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5233bbec4619d0811c06');
        $this->forge->processIndexes('politicas_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'alterada_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_80135c0f69bb7338f0b5');
        $this->forge->processIndexes('politicas_acesso');
    }

    /** Reverte a tabela politicas_acesso e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('politicas_acesso', 'fk_80135c0f69bb7338f0b5');
        $this->forge->dropForeignKey('politicas_acesso', 'fk_5233bbec4619d0811c06');
        $this->forge->dropForeignKey('politicas_acesso', 'fk_eb183768afe117cf3c3b');
        $this->forge->dropForeignKey('politicas_acesso', 'fk_2afdfb0ee0838b377e6c');
        $this->forge->dropTable('politicas_acesso');
    }
}
