<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuditoriaEventos extends Migration
{
    /** Cria a tabela auditoria_eventos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'usuario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'acao' => ['type' => 'VARCHAR', 'constraint' => 120, 'null' => false],
            'entidade' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'entidade_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'ip' => ['type' => 'VARCHAR', 'constraint' => 45, 'null' => true],
            'detalhes' => ['type' => 'JSON', 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_74272d3a30e76e1cd335');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_52596d101468081a1e35');
        $this->forge->createTable('auditoria_eventos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_700e1d326a002aa8ba2f');
        $this->forge->processIndexes('auditoria_eventos');
        $this->forge->addForeignKey(['usuario_id'], 'usuarios', ['id'], 'RESTRICT', 'RESTRICT', 'fk_d31c86d369ebdb9985e0');
        $this->forge->processIndexes('auditoria_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_291b3315e47d1e166194');
        $this->forge->processIndexes('auditoria_eventos');
    }

    /** Reverte a tabela auditoria_eventos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('auditoria_eventos', 'fk_291b3315e47d1e166194');
        $this->forge->dropForeignKey('auditoria_eventos', 'fk_d31c86d369ebdb9985e0');
        $this->forge->dropForeignKey('auditoria_eventos', 'fk_700e1d326a002aa8ba2f');
        $this->forge->dropTable('auditoria_eventos');
    }
}
