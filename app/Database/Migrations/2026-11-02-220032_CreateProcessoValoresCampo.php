<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateProcessoValoresCampo extends Migration
{
    /** Cria a tabela processo_valores_campo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'campo_configuracao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'valor_texto' => ['type' => 'TEXT', 'null' => true],
            'valor_numero' => ['type' => 'DECIMAL', 'constraint' => '24,6', 'null' => true],
            'valor_data' => ['type' => 'DATETIME', 'null' => true],
            'valor_booleano' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_f243296ce9c4a58984e8');
        $this->forge->addUniqueKey(['organizacao_id', 'processo_id', 'campo_configuracao_id'], 'uq_5eb9299165fd38a96533');
        $this->forge->createTable('processo_valores_campo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_11d27770122aaf076fac');
        $this->forge->processIndexes('processo_valores_campo');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3d496c9ec474d162a77c');
        $this->forge->processIndexes('processo_valores_campo');
        $this->forge->addForeignKey(['organizacao_id', 'campo_configuracao_id'], 'tipo_processo_campos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3314134910278aecf53d');
        $this->forge->processIndexes('processo_valores_campo');
    }

    /** Reverte a tabela processo_valores_campo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('processo_valores_campo', 'fk_3314134910278aecf53d');
        $this->forge->dropForeignKey('processo_valores_campo', 'fk_3d496c9ec474d162a77c');
        $this->forge->dropForeignKey('processo_valores_campo', 'fk_11d27770122aaf076fac');
        $this->forge->dropTable('processo_valores_campo');
    }
}
