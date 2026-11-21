<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DefinirSituacaoInicialProcesso extends Migration
{
    public function up()
    {
        $tabela = $this->db->protectIdentifiers('status_processo', true);
        // NULL permite várias situações comuns e o valor 1 é único por organização.
        $this->db->query("ALTER TABLE {$tabela}
            ADD COLUMN inicial TINYINT UNSIGNED NULL DEFAULT NULL,
            ADD UNIQUE KEY uq_status_processo_inicial (organizacao_id, inicial)");
    }

    public function down()
    {
        $tabela = $this->db->protectIdentifiers('status_processo', true);
        $this->db->query("ALTER TABLE {$tabela} DROP INDEX uq_status_processo_inicial, DROP COLUMN inicial");
    }
}
