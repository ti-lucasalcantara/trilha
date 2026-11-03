<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTramitacaoDestinatarios extends Migration
{
    /** Cria a tabela tramitacao_destinatarios e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tramitacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'destinatario_usuario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'modalidade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'prazo_em' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'recebido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'recebido_em' => ['type' => 'DATETIME', 'null' => true],
            'concluido_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_c22a442233d24b61ea9a');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_5469a75c3ef89a87eec1');
        $this->forge->createTable('tramitacao_destinatarios', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_40fe4b6e5ad2b298d9b3');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'tramitacao_id'], 'tramitacoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a5c6c0efa4da3e6a5a41');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a99f1f10b29c5a9b30cd');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5fb9182a4603f5443710');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_usuario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e6635b3034bc53391187');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'recebido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a0b4a15a6411b33f58da');
        $this->forge->processIndexes('tramitacao_destinatarios');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id', 'setor_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_aa5b20d7aefca4cffa50');
        $this->forge->processIndexes('tramitacao_destinatarios');
    }

    /** Reverte a tabela tramitacao_destinatarios e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_aa5b20d7aefca4cffa50');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_a0b4a15a6411b33f58da');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_e6635b3034bc53391187');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_5fb9182a4603f5443710');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_a99f1f10b29c5a9b30cd');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_a5c6c0efa4da3e6a5a41');
        $this->forge->dropForeignKey('tramitacao_destinatarios', 'fk_40fe4b6e5ad2b298d9b3');
        $this->forge->dropTable('tramitacao_destinatarios');
    }
}
