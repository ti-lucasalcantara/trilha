<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAdicionaisContratados extends Migration
{
    /** Cria a tabela adicionais_contratados e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'contratacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'quantidade' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'preco' => ['type' => 'DECIMAL', 'constraint' => '19,2', 'null' => false],
            'moeda' => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => false],
            'vigente_desde' => ['type' => 'DATETIME', 'null' => false],
            'vigente_ate' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a3b627582ec3a819bd54');
        $this->forge->createTable('adicionais_contratados', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_518bf2826b58ebe969f2');
        $this->forge->processIndexes('adicionais_contratados');
        $this->forge->addForeignKey(['organizacao_id', 'contratacao_id'], 'contratacoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_904110d3dedc99350014');
        $this->forge->processIndexes('adicionais_contratados');
    }

    /** Reverte a tabela adicionais_contratados e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('adicionais_contratados', 'fk_904110d3dedc99350014');
        $this->forge->dropForeignKey('adicionais_contratados', 'fk_518bf2826b58ebe969f2');
        $this->forge->dropTable('adicionais_contratados');
    }
}
