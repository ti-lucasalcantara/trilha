<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use App\Libraries\Organizacao\GestaoEquipe;
use CodeIgniter\HTTP\ResponseInterface;

/** Abre a prévia administrativa da organização demonstrativa. */
class Configuracoes extends BaseController
{
    /** Reaproveita a navegação e a identidade visual da área da organização. */
    public function exibir(string $identificadorOrganizacao): ResponseInterface
    {
        helper(['url', 'form']);

        $dados = [
            'tituloPagina' => 'Estrutura e equipe',
            'paginaAtiva' => 'configuracoes',
            'identificadorOrganizacao' => $identificadorOrganizacao,
            'nomeOrganizacao' => 'Empresa Horizonte',
            'nomeUsuario' => 'Lucas',
        ];
        if ($identificadorOrganizacao !== '1') {
            $banco = db_connect();
            $sessao = session()->get(SimulacaoEmpresa::SESSAO);
            $simulada = ($sessao['hash'] ?? null) === $identificadorOrganizacao;
            if ($simulada) {
                $contexto = SimulacaoEmpresa::contexto($this->request, $identificadorOrganizacao, true);
                $empresa = (new \App\Libraries\Admin\Cadastros($banco))->obter('clientes', $contexto['empresa']);
                $contexto = ['empresa' => (string) $empresa['id'], 'organizacao' => $empresa['nome'], 'usuario' => 'Administrador da organização'];
            } else {
                $contexto = (new AcessoOrganizacao($banco))->contexto($identificadorOrganizacao, true);
            }
            $base = site_url('organizacao/' . $identificadorOrganizacao);
            $dados = array_replace($dados, [
                'nomeOrganizacao' => $contexto['organizacao'], 'nomeUsuario' => $contexto['usuario'],
                'contextoEmpresaReal' => !$simulada, 'modoSimulacao' => $simulada, 'administradorSimulado' => true,
                'consultasPermitidas' => [], 'empresaSimulada' => $contexto['empresa'],
                'contextoTrabalho' => (new ContextoSetor($banco, (string) $contexto['empresa'], null))->obter(),
                'configuracoesReais' => (new GestaoEquipe($banco, (string) $contexto['empresa']))->dadosConfiguracoes() + [
                    'estrutura' => $base . ($simulada ? '/simulacao/estrutura' : '/estrutura'),
                    'equipe' => $simulada ? null : $base . '/equipe',
                    'perfis' => $simulada ? null : $base . '/perfis',
                    'csrfNome' => csrf_token(), 'csrfValor' => csrf_hash(),
                ],
            ]);
        }
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/configuracoes', $dados));
    }
}
