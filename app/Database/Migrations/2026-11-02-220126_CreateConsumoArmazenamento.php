<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateConsumoArmazenamento extends Migration
{
    /** Cria a tabela consumo_armazenamento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'categoria' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'bytes_utilizados' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'apurado_em' => ['type' => 'DATETIME', 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_26af93983fbd773e54d8');
        $this->forge->addUniqueKey(['organizacao_id', 'categoria'], 'uq_086243a4df28fc57ade2');
        $this->forge->createTable('consumo_armazenamento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_19a7061837c7eb88cf53');
        $this->forge->processIndexes('consumo_armazenamento');
    }

    /** Reverte a tabela consumo_armazenamento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('consumo_armazenamento', 'fk_19a7061837c7eb88cf53');
        $this->forge->dropTable('consumo_armazenamento');
    }
}
