<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDefinicoesCampo extends Migration
{
    /** Cria a tabela definicoes_campo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'chave' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'tipo_dado' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_ca25d96d7f09ed46b676');
        $this->forge->addUniqueKey(['organizacao_id', 'chave'], 'uq_d4182b5ed7a20b5e23aa');
        $this->forge->createTable('definicoes_campo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_1f64df20e1a99c815cb0');
        $this->forge->processIndexes('definicoes_campo');
    }

    /** Reverte a tabela definicoes_campo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('definicoes_campo', 'fk_1f64df20e1a99c815cb0');
        $this->forge->dropTable('definicoes_campo');
    }
}
