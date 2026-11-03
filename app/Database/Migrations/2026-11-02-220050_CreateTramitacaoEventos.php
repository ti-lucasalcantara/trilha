<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTramitacaoEventos extends Migration
{
    /** Cria a tabela tramitacao_eventos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'destinatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_evento' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'motivo' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_ed14bd30aad2ecafc0ca');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_b6c110d35ae8a4f987a9');
        $this->forge->createTable('tramitacao_eventos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_532df36321a6582ddd2b');
        $this->forge->processIndexes('tramitacao_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_id'], 'tramitacao_destinatarios', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b9081816d134c550dda0');
        $this->forge->processIndexes('tramitacao_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_01425629c2da0a33f61b');
        $this->forge->processIndexes('tramitacao_eventos');
    }

    /** Reverte a tabela tramitacao_eventos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tramitacao_eventos', 'fk_01425629c2da0a33f61b');
        $this->forge->dropForeignKey('tramitacao_eventos', 'fk_b9081816d134c550dda0');
        $this->forge->dropForeignKey('tramitacao_eventos', 'fk_532df36321a6582ddd2b');
        $this->forge->dropTable('tramitacao_eventos');
    }
}
