<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatusDocumento extends Migration
{
    /** Cria a tabela status_documento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'cor' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'ordem' => ['type' => 'BIGINT', 'null' => false],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_18f4fbf9e87cfd188a58');
        $this->forge->addUniqueKey(['organizacao_id', 'nome'], 'uq_e4e9fefb49953513f224');
        $this->forge->createTable('status_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_040615594adfa6934271');
        $this->forge->processIndexes('status_documento');
    }

    /** Reverte a tabela status_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('status_documento', 'fk_040615594adfa6934271');
        $this->forge->dropTable('status_documento');
    }
}
