<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

class Painel extends BaseController
{
    public function exibir(string $hash): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $acesso = new AcessoOrganizacao($banco);
        $contexto = $acesso->contexto($hash);
        $empresaId = (string) $contexto['empresa'];
        $simulado = $contexto['simulacao'];
        $vinculo = $contexto['vinculo'] !== null ? (string) $contexto['vinculo'] : null;
        $empresa = (new Cadastros($banco))->obter('clientes', $empresaId);
        if (!hash_equals($empresa['hash_publico'], $hash)) throw PageNotFoundException::forPageNotFound();
        $consulta = new ConsultaEmpresa($banco, $empresaId);
        $administrador = (bool) $contexto['fundador'];

        $modulo = $this->request->getGet('modulo');
        $categorias = ['processos', 'documentos', 'assinaturas', 'pastas', 'equipe', 'perfis', 'unidades', 'setores'];
        if (!in_array($modulo, [...$categorias, 'estrutura'], true)) $modulo = 'dashboard';
        $consultas = $administrador ? [] : $acesso->consultas($contexto);
        if (!$administrador && $modulo !== 'dashboard' && !in_array($modulo, $consultas, true)) throw PageNotFoundException::forPageNotFound();
        if ($modulo === 'processos') {
            return redirect()->to(site_url('organizacao/' . $hash . '/processos'));
        }
        if (in_array($modulo, ['estrutura', 'equipe', 'perfis'], true)) {
            return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes/' . $modulo));
        }

        $contextoTrabalho = (new ContextoSetor($banco, $empresaId, $administrador ? null : $vinculo))->obter();
        $dados = $administrador ? [] : $consulta->contextoUsuario($vinculo);
        if ($administrador || $modulo !== 'dashboard') {
            $pagina = $this->request->getGet('pagina');
            $dados = ['inventario' => $consulta->inventario($modulo === 'dashboard' ? 'processos' : $modulo,
                is_string($pagina) ? (int) $pagina : 1, $contextoTrabalho['setor']['id'] ?? '', $vinculo)];
        }
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/painel', [
            'tituloPagina' => $simulado ? 'Caixa de trabalho · Simulação' : 'Caixa de trabalho',
            'paginaAtiva' => $modulo,
            'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $empresa['nome'],
            'nomeUsuario' => $contexto['usuario'],
            'usuarioTemAvatar' => !empty($contexto['tem_avatar']),
            'modoSimulacao' => $simulado,
            'simulacaoSemVinculo' => $vinculo === null, 'vinculoPreferencias' => $vinculo,
            'empresaSimulada' => $empresa['id'],
            'administradorSimulado' => $administrador,
            'consultasPermitidas' => $consultas,
            'contextoTrabalho' => $contextoTrabalho,
        ] + $dados));
    }
}
