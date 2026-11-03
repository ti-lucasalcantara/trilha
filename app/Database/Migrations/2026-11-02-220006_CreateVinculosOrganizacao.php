<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateVinculosOrganizacao extends Migration
{
    /** Cria a tabela vinculos_organizacao e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'usuario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_vinculo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'fim_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_07826a4295d5c60294e2');
        $this->forge->addUniqueKey(['organizacao_id', 'usuario_id'], 'uq_9d997b6fafa74cb50f36');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_af377ae50190e6b2558a');
        $this->forge->createTable('vinculos_organizacao', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_4a23d786444127fea78b');
        $this->forge->processIndexes('vinculos_organizacao');
        $this->forge->addForeignKey(['usuario_id'], 'usuarios', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8bafca9d26461be5fca3');
        $this->forge->processIndexes('vinculos_organizacao');
    }

    /** Reverte a tabela vinculos_organizacao e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('vinculos_organizacao', 'fk_8bafca9d26461be5fca3');
        $this->forge->dropForeignKey('vinculos_organizacao', 'fk_4a23d786444127fea78b');
        $this->forge->dropTable('vinculos_organizacao');
    }
}
