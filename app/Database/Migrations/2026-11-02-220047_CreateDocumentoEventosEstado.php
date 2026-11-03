<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoEventosEstado extends Migration
{
    /** Cria a tabela documento_eventos_estado e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status_anterior_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'status_novo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'evento' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'motivo' => ['type' => 'TEXT', 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_3775ba2c5cd5d185f032');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_5418704fbcb79b746a50');
        $this->forge->createTable('documento_eventos_estado', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_5e55489989e378a1a482');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b675e7ba443df9f50fba');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_951f2ed6ed4cf4786445');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'status_anterior_id'], 'status_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c5a4aae3d8943e815ea5');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'status_novo_id'], 'status_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_bcced974c2ac5a0bc25e');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f2029b4eb67971c3377a');
        $this->forge->processIndexes('documento_eventos_estado');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id', 'versao_id'], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_70c1bf85bf57de4078c6');
        $this->forge->processIndexes('documento_eventos_estado');
    }

    /** Reverte a tabela documento_eventos_estado e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_70c1bf85bf57de4078c6');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_f2029b4eb67971c3377a');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_bcced974c2ac5a0bc25e');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_c5a4aae3d8943e815ea5');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_951f2ed6ed4cf4786445');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_b675e7ba443df9f50fba');
        $this->forge->dropForeignKey('documento_eventos_estado', 'fk_5e55489989e378a1a482');
        $this->forge->dropTable('documento_eventos_estado');
    }
}
