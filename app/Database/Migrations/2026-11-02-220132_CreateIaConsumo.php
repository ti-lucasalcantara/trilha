<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIaConsumo extends Migration
{
    /** Cria a tabela ia_consumo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'execucao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidades_entrada' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'unidades_saida' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'paginas' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'custo_estimado' => ['type' => 'DECIMAL', 'constraint' => '24,6', 'null' => true],
            'moeda' => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_6c3cc90dc6b77dd05b9b');
        $this->forge->createTable('ia_consumo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_112aff53edd1761aa3ab');
        $this->forge->processIndexes('ia_consumo');
        $this->forge->addForeignKey(['organizacao_id', 'execucao_id'], 'ia_execucoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b72d0daa35a742e54618');
        $this->forge->processIndexes('ia_consumo');
    }

    /** Reverte a tabela ia_consumo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('ia_consumo', 'fk_b72d0daa35a742e54618');
        $this->forge->dropForeignKey('ia_consumo', 'fk_112aff53edd1761aa3ab');
        $this->forge->dropTable('ia_consumo');
    }
}
