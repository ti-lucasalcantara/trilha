<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificacoes extends Migration
{
    /** Cria a tabela notificacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'destinatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'referencia' => ['type' => 'JSON', 'null' => true],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'lida_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_4aa1a384cfb40a253490');
        $this->forge->createTable('notificacoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_5278eef1a8f727132d4b');
        $this->forge->processIndexes('notificacoes');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_46906f61891ae172167e');
        $this->forge->processIndexes('notificacoes');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_416d8ea02f9fd19d3e84');
        $this->forge->processIndexes('notificacoes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d065180c188edd6b5a38');
        $this->forge->processIndexes('notificacoes');
    }

    /** Reverte a tabela notificacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('notificacoes', 'fk_d065180c188edd6b5a38');
        $this->forge->dropForeignKey('notificacoes', 'fk_416d8ea02f9fd19d3e84');
        $this->forge->dropForeignKey('notificacoes', 'fk_46906f61891ae172167e');
        $this->forge->dropForeignKey('notificacoes', 'fk_5278eef1a8f727132d4b');
        $this->forge->dropTable('notificacoes');
    }
}
