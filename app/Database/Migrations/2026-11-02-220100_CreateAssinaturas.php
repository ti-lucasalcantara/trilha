<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAssinaturas extends Migration
{
    /** Cria a tabela assinaturas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'solicitacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'signatario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome_registrado' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'cargo_registrado' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'metodo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'hash_assinado' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'algoritmo_hash' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'assinada_em' => ['type' => 'DATETIME', 'null' => false],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => false],
            'agente_usuario' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_8f8b75c45d5a9615310f');
        $this->forge->addUniqueKey(['organizacao_id', 'solicitacao_id'], 'uq_b59688177a03b2cc6c01');
        $this->forge->createTable('assinaturas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_722f43ad3f82eb2668dc');
        $this->forge->processIndexes('assinaturas');
        $this->forge->addForeignKey(['organizacao_id', 'solicitacao_id'], 'solicitacoes_assinatura', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ea16b826546553c3fc73');
        $this->forge->processIndexes('assinaturas');
        $this->forge->addForeignKey(['organizacao_id', 'documento_versao_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_4ed887303f2d35e6b408');
        $this->forge->processIndexes('assinaturas');
        $this->forge->addForeignKey(['organizacao_id', 'signatario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b7f4d5faa42bb1278970');
        $this->forge->processIndexes('assinaturas');
        $this->forge->addForeignKey(['organizacao_id', 'solicitacao_id', 'documento_versao_id', 'signatario_id'], 'solicitacoes_assinatura', ['organizacao_id', 'id', 'documento_versao_id', 'signatario_id'], 'RESTRICT', 'RESTRICT', 'fk_81b688961b1341968804');
        $this->forge->processIndexes('assinaturas');
    }

    /** Reverte a tabela assinaturas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('assinaturas', 'fk_81b688961b1341968804');
        $this->forge->dropForeignKey('assinaturas', 'fk_b7f4d5faa42bb1278970');
        $this->forge->dropForeignKey('assinaturas', 'fk_4ed887303f2d35e6b408');
        $this->forge->dropForeignKey('assinaturas', 'fk_ea16b826546553c3fc73');
        $this->forge->dropForeignKey('assinaturas', 'fk_722f43ad3f82eb2668dc');
        $this->forge->dropTable('assinaturas');
    }
}
