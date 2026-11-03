<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIaConfiguracoes extends Migration
{
    /** Cria a tabela ia_configuracoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'habilitada' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'recursos_permitidos' => ['type' => 'JSON', 'null' => true],
            'politica_conteudo' => ['type' => 'JSON', 'null' => true],
            'limite_consumo' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_c198c98a9bb55e9c38d5');
        $this->forge->addUniqueKey(['organizacao_id'], 'uq_79de51076a4081cb370b');
        $this->forge->createTable('ia_configuracoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_79de51076a4081cb370b');
        $this->forge->processIndexes('ia_configuracoes');
    }

    /** Reverte a tabela ia_configuracoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('ia_configuracoes', 'fk_79de51076a4081cb370b');
        $this->forge->dropTable('ia_configuracoes');
    }
}
