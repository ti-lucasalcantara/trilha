<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePrazos extends Migration
{
    /** Cria a tabela prazos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'destinatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'descricao' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'vence_em' => ['type' => 'DATETIME', 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'concluido_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_0c26c74f781d57c19f5f');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_7ca4c745f927cba8f9f3');
        $this->forge->addKey(['organizacao_id', 'vence_em'], false, false, 'ix_c35b835128f385600a3c');
        $this->forge->createTable('prazos', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('prazos') . '` ADD CONSTRAINT `ck_6cbe160c89698a3d74d8` CHECK ((processo_id IS NOT NULL) + (documento_id IS NOT NULL) + (destinatario_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_ae67d03356d397922bd3');
        $this->forge->processIndexes('prazos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5deb7449b4b2b778458a');
        $this->forge->processIndexes('prazos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c2ca46e381a18d336944');
        $this->forge->processIndexes('prazos');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_id'], 'tramitacao_destinatarios', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_de0dbf896aebacdc7334');
        $this->forge->processIndexes('prazos');
    }

    /** Reverte a tabela prazos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('prazos', 'fk_de0dbf896aebacdc7334');
        $this->forge->dropForeignKey('prazos', 'fk_c2ca46e381a18d336944');
        $this->forge->dropForeignKey('prazos', 'fk_5deb7449b4b2b778458a');
        $this->forge->dropForeignKey('prazos', 'fk_ae67d03356d397922bd3');
        $this->forge->dropTable('prazos');
    }
}
