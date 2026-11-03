<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCobrancas extends Migration
{
    /** Cria a tabela cobrancas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'contratacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'referencia' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'valor' => ['type' => 'DECIMAL', 'constraint' => '19,2', 'null' => false],
            'moeda' => ['type' => 'VARCHAR', 'constraint' => 3, 'null' => false],
            'vencimento' => ['type' => 'DATE', 'null' => false],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'provedor' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => true],
            'identificador_provedor' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_f79a438840446409293b');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_af89ae9d263deb5916ca');
        $this->forge->createTable('cobrancas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_d4031fa172f25be88379');
        $this->forge->processIndexes('cobrancas');
        $this->forge->addForeignKey(['organizacao_id', 'contratacao_id'], 'contratacoes', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_d1e57d351c219b9d4393');
        $this->forge->processIndexes('cobrancas');
    }

    /** Reverte a tabela cobrancas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('cobrancas', 'fk_d1e57d351c219b9d4393');
        $this->forge->dropForeignKey('cobrancas', 'fk_d4031fa172f25be88379');
        $this->forge->dropTable('cobrancas');
    }
}
