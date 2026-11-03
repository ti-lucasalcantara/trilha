<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcompanhamentos extends Migration
{
    /** Cria a tabela acompanhamentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'preferencias' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_aff1abe9cdcf76494566');
        $this->forge->createTable('acompanhamentos', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('acompanhamentos') . '` ADD CONSTRAINT `ck_397f8cfff11cdb2002ba` CHECK ((processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_50026787d028ee8d6e68');
        $this->forge->processIndexes('acompanhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ef6c0974a48c55426aa3');
        $this->forge->processIndexes('acompanhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6fb882f7320271fafee3');
        $this->forge->processIndexes('acompanhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_dd79ca3dc92836f92827');
        $this->forge->processIndexes('acompanhamentos');
    }

    /** Reverte a tabela acompanhamentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('acompanhamentos', 'fk_dd79ca3dc92836f92827');
        $this->forge->dropForeignKey('acompanhamentos', 'fk_6fb882f7320271fafee3');
        $this->forge->dropForeignKey('acompanhamentos', 'fk_ef6c0974a48c55426aa3');
        $this->forge->dropForeignKey('acompanhamentos', 'fk_50026787d028ee8d6e68');
        $this->forge->dropTable('acompanhamentos');
    }
}
