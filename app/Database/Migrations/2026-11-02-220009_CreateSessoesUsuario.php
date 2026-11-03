<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSessoesUsuario extends Migration
{
    /** Cria a tabela sessoes_usuario e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'usuario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'token_hash' => ['type' => 'VARCHAR', 'constraint' => 64, 'null' => false],
            'expira_em' => ['type' => 'DATETIME', 'null' => false],
            'revogado_em' => ['type' => 'DATETIME', 'null' => true],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'agente_usuario' => ['type' => 'TEXT', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['token_hash'], 'uq_617eb734647261cd5aa6');
        $this->forge->addKey(['expira_em'], false, false, 'ix_71336c9c6970da4d91e9');
        $this->forge->createTable('sessoes_usuario', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['usuario_id'], 'usuarios', ['id'], 'RESTRICT', 'RESTRICT', 'fk_fde62168ab1c294c6225');
        $this->forge->processIndexes('sessoes_usuario');
    }

    /** Reverte a tabela sessoes_usuario e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('sessoes_usuario', 'fk_fde62168ab1c294c6225');
        $this->forge->dropTable('sessoes_usuario');
    }
}
