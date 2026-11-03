<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlocosAssinatura extends Migration
{
    /** Cria a tabela blocos_assinatura e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'setor_criador_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_8325566334b2c367f1f9');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_6c13a940a846983a398a');
        $this->forge->createTable('blocos_assinatura', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8542d4a270e760461e96');
        $this->forge->processIndexes('blocos_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'setor_criador_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7b3117a180a5ae45e4c7');
        $this->forge->processIndexes('blocos_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a89a09fa991ef271a61d');
        $this->forge->processIndexes('blocos_assinatura');
    }

    /** Reverte a tabela blocos_assinatura e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('blocos_assinatura', 'fk_a89a09fa991ef271a61d');
        $this->forge->dropForeignKey('blocos_assinatura', 'fk_7b3117a180a5ae45e4c7');
        $this->forge->dropForeignKey('blocos_assinatura', 'fk_8542d4a270e760461e96');
        $this->forge->dropTable('blocos_assinatura');
    }
}
