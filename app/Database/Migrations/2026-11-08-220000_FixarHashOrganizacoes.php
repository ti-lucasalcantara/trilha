<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;
use RuntimeException;

/** Define o identificador público permanente das organizações. */
class FixarHashOrganizacoes extends Migration
{
    /** Preserva identificadores anteriores e protege os novos no banco. */
    public function up()
    {
        if (!$this->db->fieldExists('hash_publico', 'organizacoes')) {
            $this->db->query('ALTER TABLE organizacoes ADD hash_publico CHAR(48) CHARACTER SET ascii COLLATE ascii_bin NULL');
        }

        $this->db->query("
            UPDATE organizacoes o
            JOIN (
                SELECT organizacao_id, MIN(id) AS identificador_id
                FROM organizacao_identificadores
                WHERE tipo = 'hash_simulacao' AND valor REGEXP '^[0-9a-f]{48}$'
                GROUP BY organizacao_id
            ) anterior ON anterior.organizacao_id = o.id
            JOIN organizacao_identificadores i ON i.id = anterior.identificador_id
            SET o.hash_publico = LOWER(i.valor)
            WHERE o.hash_publico IS NULL
        ");

        if (!isset($this->db->getIndexData('organizacoes')['uq_organizacoes_hash_publico'])) {
            $this->db->query('CREATE UNIQUE INDEX uq_organizacoes_hash_publico ON organizacoes (hash_publico)');
        }
        do {
            $organizacoes = $this->db->table('organizacoes')->select('id')->where('hash_publico', null)
                ->orderBy('id')->get(500)->getResultArray();
            foreach ($organizacoes as $organizacao) {
                $salvou = $this->db->table('organizacoes')->where('id', $organizacao['id'])->where('hash_publico', null)
                    ->update(['hash_publico' => bin2hex(random_bytes(24))]);
                if (!$salvou) throw new RuntimeException('Falha ao gerar o identificador de uma organização.');
            }
        } while ($organizacoes);

        $this->db->query('ALTER TABLE organizacoes MODIFY hash_publico CHAR(48) CHARACTER SET ascii COLLATE ascii_bin NOT NULL');
        $gatilho = $this->db->query("SELECT 1 FROM information_schema.TRIGGERS
            WHERE TRIGGER_SCHEMA = DATABASE() AND TRIGGER_NAME = 'impedir_alteracao_hash_organizacao'")->getRowArray();
        if ($gatilho) return;

        $this->db->query("
            CREATE TRIGGER impedir_alteracao_hash_organizacao
            BEFORE UPDATE ON organizacoes FOR EACH ROW
            BEGIN
                IF NOT (NEW.hash_publico <=> OLD.hash_publico) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O identificador da organização é imutável.';
                END IF;
            END
        ");
    }

    /** Remove as proteções e a coluna criada nesta migration. */
    public function down()
    {
        $this->db->query('DROP TRIGGER IF EXISTS impedir_alteracao_hash_organizacao');
        $this->forge->dropColumn('organizacoes', 'hash_publico');
    }
}
