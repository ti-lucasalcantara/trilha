<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanoVersoes extends Migration
{
    /** Cria a tabela plano_versoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'plano_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'preco' => ['type' => 'DECIMAL', 'constraint' => '19,2', 'null' => false],
            'moeda' => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => false],
            'periodicidade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'limites' => ['type' => 'JSON', 'null' => false],
            'vigente_desde' => ['type' => 'DATETIME', 'null' => false],
            'vigente_ate' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('plano_versoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['plano_id'], 'planos', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2b744f50366297ad8b76');
        $this->forge->processIndexes('plano_versoes');
    }

    /** Reverte a tabela plano_versoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('plano_versoes', 'fk_2b744f50366297ad8b76');
        $this->forge->dropTable('plano_versoes');
    }
}
