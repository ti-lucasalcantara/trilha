<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrazoEventos extends Migration
{
    /** Cria a tabela prazo_eventos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'prazo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'evento' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'vencimento_anterior' => ['type' => 'DATETIME', 'null' => true],
            'novo_vencimento' => ['type' => 'DATETIME', 'null' => true],
            'motivo' => ['type' => 'TEXT', 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_5013c046891991396873');
        $this->forge->createTable('prazo_eventos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_72619d25a8318ce1cb36');
        $this->forge->processIndexes('prazo_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'prazo_id'], 'prazos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7146fa609ff8bdf87e72');
        $this->forge->processIndexes('prazo_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3cf14824c5324209666f');
        $this->forge->processIndexes('prazo_eventos');
    }

    /** Reverte a tabela prazo_eventos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('prazo_eventos', 'fk_3cf14824c5324209666f');
        $this->forge->dropForeignKey('prazo_eventos', 'fk_7146fa609ff8bdf87e72');
        $this->forge->dropForeignKey('prazo_eventos', 'fk_72619d25a8318ce1cb36');
        $this->forge->dropTable('prazo_eventos');
    }
}
