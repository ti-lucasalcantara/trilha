<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessos extends Migration
{
    /** Cria a tabela processos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'regra_numeracao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'numero_sequencial' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ano' => ['type' => 'SMALLINT', 'unsigned' => true, 'null' => false],
            'numero_completo' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'assunto' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'descricao' => ['type' => 'TEXT', 'null' => true],
            'prioridade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'status_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'aberto_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'aberto_em' => ['type' => 'DATETIME', 'null' => false],
            'encerrado_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_8be57faf8a7f5561468a');
        $this->forge->addUniqueKey(['organizacao_id', 'numero_completo'], 'uq_14049854e31f840fde9c');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'ano', 'numero_sequencial'], 'uq_2fab65c9306085537935');
        $this->forge->addKey(['organizacao_id', 'status_id'], false, false, 'ix_ba54f2d626d391056913');
        $this->forge->addKey(['organizacao_id', 'numero_completo'], false, false, 'ix_0f3c7488a0c0273fc090');
        $this->forge->createTable('processos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_2bc761b3e9d058795d52');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id'], 'tipos_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_aa35c5ca4e6e1ff518b8');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_versao_id'], 'tipo_processo_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_0e5e63629fc9fef76b47');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'regra_numeracao_id'], 'regras_numeracao_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_a038a8a30ee77839335f');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'status_id'], 'status_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ba54f2d626d391056913');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_73edaa306ecce1882878');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_5241bc455a444416afdc');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'aberto_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e6b49b470ba3f00dee7d');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_9ea7ff7ca24839ce9a0a');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id', 'tipo_processo_versao_id'], 'tipo_processo_versoes', ['organizacao_id', 'tipo_processo_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_2cbbaa2ef47746e91e57');
        $this->forge->processIndexes('processos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id', 'regra_numeracao_id'], 'regras_numeracao_processo', ['organizacao_id', 'tipo_processo_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e53d1b54a79ecd5a1a91');
        $this->forge->processIndexes('processos');
    }

    /** Reverte a tabela processos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processos', 'fk_e53d1b54a79ecd5a1a91');
        $this->forge->dropForeignKey('processos', 'fk_2cbbaa2ef47746e91e57');
        $this->forge->dropForeignKey('processos', 'fk_9ea7ff7ca24839ce9a0a');
        $this->forge->dropForeignKey('processos', 'fk_e6b49b470ba3f00dee7d');
        $this->forge->dropForeignKey('processos', 'fk_5241bc455a444416afdc');
        $this->forge->dropForeignKey('processos', 'fk_73edaa306ecce1882878');
        $this->forge->dropForeignKey('processos', 'fk_ba54f2d626d391056913');
        $this->forge->dropForeignKey('processos', 'fk_a038a8a30ee77839335f');
        $this->forge->dropForeignKey('processos', 'fk_0e5e63629fc9fef76b47');
        $this->forge->dropForeignKey('processos', 'fk_aa35c5ca4e6e1ff518b8');
        $this->forge->dropForeignKey('processos', 'fk_2bc761b3e9d058795d52');
        $this->forge->dropTable('processos');
    }
}
