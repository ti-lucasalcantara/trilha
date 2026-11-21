<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdicionarDescricaoFormatadaProcesso extends Migration
{
    public function up()
    {
        $this->forge->addColumn('processos', [
            'descricao_formatada' => ['type' => 'TEXT', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('processos', 'descricao_formatada');
    }
}
