<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConfirmacoesAssinatura extends Migration
{
    /** Cria a tabela confirmacoes_assinatura e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'solicitacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'metodo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'desafio_hash' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'expira_em' => ['type' => 'DATETIME', 'null' => true],
            'confirmado_em' => ['type' => 'DATETIME', 'null' => true],
            'tentativas' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_d0aa2283de0da9bdfc1c');
        $this->forge->addKey(['organizacao_id', 'expira_em'], false, false, 'ix_2c2d89904448f3908a60');
        $this->forge->createTable('confirmacoes_assinatura', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_32838a706c7b2ec053e7');
        $this->forge->processIndexes('confirmacoes_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'solicitacao_id'], 'solicitacoes_assinatura', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_403c95343c332d056f0d');
        $this->forge->processIndexes('confirmacoes_assinatura');
    }

    /** Reverte a tabela confirmacoes_assinatura e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('confirmacoes_assinatura', 'fk_403c95343c332d056f0d');
        $this->forge->dropForeignKey('confirmacoes_assinatura', 'fk_32838a706c7b2ec053e7');
        $this->forge->dropTable('confirmacoes_assinatura');
    }
}
