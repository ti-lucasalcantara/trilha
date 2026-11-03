<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateOpcoesCampo extends Migration
{
    /** Cria a tabela opcoes_campo e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'campo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'valor' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => false],
            'rotulo' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'ordem' => ['type' => 'BIGINT', 'null' => false],
            'ativo' => ['type' => 'TINYINT', 'constraint' => 1, 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_2670d02c6143ac8b4be1');
        $this->forge->addUniqueKey(['organizacao_id', 'campo_id', 'valor'], 'uq_a7ced7eb96f76b7a6669');
        $this->forge->createTable('opcoes_campo', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_8e33d99dce4031fe15d1');
        $this->forge->processIndexes('opcoes_campo');
        $this->forge->addForeignKey(['organizacao_id', 'campo_id'], 'definicoes_campo', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_99172a46c693e1eb18c5');
        $this->forge->processIndexes('opcoes_campo');
    }

    /** Reverte a tabela opcoes_campo e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('opcoes_campo', 'fk_99172a46c693e1eb18c5');
        $this->forge->dropForeignKey('opcoes_campo', 'fk_8e33d99dce4031fe15d1');
        $this->forge->dropTable('opcoes_campo');
    }
}
