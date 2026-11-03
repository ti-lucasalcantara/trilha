<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateNotificacaoEntregas extends Migration
{
    /** Cria a tabela notificacao_entregas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'notificacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'canal' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'tentativas' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'enviada_em' => ['type' => 'DATETIME', 'null' => true],
            'erro' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_4c33da52a5b23bae0584');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_5e067a1e84522da8c09c');
        $this->forge->createTable('notificacao_entregas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_d9864f6624962c4cf74d');
        $this->forge->processIndexes('notificacao_entregas');
        $this->forge->addForeignKey(['organizacao_id', 'notificacao_id'], 'notificacoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6b71d2d5014732d0aed8');
        $this->forge->processIndexes('notificacao_entregas');
    }

    /** Reverte a tabela notificacao_entregas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('notificacao_entregas', 'fk_6b71d2d5014732d0aed8');
        $this->forge->dropForeignKey('notificacao_entregas', 'fk_d9864f6624962c4cf74d');
        $this->forge->dropTable('notificacao_entregas');
    }
}
