<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSolicitacoesAssinatura extends Migration
{
    /** Cria a tabela solicitacoes_assinatura e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'bloco_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'signatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'ordem' => ['type' => 'BIGINT', 'null' => true],
            'obrigatoria' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'prazo_em' => ['type' => 'DATETIME', 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'solicitada_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1c6e40ba20dc3b33d16b');
        $this->forge->addUniqueKey(['organizacao_id', 'id', 'documento_versao_id', 'signatario_id'], 'uq_52da1a51fcaf9b451e94');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_412e3cdfccc331348464');
        $this->forge->createTable('solicitacoes_assinatura', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_597f23be10c50f6e00ce');
        $this->forge->processIndexes('solicitacoes_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_2b04374b3af44c65898b');
        $this->forge->processIndexes('solicitacoes_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'bloco_id'], 'blocos_assinatura', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_43b15d93bb7cc5b89c9c');
        $this->forge->processIndexes('solicitacoes_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'signatario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_eb5bfbb92244506a1431');
        $this->forge->processIndexes('solicitacoes_assinatura');
        $this->forge->addForeignKey(['organizacao_id', 'solicitada_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_def3e8070131ffe7efbc');
        $this->forge->processIndexes('solicitacoes_assinatura');
    }

    /** Reverte a tabela solicitacoes_assinatura e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('solicitacoes_assinatura', 'fk_def3e8070131ffe7efbc');
        $this->forge->dropForeignKey('solicitacoes_assinatura', 'fk_eb5bfbb92244506a1431');
        $this->forge->dropForeignKey('solicitacoes_assinatura', 'fk_43b15d93bb7cc5b89c9c');
        $this->forge->dropForeignKey('solicitacoes_assinatura', 'fk_2b04374b3af44c65898b');
        $this->forge->dropForeignKey('solicitacoes_assinatura', 'fk_597f23be10c50f6e00ce');
        $this->forge->dropTable('solicitacoes_assinatura');
    }
}
