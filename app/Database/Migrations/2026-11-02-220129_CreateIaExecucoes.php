<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIaExecucoes extends Migration
{
    /** Cria a tabela ia_execucoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'solicitante_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'recurso' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'provedor' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'modelo' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'versao_instrucao' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'iniciada_em' => ['type' => 'DATETIME', 'null' => false],
            'concluida_em' => ['type' => 'DATETIME', 'null' => true],
            'erro' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a34f8b5e52af0a012da1');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_814093fcc1057bd9c192');
        $this->forge->createTable('ia_execucoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_4b2f65b5da3eb674baae');
        $this->forge->processIndexes('ia_execucoes');
        $this->forge->addForeignKey(['organizacao_id', 'solicitante_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_879aa5f9be9e2cc952f1');
        $this->forge->processIndexes('ia_execucoes');
    }

    /** Reverte a tabela ia_execucoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('ia_execucoes', 'fk_879aa5f9be9e2cc952f1');
        $this->forge->dropForeignKey('ia_execucoes', 'fk_4b2f65b5da3eb674baae');
        $this->forge->dropTable('ia_execucoes');
    }
}
