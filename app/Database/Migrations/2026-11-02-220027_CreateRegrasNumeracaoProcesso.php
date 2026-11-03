<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegrasNumeracaoProcesso extends Migration
{
    /** Cria a tabela regras_numeracao_processo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'mascara' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'sigla' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'digitos_minimos' => ['type' => 'BIGINT', 'null' => false],
            'vigente_desde' => ['type' => 'DATETIME', 'null' => false],
            'vigente_ate' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_cf03d03d8dd5c7e25195');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'versao'], 'uq_31bc0d0dcc87dd16fc5d');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_processo_id', 'id'], 'uq_eb43b677be1ae1a3d442');
        $this->forge->createTable('regras_numeracao_processo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_6f7d6c4f98fe3c84d5e7');
        $this->forge->processIndexes('regras_numeracao_processo');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_processo_id'], 'tipos_processo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1f7eccb71f6fa02932eb');
        $this->forge->processIndexes('regras_numeracao_processo');
    }

    /** Reverte a tabela regras_numeracao_processo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('regras_numeracao_processo', 'fk_1f7eccb71f6fa02932eb');
        $this->forge->dropForeignKey('regras_numeracao_processo', 'fk_6f7d6c4f98fe3c84d5e7');
        $this->forge->dropTable('regras_numeracao_processo');
    }
}
