<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Registra o administrador fundador e seu perfil por organização. */
class CriarAdministradoresOrganizacao extends Migration
{
    /** Protege o vínculo e o perfil com chaves estrangeiras da própria organização. */
    public function up()
    {
        $this->forge->addField([
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'vinculo_id' => ['type' => 'BIGINT', 'unsigned' => true],
            'perfil_id' => ['type' => 'BIGINT', 'unsigned' => true],
        ]);
        $this->forge->addPrimaryKey('organizacao_id');
        $this->forge->addForeignKey(['organizacao_id', 'vinculo_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT');
        $this->forge->addForeignKey(['organizacao_id', 'perfil_id'], 'perfis', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT');
        $this->forge->createTable('administradores_organizacao', true, ['ENGINE' => 'InnoDB']);
    }

    /** Remove a marcação de administradores fundadores. */
    public function down()
    {
        $this->forge->dropTable('administradores_organizacao', true);
    }
}
