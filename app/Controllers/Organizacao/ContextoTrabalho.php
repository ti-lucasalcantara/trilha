<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Aplica a escolha de unidade e setor à sessão de trabalho. */
class ContextoTrabalho extends BaseController
{
    /** Confere o vínculo e o destino antes de voltar à tela de origem. */
    public function selecionar(string $hash): ResponseInterface
    {
        helper('url');
        $banco = db_connect();
        $simulado = (session()->get(SimulacaoEmpresa::SESSAO)['hash'] ?? null) === $hash;
        $contexto = $simulado ? SimulacaoEmpresa::contexto($this->request, $hash) : (new AcessoOrganizacao($banco))->contexto($hash);
        $empresa = (string) $contexto['empresa'];
        if ($simulado && $contexto['usuario'] !== 'administrador') {
            $usuarios = array_column((new ConsultaEmpresa($banco, $empresa))->usuarios(), null, 'id');
            $usuario = $usuarios[$contexto['usuario']] ?? null;
            if (!$usuario) throw PageNotFoundException::forPageNotFound();
            $vinculo = (string) $usuario['vinculo_id'];
        } else {
            $vinculo = !$simulado && !$contexto['fundador'] ? (string) $contexto['vinculo'] : null;
        }

        $unidade = $this->request->getPost('unidade');
        $setor = $this->request->getPost('setor');
        $pagina = $this->request->getPost('pagina');
        $caminho = match ($pagina) {
            'processos' => 'processos',
            'configuracoes' => 'configuracoes',
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
