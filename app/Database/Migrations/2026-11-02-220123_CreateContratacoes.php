<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContratacoes extends Migration
{
    /** Cria a tabela contratacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'plano_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'renovacao_em' => ['type' => 'DATETIME', 'null' => true],
            'encerramento_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a14bfa34b3e986d1ed77');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_a2e6233052cb16346c96');
        $this->forge->createTable('contratacoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_f82dc2e370037e1c8a46');
        $this->forge->processIndexes('contratacoes');
        $this->forge->addForeignKey(['plano_versao_id'], 'plano_versoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_a0c8a6b75c09ec29c03a');
        $this->forge->processIndexes('contratacoes');
    }

    /** Reverte a tabela contratacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('contratacoes', 'fk_a0c8a6b75c09ec29c03a');
        $this->forge->dropForeignKey('contratacoes', 'fk_f82dc2e370037e1c8a46');
        $this->forge->dropTable('contratacoes');
    }
}
