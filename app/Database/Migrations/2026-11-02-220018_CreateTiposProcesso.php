<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiposProcesso extends Migration
{
    /** Cria a tabela tipos_processo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'sigla' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_62038b309e048f287c8a');
        $this->forge->addUniqueKey(['organizacao_id', 'nome'], 'uq_c426997cd1682bdbdb83');
        $this->forge->createTable('tipos_processo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_d7db016161109e77e9cb');
        $this->forge->processIndexes('tipos_processo');
    }

    /** Reverte a tabela tipos_processo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipos_processo', 'fk_d7db016161109e77e9cb');
        $this->forge->dropTable('tipos_processo');
    }
}
