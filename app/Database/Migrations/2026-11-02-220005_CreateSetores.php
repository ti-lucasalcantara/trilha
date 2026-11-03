<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSetores extends Migration
{
    /** Cria a tabela setores e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'unidade_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'setor_superior_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'sigla' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'status' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_5aef4ee0b16984d63aeb');
        $this->forge->addUniqueKey(['organizacao_id', 'unidade_id', 'id'], 'uq_e3149ce10058b9436861');
        $this->forge->addKey(['organizacao_id', 'status'], false, false, 'ix_692e83bf1426ab55a704');
        $this->forge->createTable('setores', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_570a5fdaf313f121d04f');
        $this->forge->processIndexes('setores');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id'], 'unidades', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_8758d4f6a3b3a52c4c0c');
        $this->forge->processIndexes('setores');
        $this->forge->addForeignKey(['organizacao_id', 'setor_superior_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_3dca0edffc594350ec7a');
        $this->forge->processIndexes('setores');
        $this->forge->addForeignKey(['organizacao_id', 'unidade_id', 'setor_superior_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_6c58fd008cb31f48927d');
        $this->forge->processIndexes('setores');
    }

    /** Reverte a tabela setores e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('setores', 'fk_6c58fd008cb31f48927d');
        $this->forge->dropForeignKey('setores', 'fk_3dca0edffc594350ec7a');
        $this->forge->dropForeignKey('setores', 'fk_8758d4f6a3b3a52c4c0c');
        $this->forge->dropForeignKey('setores', 'fk_570a5fdaf313f121d04f');
        $this->forge->dropTable('setores');
    }
}
