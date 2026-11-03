<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTipoProcessoVersoes extends Migration
{
    /** Cria a tabela tipo_processo_versoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'instrucoes' => ['type' => 'TEXT', 'null' => true],
            'acesso_padrao' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'prazo_padrao_dias' => ['type' => 'BIGINT', 'null' => true],
            'publicado_em' => ['type' => 'DATETIME', 'null' => true],
            'publicado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_a5595af979da5015c84d');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'versao'], 'uq_ee953cc79c02da218446');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'id'], 'uq_89d40a64bc1ba90fa3f4');
        $this->forge->createTable('tipo_processo_versoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8998f61255b2bfa1515a');
        $this->forge->processIndexes('tipo_processo_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id'], 'tipos_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a93c1e7d50458ca1b1fe');
        $this->forge->processIndexes('tipo_processo_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'publicado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7dc44691f6492c235491');
        $this->forge->processIndexes('tipo_processo_versoes');
    }

    /** Reverte a tabela tipo_processo_versoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('tipo_processo_versoes', 'fk_7dc44691f6492c235491');
        $this->forge->dropForeignKey('tipo_processo_versoes', 'fk_a93c1e7d50458ca1b1fe');
        $this->forge->dropForeignKey('tipo_processo_versoes', 'fk_8998f61255b2bfa1515a');
        $this->forge->dropTable('tipo_processo_versoes');
    }
}
