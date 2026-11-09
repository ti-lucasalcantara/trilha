<?php

namespace App\Libraries\Organizacao;

use CodeIgniter\Database\BaseConnection;
use DomainException;

/** Controla a atribuição de perfis e a proteção do administrador fundador. */
class PerfisOrganizacao
{
    public function __construct(private readonly BaseConnection $banco)
    {
    }

    /** Lista os perfis da organização para seleção no cadastro. */
    public function listar(string $empresa): array
    {
        return $this->banco->table('perfis')->where('organizacao_id', $empresa)->orderBy('nome')->get()->getResultArray();
    }

    /** Obtém a referência protegida criada no primeiro vínculo. */
    public function administrador(string $empresa): ?array
    {
        return $this->banco->table('administradores_organizacao')->where('organizacao_id', $empresa)->get()->getRowArray();
    }

    /** Atribui um perfil organizacional e cria o Administrador se ainda não houver perfis. */
    public function atribuir(string $empresa, string $vinculo, string $perfil, string $statusUsuario, string $agora): void
    {
        $fundador = $this->banco->query('SELECT * FROM administradores_organizacao WHERE organizacao_id = ? FOR UPDATE', [$empresa])->getRowArray();
        if ($fundador && (string) $fundador['vinculo_id'] === $vinculo) {
            if ($statusUsuario !== 'ativo' || ($perfil !== '' && $perfil !== (string) $fundador['perfil_id'])) {
                throw new DomainException('O perfil e o vínculo do Administrador fundador são protegidos.');
            }
            return;
        }

        // O chamador bloqueia a organização para serializar o primeiro cadastro.
        $perfis = $this->banco->query('SELECT id, status FROM perfis WHERE organizacao_id = ? FOR UPDATE', [$empresa])->getResultArray();
        if (!$perfis) {
            if ($statusUsuario !== 'ativo') throw new DomainException('O primeiro Administrador deve estar ativo.');
            $this->banco->table('perfis')->insert([
                'organizacao_id' => $empresa, 'nome' => 'Administrador', 'status' => 'ativo', 'criado_em' => $agora,
            ]);
            $perfil = (string) $this->banco->insertID();
            $this->banco->table('administradores_organizacao')->insert([
                'organizacao_id' => $empresa, 'vinculo_id' => $vinculo, 'perfil_id' => $perfil,
            ]);
        } else {
            $opcoes = array_column($perfis, 'status', 'id');
            if ($perfil === '' || ($opcoes[$perfil] ?? null) !== 'ativo' || ($fundador && (string) $fundador['perfil_id'] === $perfil)) {
                throw new DomainException('Selecione um perfil ativo da organização. O perfil Administrador fundador é reservado.');
            }
        }

        $escopo = ['organizacao_id' => $empresa, 'vinculo_id' => $vinculo, 'unidade_id' => null, 'setor_id' => null];
        $consulta = $this->banco->table('atribuicoes_perfil');
        $atuais = $consulta->where($escopo)->get()->getResultArray();
        if (count($atuais) === 1 && (string) $atuais[0]['perfil_id'] === $perfil) return;
        // Substitui a atribuição geral, preservando escopos por unidade ou setor.
        if ($atuais) $consulta->where($escopo)->delete();
        $consulta->insert($escopo + ['perfil_id' => $perfil, 'criado_em' => $agora]);
    }
}
