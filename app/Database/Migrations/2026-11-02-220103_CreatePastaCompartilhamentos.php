<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePastaCompartilhamentos extends Migration
{
    /** Cria a tabela pasta_compartilhamentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'pasta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'permite_gerenciar' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_387d1313e9b99f050bcb');
        $this->forge->createTable('pasta_compartilhamentos', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('pasta_compartilhamentos') . '` ADD CONSTRAINT `ck_8dd24995b8b213337cf8` CHECK ((vinculo_id IS NOT NULL) + (setor_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_3d0f1d4f79e5f66fec9a');
        $this->forge->processIndexes('pasta_compartilhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'pasta_id'], 'pastas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0d783108650c0a7c3170');
        $this->forge->processIndexes('pasta_compartilhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3fb712ba41d502cc58e6');
        $this->forge->processIndexes('pasta_compartilhamentos');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_dc9ae85f72948a43dd74');
        $this->forge->processIndexes('pasta_compartilhamentos');
    }

    /** Reverte a tabela pasta_compartilhamentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('pasta_compartilhamentos', 'fk_dc9ae85f72948a43dd74');
        $this->forge->dropForeignKey('pasta_compartilhamentos', 'fk_3fb712ba41d502cc58e6');
        $this->forge->dropForeignKey('pasta_compartilhamentos', 'fk_0d783108650c0a7c3170');
        $this->forge->dropForeignKey('pasta_compartilhamentos', 'fk_3d0f1d4f79e5f66fec9a');
        $this->forge->dropTable('pasta_compartilhamentos');
    }
}
