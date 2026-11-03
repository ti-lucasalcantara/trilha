<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateHistoricoEventos extends Migration
{
    /** Cria a tabela historico_eventos e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'processo_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'tipo' => ['type' => 'VARCHAR', 'constraint' => 80, 'null' => false],
            'autor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'ocorrido_em' => ['type' => 'DATETIME', 'null' => false],
            'detalhes' => ['type' => 'JSON', 'null' => true],
            'visibilidade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_c7aa578b2ffc5f698b3e');
        $this->forge->addKey(['organizacao_id', 'ocorrido_em'], false, false, 'ix_3054d1fe37529f0d7adb');
        $this->forge->createTable('historico_eventos', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_4cb6b4deb8f36ecbadfe');
        $this->forge->processIndexes('historico_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'processo_id'], 'processos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_1724df6205e57b137d74');
        $this->forge->processIndexes('historico_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_dc9c3a6359cbcaf03911');
        $this->forge->processIndexes('historico_eventos');
        $this->forge->addForeignKey(['organizacao_id', 'autor_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f5b6744ef0ebd43b4a63');
        $this->forge->processIndexes('historico_eventos');
    }

    /** Reverte a tabela historico_eventos e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('historico_eventos', 'fk_f5b6744ef0ebd43b4a63');
        $this->forge->dropForeignKey('historico_eventos', 'fk_dc9c3a6359cbcaf03911');
        $this->forge->dropForeignKey('historico_eventos', 'fk_1724df6205e57b137d74');
        $this->forge->dropForeignKey('historico_eventos', 'fk_4cb6b4deb8f36ecbadfe');
        $this->forge->dropTable('historico_eventos');
    }
}
