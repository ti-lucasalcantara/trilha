<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOrganizacaoConfiguracoes extends Migration
{
    /** Cria a tabela organizacao_configuracoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'logo_arquivo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'preferencias' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_375d6ce7d17dc1a3955f');
        $this->forge->addUniqueKey(['organizacao_id'], 'uq_422485e5524925ddd52b');
        $this->forge->createTable('organizacao_configuracoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_422485e5524925ddd52b');
        $this->forge->processIndexes('organizacao_configuracoes');
        $this->forge->addForeignKey(['organizacao_id', 'logo_arquivo_id'], 'arquivos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6ea6b410c50d52efdeac');
        $this->forge->processIndexes('organizacao_configuracoes');
    }

    /** Reverte a tabela organizacao_configuracoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('organizacao_configuracoes', 'fk_6ea6b410c50d52efdeac');
        $this->forge->dropForeignKey('organizacao_configuracoes', 'fk_422485e5524925ddd52b');
        $this->forge->dropTable('organizacao_configuracoes');
    }
}
