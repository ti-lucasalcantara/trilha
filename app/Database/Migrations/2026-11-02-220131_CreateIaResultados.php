<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateIaResultados extends Migration
{
    /** Cria a tabela ia_resultados e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'execucao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'conteudo' => ['type' => 'LONGTEXT', 'null' => false],
            'status_revisao' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'revisado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'revisado_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9631db318edb4d6a4a7c');
        $this->forge->createTable('ia_resultados', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_f06517c48116959ae9e3');
        $this->forge->processIndexes('ia_resultados');
        $this->forge->addForeignKey(['organizacao_id', 'execucao_id'], 'ia_execucoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_eb7d1dd8375a5ff09e09');
        $this->forge->processIndexes('ia_resultados');
        $this->forge->addForeignKey(['organizacao_id', 'revisado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_47dc4f2265177e86fae0');
        $this->forge->processIndexes('ia_resultados');
    }

    /** Reverte a tabela ia_resultados e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('ia_resultados', 'fk_47dc4f2265177e86fae0');
        $this->forge->dropForeignKey('ia_resultados', 'fk_eb7d1dd8375a5ff09e09');
        $this->forge->dropForeignKey('ia_resultados', 'fk_f06517c48116959ae9e3');
        $this->forge->dropTable('ia_resultados');
    }
}
