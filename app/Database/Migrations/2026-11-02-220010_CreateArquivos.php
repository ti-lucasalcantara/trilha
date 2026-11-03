<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateArquivos extends Migration
{
    /** Cria a tabela arquivos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'chave_armazenamento' => ['type' => 'VARCHAR', 'constraint' => 500, 'null' => false],
            'nome_original' => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'mime_type' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'tamanho_bytes' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'hash' => ['type' => 'VARCHAR', 'constraint' => 128, 'null' => false],
            'algoritmo_hash' => ['type' => 'VARCHAR', 'constraint' => 30, 'null' => false],
            'status_verificacao' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9c5091fc023bba12e237');
        $this->forge->addUniqueKey(['organizacao_id', 'chave_armazenamento'], 'uq_cda862d48ed1c67745a6');
        $this->forge->createTable('arquivos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_cf49781e0581f0c93949');
        $this->forge->processIndexes('arquivos');
        $this->forge->addForeignKey(['organizacao_id', 'criado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_e935200f09e6f40f3693');
        $this->forge->processIndexes('arquivos');
    }

    /** Reverte a tabela arquivos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('arquivos', 'fk_e935200f09e6f40f3693');
        $this->forge->dropForeignKey('arquivos', 'fk_cf49781e0581f0c93949');
        $this->forge->dropTable('arquivos');
    }
}
