<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAcessosExternos extends Migration
{
    /** Cria a tabela acessos_externos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'autorizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'acao' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_27a33283b56268fec2e5');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_56d1fa2b93dacf8f1195');
        $this->forge->createTable('acessos_externos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_693b65b22654b59b2cda');
        $this->forge->processIndexes('acessos_externos');
        $this->forge->addForeignKey(['organizacao_id', 'autorizacao_id'], 'autorizacoes_externas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_cf0f7e91272836eb7af6');
        $this->forge->processIndexes('acessos_externos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_bd04e4b6cfcb32b91089');
        $this->forge->processIndexes('acessos_externos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_24df0908788364e00f14');
        $this->forge->processIndexes('acessos_externos');
        $this->forge->addForeignKey(['organizacao_id', 'versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0ef716e83260bfe22bf4');
        $this->forge->processIndexes('acessos_externos');
    }

    /** Reverte a tabela acessos_externos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('acessos_externos', 'fk_0ef716e83260bfe22bf4');
        $this->forge->dropForeignKey('acessos_externos', 'fk_24df0908788364e00f14');
        $this->forge->dropForeignKey('acessos_externos', 'fk_bd04e4b6cfcb32b91089');
        $this->forge->dropForeignKey('acessos_externos', 'fk_cf0f7e91272836eb7af6');
        $this->forge->dropForeignKey('acessos_externos', 'fk_693b65b22654b59b2cda');
        $this->forge->dropTable('acessos_externos');
    }
}
