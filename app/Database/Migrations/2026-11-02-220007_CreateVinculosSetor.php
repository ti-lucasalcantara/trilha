<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVinculosSetor extends Migration
{
    /** Cria a tabela vinculos_setor e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'funcao' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'responsavel_setor' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'fim_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_24b553b02200adbbe4cc');
        $this->forge->createTable('vinculos_setor', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_5d915fc5a55ffb3b9fae');
        $this->forge->processIndexes('vinculos_setor');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_8a83cd149f6770dc7036');
        $this->forge->processIndexes('vinculos_setor');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_9a10ded532c798ecaa6d');
        $this->forge->processIndexes('vinculos_setor');
    }

    /** Reverte a tabela vinculos_setor e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('vinculos_setor', 'fk_9a10ded532c798ecaa6d');
        $this->forge->dropForeignKey('vinculos_setor', 'fk_8a83cd149f6770dc7036');
        $this->forge->dropForeignKey('vinculos_setor', 'fk_5d915fc5a55ffb3b9fae');
        $this->forge->dropTable('vinculos_setor');
    }
}
