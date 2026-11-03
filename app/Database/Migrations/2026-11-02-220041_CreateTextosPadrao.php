<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTextosPadrao extends Migration
{
    /** Cria a tabela textos_padrao e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'proprietario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'conteudo' => ['type' => 'LONGTEXT', 'null' => false],
            'visibilidade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a1d7312af982c44c81eb');
        $this->forge->createTable('textos_padrao', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_923cf6d97aec0e0f1a64');
        $this->forge->processIndexes('textos_padrao');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_edac2dbbfc80cb258cf9');
        $this->forge->processIndexes('textos_padrao');
        $this->forge->addForeignKey(['organizacao_id', 'proprietario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e6280ff96a85aa06d893');
        $this->forge->processIndexes('textos_padrao');
    }

    /** Reverte a tabela textos_padrao e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('textos_padrao', 'fk_e6280ff96a85aa06d893');
        $this->forge->dropForeignKey('textos_padrao', 'fk_edac2dbbfc80cb258cf9');
        $this->forge->dropForeignKey('textos_padrao', 'fk_923cf6d97aec0e0f1a64');
        $this->forge->dropTable('textos_padrao');
    }
}
