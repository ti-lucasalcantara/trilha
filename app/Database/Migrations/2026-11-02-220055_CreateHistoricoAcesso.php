<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoricoAcesso extends Migration
{
    /** Cria a tabela historico_acesso e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'politica_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'evento' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'dados_alteracao' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_25dc1e9dc164195f655e');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_53c2c4fc50482033b679');
        $this->forge->createTable('historico_acesso', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_42461b1e23c0f16a798f');
        $this->forge->processIndexes('historico_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'politica_id'], 'politicas_acesso', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0f8345edd48b9a36b92d');
        $this->forge->processIndexes('historico_acesso');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_42a83998aab2382643b9');
        $this->forge->processIndexes('historico_acesso');
    }

    /** Reverte a tabela historico_acesso e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('historico_acesso', 'fk_42a83998aab2382643b9');
        $this->forge->dropForeignKey('historico_acesso', 'fk_0f8345edd48b9a36b92d');
        $this->forge->dropForeignKey('historico_acesso', 'fk_42461b1e23c0f16a798f');
        $this->forge->dropTable('historico_acesso');
    }
}
