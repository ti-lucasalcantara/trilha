<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateDocumentoEtiquetas extends Migration
{
    /** Cria a tabela documento_etiquetas e suas restrições; nome exigido pelo CI4. */
    public function up()
    {
        $this->forge->addField([
            'id' => ['type' => 'BIGINT', 'unsigned' => true, 'auto_increment' => true, 'null' => false],
            'organizacao_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'documento_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'etiqueta_id' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'aplicado_por' => ['type' => 'BIGINT', 'unsigned' => true, 'null' => false],
            'criado_em' => ['type' => 'DATETIME', 'null' => false],
            'atualizado_em' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['organizacao_id', 'id'], 'uq_3c67134e4a9c7642c5d3');
        $this->forge->addUniqueKey(['organizacao_id', 'documento_id', 'etiqueta_id'], 'uq_14fc356f47d9afd5457a');
        $this->forge->createTable('documento_etiquetas', false, ['ENGINE' => 'InnoDB']);
        $this->forge->addForeignKey(['organizacao_id'], 'organizacoes', ['id'], 'RESTRICT', 'RESTRICT', 'fk_770c62afd6c6d0d92a01');
        $this->forge->processIndexes('documento_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'documento_id'], 'documentos', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_84425e03819cd38ad3ba');
        $this->forge->processIndexes('documento_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'etiqueta_id'], 'etiquetas', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_62c5bb94e17b9a336040');
        $this->forge->processIndexes('documento_etiquetas');
        $this->forge->addForeignKey(['organizacao_id', 'aplicado_por'], 'vinculos_organizacao', ['organizacao_id', 'id'], 'RESTRICT', 'RESTRICT', 'fk_7213e1c33e49c89db5ce');
        $this->forge->processIndexes('documento_etiquetas');
    }

    /** Reverte a tabela documento_etiquetas e suas restrições; nome exigido pelo CI4. */
    public function down()
    {
        $this->forge->dropForeignKey('documento_etiquetas', 'fk_7213e1c33e49c89db5ce');
        $this->forge->dropForeignKey('documento_etiquetas', 'fk_62c5bb94e17b9a336040');
        $this->forge->dropForeignKey('documento_etiquetas', 'fk_84425e03819cd38ad3ba');
        $this->forge->dropForeignKey('documento_etiquetas', 'fk_770c62afd6c6d0d92a01');
        $this->forge->dropTable('documento_etiquetas');
    }
}
