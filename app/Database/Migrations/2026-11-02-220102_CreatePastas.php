<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePastas extends Migration
{
    /** Cria a tabela pastas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'pasta_pai_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'nome' => ['type' => 'VARCHAR', 'constraint' => 200, 'null' => false],
            'proprietario_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'setor_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => true],
            'visibilidade' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => false],
            'criada_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_9b84de4b742a66b6f4f8');
        $this->forge->createTable('pastas', false, ['ENGINE' => 'InnoDB']);
        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('pastas') . '` ADD CONSTRAINT `ck_ddd8bfbcd5f555bececd` CHECK ((proprietario_id IS NOT NULL) + (setor_id IS NOT NULL) = 1)');
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_0db0319fb9116fd01fa6');
        $this->forge->processIndexes('pastas');
        $this->forge->addForeignKey(['organizacao_id', 'pasta_pai_id'], 'pastas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_18e156d04247c68bfa0c');
        $this->forge->processIndexes('pastas');
        $this->forge->addForeignKey(['organizacao_id', 'proprietario_id'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_ad452a3157f830e522f6');
        $this->forge->processIndexes('pastas');
        $this->forge->addForeignKey(['organizacao_id', 'setor_id'], 'setores', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_bd182dcd56301240f01f');
        $this->forge->processIndexes('pastas');
        $this->forge->addForeignKey(['organizacao_id', 'criada_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_48d5be5f50ca94a71116');
        $this->forge->processIndexes('pastas');
    }

    /** Reverte a tabela pastas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('pastas', 'fk_48d5be5f50ca94a71116');
        $this->forge->dropForeignKey('pastas', 'fk_bd182dcd56301240f01f');
        $this->forge->dropForeignKey('pastas', 'fk_ad452a3157f830e522f6');
        $this->forge->dropForeignKey('pastas', 'fk_18e156d04247c68bfa0c');
        $this->forge->dropForeignKey('pastas', 'fk_0db0319fb9116fd01fa6');
        $this->forge->dropTable('pastas');
    }
}
