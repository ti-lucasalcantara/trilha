<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateEtiquetas extends Migration
{
    /** Cria a tabela etiquetas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => false],
            'cor' => ['type' => 'VARCHAR', 'constraint' => 20, 'null' => true],
            'escopo' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'proprietario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_061d146d439276f98f19');
        $this->forge->createTable('etiquetas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_596df7df08452473f527');
        $this->forge->processIndexes('etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'proprietario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_b145e87058a58b0dc379');
        $this->forge->processIndexes('etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_69d6fb3591e84dc0a652');
        $this->forge->processIndexes('etiquetas');
    }

    /** Reverte a tabela etiquetas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('etiquetas', 'fk_69d6fb3591e84dc0a652');
        $this->forge->dropForeignKey('etiquetas', 'fk_b145e87058a58b0dc379');
        $this->forge->dropForeignKey('etiquetas', 'fk_596df7df08452473f527');
        $this->forge->dropTable('etiquetas');
    }
}
