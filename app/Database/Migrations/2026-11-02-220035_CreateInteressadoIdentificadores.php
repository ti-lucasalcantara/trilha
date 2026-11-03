<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateInteressadoIdentificadores extends Migration
{
    /** Cria a tabela interessado_identificadores e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'interessado_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'valor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'pais' => ['type' => 'VARCHAR', 'constraint' => 2, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_c3929420990c9373155b');
        $this->forge->createTable('interessado_identificadores', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_3e80be02bc02517a4ba3');
        $this->forge->processIndexes('interessado_identificadores');
        $this->forge->addForeignKey(['organizacao_id', 'interessado_id'], 'interessados', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ec7abdba191a8db6a0e3');
        $this->forge->processIndexes('interessado_identificadores');
    }

    /** Reverte a tabela interessado_identificadores e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('interessado_identificadores', 'fk_ec7abdba191a8db6a0e3');
        $this->forge->dropForeignKey('interessado_identificadores', 'fk_3e80be02bc02517a4ba3');
        $this->forge->dropTable('interessado_identificadores');
    }
}
