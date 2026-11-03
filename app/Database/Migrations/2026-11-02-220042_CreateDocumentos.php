<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentos extends Migration
{
    /** Cria a tabela documentos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_principal_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'origem' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'status_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_origem_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao_atual_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'regra_numeracao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'numero_sequencial' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'periodo_numeracao' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'numero_completo' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_bc7d7c446c7584ba0dd7');
        $this->forge->addUniqueKey(['organizacao_id', 'numero_completo'], 'uq_75a5b543967416c9b227');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'periodo_numeracao', 'numero_sequencial'], 'uq_842528b35367f799a92f');
        $this->forge->addKey(['organizacao_id', 'status_id'], false, false, 'ix_1e07083276010652155f');
        $this->forge->addKey(['organizacao_id', 'numero_completo'], false, false, 'ix_aba69385a95717fd9003');
        $this->forge->createTable('documentos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_3b3c16011fe8e3dd8655');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_88247196c66ffdc9adac');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_versao_id'], 'tipo_documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7bf39e0a0ae77f879adc');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_principal_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f0c05c5db40f0078bd5a');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'status_id'], 'status_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1e07083276010652155f');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c97a64d6c6eff3e6c0a6');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ff863b01dfd24b4aa8bf');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'regra_numeracao_id'], 'regras_numeracao_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_4c89febf51b80fa9367b');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_de28236b62656e56a83b');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_origem_id', 'setor_origem_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_904d8388516935823c85');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id', 'tipo_documento_versao_id'], 'tipo_documento_versoes', ['organizacao_id', 'tipo_documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b4288be775bc103354ff');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id', 'regra_numeracao_id'], 'regras_numeracao_documento', ['organizacao_id', 'tipo_documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_2f8f45d488186a219f5f');
        $this->forge->processIndexes('documentos');
    }

    /** Reverte a tabela documentos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documentos', 'fk_2f8f45d488186a219f5f');
        $this->forge->dropForeignKey('documentos', 'fk_b4288be775bc103354ff');
        $this->forge->dropForeignKey('documentos', 'fk_904d8388516935823c85');
        $this->forge->dropForeignKey('documentos', 'fk_de28236b62656e56a83b');
        $this->forge->dropForeignKey('documentos', 'fk_4c89febf51b80fa9367b');
        $this->forge->dropForeignKey('documentos', 'fk_ff863b01dfd24b4aa8bf');
        $this->forge->dropForeignKey('documentos', 'fk_c97a64d6c6eff3e6c0a6');
        $this->forge->dropForeignKey('documentos', 'fk_1e07083276010652155f');
        $this->forge->dropForeignKey('documentos', 'fk_f0c05c5db40f0078bd5a');
        $this->forge->dropForeignKey('documentos', 'fk_7bf39e0a0ae77f879adc');
        $this->forge->dropForeignKey('documentos', 'fk_88247196c66ffdc9adac');
        $this->forge->dropForeignKey('documentos', 'fk_3b3c16011fe8e3dd8655');
        $this->forge->dropTable('documentos');
    }
}
