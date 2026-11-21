<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdicionarExclusaoDocumentos extends Migration
{
    public function up()
    {
        $this->forge->addColumn('documentos', ['excluido_em' => ['type' => 'DATETIME', 'null' => true]]);
    }

    public function down()
    {
        $this->forge->dropColumn('documentos', 'excluido_em');
    }
}
