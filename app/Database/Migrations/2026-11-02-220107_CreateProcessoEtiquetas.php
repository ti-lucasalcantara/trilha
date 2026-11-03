<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoEtiquetas extends Migration
{
    /** Cria a tabela processo_etiquetas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'etiqueta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'aplicado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_2ec0208c0fc9e025bf66');
        $this->forge->addUniqueKey(['organizacao_id', 'processo_id', 'etiqueta_id'], 'uq_c541a1ea135b0b42ca5f');
        $this->forge->createTable('processo_etiquetas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0c3b63b48e79dca434f9');
        $this->forge->processIndexes('processo_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_99fdbcf191c51716e710');
        $this->forge->processIndexes('processo_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'etiqueta_id'], 'etiquetas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_86bd1a435ade8b237b6b');
        $this->forge->processIndexes('processo_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'aplicado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0bb455b3cbe5d6fbf17d');
        $this->forge->processIndexes('processo_etiquetas');
    }

    /** Reverte a tabela processo_etiquetas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_etiquetas', 'fk_0bb455b3cbe5d6fbf17d');
        $this->forge->dropForeignKey('processo_etiquetas', 'fk_86bd1a435ade8b237b6b');
        $this->forge->dropForeignKey('processo_etiquetas', 'fk_99fdbcf191c51716e710');
        $this->forge->dropForeignKey('processo_etiquetas', 'fk_0c3b63b48e79dca434f9');
        $this->forge->dropTable('processo_etiquetas');
    }
}
