<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTiposDocumento extends Migration
{
    /** Cria a tabela tipos_documento e suas restrições; nome exigido pelo CI4. */
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
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_e2e5de6ffb066dca2177');
        $this->forge->addUniqueKey(['organizacao_id', 'nome'], 'uq_40255d788d5b237332b1');
        $this->forge->createTable('tipos_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_305e4c23d1ecaa7c8ac5');
        $this->forge->processIndexes('tipos_documento');
    }

    /** Reverte a tabela tipos_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipos_documento', 'fk_305e4c23d1ecaa7c8ac5');
        $this->forge->dropTable('tipos_documento');
    }
}
