<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Cria um identificador público permanente para cada processo. */
class FixarHashProcessos extends Migration
{
    /** Preenche os processos existentes antes de exigir o identificador. */
    public function up()
    {
        if (!$this->db->fieldExists('hash_publico', 'processos')) {
            $this->db->query('ALTER TABLE processos ADD hash_publico CHAR(48) CHARACTER SET ascii COLLATE ascii_bin NULL');
        }
        if (!isset($this->db->getIndexData('processos')['uq_processos_hash_publico'])) {
            $this->db->query('CREATE UNIQUE INDEX uq_processos_hash_publico ON processos (hash_publico)');
        }
        do {
            $processos = $this->db->table('processos')->select('id')->where('hash_publico', null)->orderBy('id')->get(500)->getResultArray();
            foreach ($processos as $processo) {
                if (!$this->db->table('processos')->where('id', $processo['id'])->where('hash_publico', null)
                    ->update(['hash_publico' => bin2hex(random_bytes(24))])) {
                    throw new \RuntimeException('Não foi possível atribuir o hash do processo.');
                }
            }
        } while ($processos);

        $this->db->query('ALTER TABLE processos MODIFY hash_publico CHAR(48) CHARACTER SET ascii COLLATE ascii_bin NOT NULL');
        $this->db->query("CREATE TRIGGER IF NOT EXISTS impedir_alteracao_hash_processo BEFORE UPDATE ON processos FOR EACH ROW
            BEGIN
                IF NOT (NEW.hash_publico <=> OLD.hash_publico) THEN
                    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'O identificador do processo é imutável.';
                END IF;
            END");
    }

    /** Remove o identificador público e sua proteção. */
    public function down()
    {
        $this->db->query('DROP TRIGGER IF EXISTS impedir_alteracao_hash_processo');
        $this->forge->dropColumn('processos', 'hash_publico');
    }
}
