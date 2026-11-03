<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoEventosEstado extends Migration
{
    /** Cria a tabela processo_eventos_estado e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'estado_anterior_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'estado_novo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'motivo' => ['type' => 'TEXT', 'null' => true],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_cdf69ae0d0d08a67a529');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_f098c72fe02fe9d618ea');
        $this->forge->createTable('processo_eventos_estado', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0e9e3d04868740939bef');
        $this->forge->processIndexes('processo_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_67b6d9d5529a46aad14f');
        $this->forge->processIndexes('processo_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'estado_anterior_id'], 'status_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e3762639607843bf6a99');
        $this->forge->processIndexes('processo_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'estado_novo_id'], 'status_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6ad71bfd0304aed0ec33');
        $this->forge->processIndexes('processo_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_4acf28cb20117b57ce2e');
        $this->forge->processIndexes('processo_eventos_estado');
    }

    /** Reverte a tabela processo_eventos_estado e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_eventos_estado', 'fk_4acf28cb20117b57ce2e');
        $this->forge->dropForeignKey('processo_eventos_estado', 'fk_6ad71bfd0304aed0ec33');
        $this->forge->dropForeignKey('processo_eventos_estado', 'fk_e3762639607843bf6a99');
        $this->forge->dropForeignKey('processo_eventos_estado', 'fk_67b6d9d5529a46aad14f');
        $this->forge->dropForeignKey('processo_eventos_estado', 'fk_0e9e3d04868740939bef');
        $this->forge->dropTable('processo_eventos_estado');
    }
}
