<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAtribuicoesResponsabilidade extends Migration
{
    /** Cria a tabela atribuicoes_responsabilidade e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'destinatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'responsavel_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'atribuido_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'inicio_em' => ['type' => 'DATETIME', 'null' => false],
            'fim_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_1489a45eda379c52f324');
        $this->forge->createTable('atribuicoes_responsabilidade', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('atribuicoes_responsabilidade') . '` ADD CONSTRAINT `ck_4284d21279bd0661cd46` CHECK ((processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8ad9d68f816f70e789da');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3ff056dd2d0a3b99d252');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c550f196fe6673ea3d91');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
        $this->forge->addForeignKey(['organizacao_id', 'destinatario_id'], 'tramitacao_destinatarios', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b44e10d8e3130c116d81');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
        $this->forge->addForeignKey(['organizacao_id', 'responsavel_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1b85838aa1c95ba7fde7');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
        $this->forge->addForeignKey(['organizacao_id', 'atribuido_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_82d82c272de82cabf77f');
        $this->forge->processIndexes('atribuicoes_responsabilidade');
    }

    /** Reverte a tabela atribuicoes_responsabilidade e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_82d82c272de82cabf77f');
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_1b85838aa1c95ba7fde7');
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_b44e10d8e3130c116d81');
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_c550f196fe6673ea3d91');
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_3ff056dd2d0a3b99d252');
        $this->forge->dropForeignKey('atribuicoes_responsabilidade', 'fk_8ad9d68f816f70e789da');
        $this->forge->dropTable('atribuicoes_responsabilidade');
    }
}
