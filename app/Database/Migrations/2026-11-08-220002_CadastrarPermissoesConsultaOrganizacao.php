<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/** Disponibiliza no catálogo as consultas já integradas à área da organização. */
class CadastrarPermissoesConsultaOrganizacao extends Migration
{
    private const PERMISSOES = [
        'organizacao.unidades.consultar' => ['Estrutura', 'Consultar unidades da organização'],
        'organizacao.setores.consultar' => ['Estrutura', 'Consultar setores da organização'],
    ];

    /** Insere os códigos que ainda não existem. */
    public function up()
    {
        foreach (self::PERMISSOES as $codigo => [$modulo, $descricao]) {
            if (!$this->db->table('permissoes')->where('codigo', $codigo)->countAllResults()) {
                $this->db->table('permissoes')->insert(compact('codigo', 'modulo', 'descricao') + ['criado_em' => date('Y-m-d H:i:s')]);
            }
        }
    }

    /** Mantém os códigos já usados em perfis ao reverter a migration. */
    public function down()
    {
        $this->db->query("DELETE p FROM permissoes p LEFT JOIN perfil_permissoes pp ON pp.permissao_id = p.id
            WHERE pp.id IS NULL AND p.codigo IN ('organizacao.unidades.consultar', 'organizacao.setores.consultar')");
    }
}
