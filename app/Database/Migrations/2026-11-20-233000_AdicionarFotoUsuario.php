<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AdicionarFotoUsuario extends Migration
{
    public function up()
    {
        $this->forge->addColumn('usuarios', [
            'avatar_jpeg' => ['type' => 'MEDIUMBLOB', 'null' => true],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('usuarios', 'avatar_jpeg');
    }
}
