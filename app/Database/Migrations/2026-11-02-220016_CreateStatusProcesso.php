<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateStatusProcesso extends Migration
{
    /** Cria a tabela status_processo e suas restrições; nome exigido pelo CI4. */
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
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_6673d26421b218a1356b');
        $this->forge->addUniqueKey(['organizacao_id', 'nome'], 'uq_682e4f06f5695d4ea608');
        $this->forge->createTable('status_processo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_aa7667b11ca800fc0037');
        $this->forge->processIndexes('status_processo');
    }

    /** Reverte a tabela status_processo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('status_processo', 'fk_aa7667b11ca800fc0037');
        $this->forge->dropTable('status_processo');
    }
}
