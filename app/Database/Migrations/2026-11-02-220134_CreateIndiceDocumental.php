<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIndiceDocumental extends Migration
{
    /** Cria a tabela indice_documental e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'referencia_indice' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'versao_indexacao' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_933f1bffcef930c0b481');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_c39002ee4c82570c5562');
        $this->forge->createTable('indice_documental', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_e4f70129d4a646777099');
        $this->forge->processIndexes('indice_documental');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c71567283e21cee9c2e0');
        $this->forge->processIndexes('indice_documental');
    }

    /** Reverte a tabela indice_documental e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('indice_documental', 'fk_c71567283e21cee9c2e0');
        $this->forge->dropForeignKey('indice_documental', 'fk_e4f70129d4a646777099');
        $this->forge->dropTable('indice_documental');
    }
}
