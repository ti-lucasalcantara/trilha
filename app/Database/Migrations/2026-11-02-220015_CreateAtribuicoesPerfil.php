<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAtribuicoesPerfil extends Migration
{
    /** Cria a tabela atribuicoes_perfil e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'perfil_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9a6bc045bebddaaddeae');
        $this->forge->createTable('atribuicoes_perfil', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_24ed1adc2de9ae40d919');
        $this->forge->processIndexes('atribuicoes_perfil');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d829f37caf064ec3f319');
        $this->forge->processIndexes('atribuicoes_perfil');
        $this->forge->addForeignKey(['organizacao_id', 'perfil_id'], 'perfis', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e4a6ca67b53cd2ddbdc5');
        $this->forge->processIndexes('atribuicoes_perfil');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b32dfa88068cc144a468');
        $this->forge->processIndexes('atribuicoes_perfil');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d30e46bd6ba3fa3fcfea');
        $this->forge->processIndexes('atribuicoes_perfil');
    }

    /** Reverte a tabela atribuicoes_perfil e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('atribuicoes_perfil', 'fk_d30e46bd6ba3fa3fcfea');
        $this->forge->dropForeignKey('atribuicoes_perfil', 'fk_b32dfa88068cc144a468');
        $this->forge->dropForeignKey('atribuicoes_perfil', 'fk_e4a6ca67b53cd2ddbdc5');
        $this->forge->dropForeignKey('atribuicoes_perfil', 'fk_d829f37caf064ec3f319');
        $this->forge->dropForeignKey('atribuicoes_perfil', 'fk_24ed1adc2de9ae40d919');
        $this->forge->dropTable('atribuicoes_perfil');
    }
}
