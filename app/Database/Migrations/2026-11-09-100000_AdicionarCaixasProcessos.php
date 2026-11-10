<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Registra arquivamento e exclusão recuperável sem alterar a situação do processo. */
class AdicionarCaixasProcessos extends Migration
{
    /** Adiciona as datas das duas operações. */
    public function up()
    {
        $this->forge->addColumn('processos', [
            'arquivado_em' => ['type' => 'DATETIME', 'null' => true],
            'excluido_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
    }

    /** Remove as datas introduzidas pela migration. */
    public function down()
    {
        $this->forge->dropColumn('processos', ['arquivado_em', 'excluido_em']);
    }
}
