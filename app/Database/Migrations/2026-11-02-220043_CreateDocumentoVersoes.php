<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoVersoes extends Migration
{
    /** Cria a tabela documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'numero_versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'modelo_versao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'conteudo_editor' => ['type' => 'LONGTEXT', 'null' => true],
            'arquivo_original_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'arquivo_final_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'hash_conteudo' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => true],
            'algoritmo_hash' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => true],
            'status_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criada_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'finalizada_em' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_705d777cd8a92d840ae9');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_id', 'numero_versao'], 'uq_2966cfbd1c152f674db6');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_id', 'id'], 'uq_21508ac7efbb8598502c');
        $this->forge->addKey(['organizacao_id', 'status_id'], false, false, 'ix_4a8aee50e1eec7312ea1');
        $this->forge->createTable('documento_versoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id', 'versao_atual_id'], 'documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_9419534c9dbd72ba739b');
        $this->forge->processIndexes('documentos');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_1183bd631a09e7a60114');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ac3e6da07fcffc1679d4');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'modelo_versao_id'], 'modelo_documento_versoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f79d7cd52ce08defbbd8');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'arquivo_original_id'], 'arquivos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ab662df6345ba411ab83');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'arquivo_final_id'], 'arquivos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b1fcf8a4ef2ea7369b11');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'status_id'], 'status_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_4a8aee50e1eec7312ea1');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'criada_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7cd7e3b479bb132f1154');
        $this->forge->processIndexes('documento_versoes');
        $this->forge->addForeignKey(['organizacao_id', 'id', 'versao_atual_id'], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b521d1d60f51fa54a8fc');
        $this->forge->processIndexes('documentos');
    }

    /** Reverte a tabela documento_versoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documentos', 'fk_b521d1d60f51fa54a8fc');
        $this->forge->dropForeignKey('documento_versoes', 'fk_7cd7e3b479bb132f1154');
        $this->forge->dropForeignKey('documento_versoes', 'fk_4a8aee50e1eec7312ea1');
        $this->forge->dropForeignKey('documento_versoes', 'fk_b1fcf8a4ef2ea7369b11');
        $this->forge->dropForeignKey('documento_versoes', 'fk_ab662df6345ba411ab83');
        $this->forge->dropForeignKey('documento_versoes', 'fk_f79d7cd52ce08defbbd8');
        $this->forge->dropForeignKey('documento_versoes', 'fk_ac3e6da07fcffc1679d4');
        $this->forge->dropForeignKey('documento_versoes', 'fk_1183bd631a09e7a60114');
        $this->forge->dropForeignKey('documentos', 'fk_9419534c9dbd72ba739b');
        $this->forge->dropTable('documento_versoes');
    }
}
