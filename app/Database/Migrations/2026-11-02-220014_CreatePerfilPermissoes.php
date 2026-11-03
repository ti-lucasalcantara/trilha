<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePerfilPermissoes extends Migration
{
    /** Cria a tabela perfil_permissoes e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'perfil_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'permissao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_21d25e3cc0ba2d283a8c');
        $this->forge->addUniqueKey(['organizacao_id', 'perfil_id', 'permissao_id'], 'uq_655c9f55595e2b06f7d5');
        $this->forge->createTable('perfil_permissoes', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_7155037ce07a64849f37');
        $this->forge->processIndexes('perfil_permissoes');
        $this->forge->addForeignKey(['organizacao_id', 'perfil_id'], 'perfis', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_f443be9ecac6dc5ab212');
        $this->forge->processIndexes('perfil_permissoes');
        $this->forge->addForeignKey(['permissao_id'], 'permissoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_6afe7dc5620e72164b65');
        $this->forge->processIndexes('perfil_permissoes');
    }

    /** Reverte a tabela perfil_permissoes e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('perfil_permissoes', 'fk_6afe7dc5620e72164b65');
        $this->forge->dropForeignKey('perfil_permissoes', 'fk_f443be9ecac6dc5ab212');
        $this->forge->dropForeignKey('perfil_permissoes', 'fk_7155037ce07a64849f37');
        $this->forge->dropTable('perfil_permissoes');
    }
}
