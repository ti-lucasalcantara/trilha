<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateManifestacoes extends Migration
{
    /** Cria a tabela manifestacoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'destinatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'texto' => ['type' => 'TEXT', 'null' => true],
            'documento_resposta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_69b976b6d754706c60f9');
        $this->forge->createTable('manifestacoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_34e46257295c24e84c6b');
        $this->forge->processIndexes('manifestacoes');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_id'], 'tramitacao_destinatarios', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c971c1a06dc5553cfee4');
        $this->forge->processIndexes('manifestacoes');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b5a22935f8ebad300698');
        $this->forge->processIndexes('manifestacoes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_resposta_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0bd5d15ed82283b05999');
        $this->forge->processIndexes('manifestacoes');
    }

    /** Reverte a tabela manifestacoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('manifestacoes', 'fk_0bd5d15ed82283b05999');
        $this->forge->dropForeignKey('manifestacoes', 'fk_b5a22935f8ebad300698');
        $this->forge->dropForeignKey('manifestacoes', 'fk_c971c1a06dc5553cfee4');
        $this->forge->dropForeignKey('manifestacoes', 'fk_34e46257295c24e84c6b');
        $this->forge->dropTable('manifestacoes');
    }
}
