<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRegrasNumeracaoDocumento extends Migration
{
    /** Cria a tabela regras_numeracao_documento e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'tipo_documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'versao' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'mascara' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'sigla' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'digitos_minimos' => ['type' => 'BIGINT', 'null' => false],
            'reinicia_anualmente' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'vigente_desde' => ['type' => 'DATETIME', 'null' => false],
            'vigente_ate' => ['type' => 'DATETIME', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_45a2a13d24ea223a4da1');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'versao'], 'uq_afca036c1e7d8973794a');
        $this->forge->addUniqueKey(['organizacao_id', 'tipo_documento_id', 'id'], 'uq_7285962748b347234d1e');
        $this->forge->createTable('regras_numeracao_documento', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_4bfe00a27e2c69d09ef9');
        $this->forge->processIndexes('regras_numeracao_documento');
        $this->forge->addForeignKey(['organizacao_id', 'tipo_documento_id'], 'tipos_documento', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_c354966e8061f53858f0');
        $this->forge->processIndexes('regras_numeracao_documento');
    }

    /** Reverte a tabela regras_numeracao_documento e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('regras_numeracao_documento', 'fk_c354966e8061f53858f0');
        $this->forge->dropForeignKey('regras_numeracao_documento', 'fk_4bfe00a27e2c69d09ef9');
        $this->forge->dropTable('regras_numeracao_documento');
    }
}
