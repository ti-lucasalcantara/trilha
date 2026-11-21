<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CriarRascunhosDocumento extends Migration
{
    public function up()
    {
        $id = ['type' => 'BIGINT', 'unsigned' => true];
        $this->forge->addField([
            'chave' => ['type' => 'CHAR', 'constraint' => 48],
            'organizacao_id' => $id,
            'processo_id' => $id,
            'setor_id' => $id,
            'autor_id' => $id,
            'tipo_id' => $id,
            'documento_id' => $id + ['null' => true],
            'titulo' => ['type' => 'VARCHAR', 'constraint' => 500],
            'conteudo' => ['type' => 'TEXT', 'null' => true],
            'revisao' => ['type' => 'CHAR', 'constraint' => 48],
            'atualizado_em' => ['type' => 'DATETIME'],
        ]);
        $this->forge->addPrimaryKey('chave');
        $this->forge->addKey(['organizacao_id', 'processo_id', 'setor_id']);
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'CASCADE');
        $this->forge->createTable('rascunhos_documento', false, ['ENGINE' => 'InnoDB']);
    }

    public function down()
    {
        $this->forge->dropTable('rascunhos_documento');
    }
}
