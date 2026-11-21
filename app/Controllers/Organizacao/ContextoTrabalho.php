<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\HTTP\ResponseInterface;

/** Aplica a escolha de unidade e setor à sessão de trabalho. */
class ContextoTrabalho extends BaseController
{
    /** Confere o vínculo e o destino antes de voltar à tela de origem. */
    public function selecionar(string $hash): ResponseInterface
    {
        helper('url');
        $banco = db_connect();
        $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
        $empresa = (string) $contexto['empresa'];
        $vinculo = $contexto['fundador'] ? null : (string) $contexto['vinculo'];

        $unidade = $this->request->getPost('unidade');
        $setor = $this->request->getPost('setor');
        $pagina = $this->request->getPost('pagina');
        $caminho = match ($pagina) {
            'processos' => 'processos',
            'configuracoes' => 'configuracoes',
            'configuracoes/estrutura', 'configuracoes/equipe', 'configuracoes/perfis',
            'configuracoes/tipos-processo', 'configuracoes/situacoes-processo' => $pagina,
            'perfil' => 'meu-perfil',
            'documentos', 'assinaturas', 'pastas', 'unidades', 'setores' => 'dashboard?modulo=' . $pagina,
            default => 'dashboard',
        };
        $voltar = redirect()->to(site_url('organizacao/' . $hash . '/' . $caminho))->withCookies();
        if (!is_string($unidade) || !is_string($setor) || !(new ContextoSetor($banco, $empresa, $vinculo))->selecionar($unidade, $setor)) {
            return $voltar->with('erroContextoTrabalho', 'O setor selecionado não está disponível para esta unidade.');
        }
        return $voltar;
    }
}
