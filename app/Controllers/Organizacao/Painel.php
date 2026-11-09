<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Admin\SimulacaoEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Apresenta a área interna com dados fictícios, sem consultar o banco. */
class Painel extends BaseController
{
    /** Abre a área demonstrativa ou uma simulação administrativa válida. */
    public function exibir(string $identificadorOrganizacao): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            return $this->exibirEmpresa($identificadorOrganizacao);
        }

        helper('url');
        $processos = $this->obterProcessosDemonstrativos();
        $quantidades = ['todos' => count($processos), 'receber' => 0, 'providencia' => 0, 'assinar' => 0];
        foreach ($processos as $processo) {
            $quantidades[$processo['categoria']]++;
        }

        return $this->response
            ->setHeader('Cache-Control', 'no-store, private')
            ->setBody(view('organizacao/dashboard', [
                'tituloPagina' => 'Caixa de trabalho',
                'paginaAtiva' => 'dashboard',
                'identificadorOrganizacao' => $identificadorOrganizacao,
                'nomeOrganizacao' => 'Empresa Horizonte',
                'nomeUsuario' => 'Lucas',
                'processos' => $processos,
                'quantidades' => $quantidades,
            ]));
    }

    /** Apresenta dados autorizados para a prévia, sem conceder uma sessão de usuário. */
    private function exibirEmpresa(string $hash): ResponseInterface
    {
        helper(['url', 'form']);
        $banco = db_connect();
        $sessaoSimulada = session()->get(SimulacaoEmpresa::SESSAO);
        $simulado = ($sessaoSimulada['hash'] ?? null) === $hash;
        if ($simulado) {
            $contexto = SimulacaoEmpresa::contexto($this->request, $hash);
            $empresaId = $contexto['empresa'];
        } else {
            $contexto = (new AcessoOrganizacao($banco))->contexto($hash);
            $empresaId = (string) $contexto['empresa'];
        }
        $empresa = (new Cadastros($banco))->obter('clientes', $empresaId);
        if (!hash_equals($empresa['hash_publico'], $hash)) throw PageNotFoundException::forPageNotFound();
        $consulta = new ConsultaEmpresa($banco, $empresaId);
        $administrador = $simulado ? $contexto['usuario'] === 'administrador' : (bool) $contexto['fundador'];
        $usuario = $simulado
            ? ($administrador ? ['nome' => 'Administrador da organização'] : (array_column($consulta->usuarios(), null, 'id')[$contexto['usuario']] ?? null))
            : ['nome' => $contexto['usuario'], 'vinculo_id' => $contexto['vinculo']];
        if (!$usuario) throw PageNotFoundException::forPageNotFound();

        $modulo = $this->request->getGet('modulo');
        $categorias = ['processos', 'documentos', 'assinaturas', 'pastas', 'equipe', 'perfis', 'unidades', 'setores'];
        if (!in_array($modulo, [...$categorias, 'estrutura'], true)) $modulo = 'dashboard';
        $consultas = !$simulado && !$administrador ? (new AcessoOrganizacao($banco))->consultas($contexto) : [];
        if (!$administrador && $modulo !== 'dashboard' && !in_array($modulo, $consultas, true)) throw PageNotFoundException::forPageNotFound();
        if ($modulo === 'processos') {
            return redirect()->to(site_url('organizacao/' . $hash . '/processos'));
        }
        $abasConfiguracoes = ['estrutura' => 'estrutura', 'equipe' => 'equipe', 'perfis' => 'permissoes'];
        if (isset($abasConfiguracoes[$modulo])) {
            return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes') . '#' . $abasConfiguracoes[$modulo]);
        }

        $contextoTrabalho = (new ContextoSetor($banco, $empresaId, $administrador ? null : (string) $usuario['vinculo_id']))->obter();
        $dados = $administrador ? [] : $consulta->contextoUsuario((string) $usuario['vinculo_id']);
        if ($administrador || $modulo !== 'dashboard') {
            $pagina = $this->request->getGet('pagina');
            $dados = ['inventario' => $consulta->inventario($modulo === 'dashboard' ? 'processos' : $modulo, is_string($pagina) ? (int) $pagina : 1, $contextoTrabalho['setor']['id'] ?? '')];
        }
        return $this->response->setHeader('Cache-Control', 'no-store, private')->setBody(view('organizacao/painel_simulacao', [
            'tituloPagina' => $simulado ? 'Caixa de trabalho · Simulação' : 'Caixa de trabalho',
            'paginaAtiva' => $modulo,
            'identificadorOrganizacao' => $hash,
            'nomeOrganizacao' => $empresa['nome'],
            'nomeUsuario' => $usuario['nome'],
            'modoSimulacao' => $simulado,
            'contextoEmpresaReal' => !$simulado,
            'empresaSimulada' => $empresa['id'],
            'administradorSimulado' => $administrador,
            'consultasPermitidas' => $consultas,
            'contextoTrabalho' => $contextoTrabalho,
        ] + $dados));
    }

    /** Fornece exemplos de conteúdo para avaliar a interface, sem definir regras reais. */
    private function obterProcessosDemonstrativos(): array
    {
        return [
            ['id' => 1, 'numero' => 'COMPRAS-0008/2026', 'assunto' => 'Aquisição de equipamentos para a equipe', 'tipo' => 'Compras', 'origem' => 'Financeiro', 'responsavel' => 'Mariana Costa', 'iniciais' => 'MC', 'categoria' => 'receber', 'situacao' => 'Aguardando recebimento', 'prazo' => 'Hoje', 'destaque' => 'laranja', 'documentos' => 3, 'etiqueta' => 'Equipamentos', 'atualizacao' => 'Há 15 min'],
            ['id' => 2, 'numero' => 'ADM-0012/2026', 'assunto' => 'Admissão de novo colaborador', 'tipo' => 'Admissão', 'origem' => 'Recursos Humanos', 'responsavel' => 'Ana Souza', 'iniciais' => 'AS', 'categoria' => 'assinar', 'situacao' => 'Assinatura pendente', 'prazo' => 'Hoje', 'destaque' => 'roxo', 'documentos' => 4, 'etiqueta' => 'Pessoas', 'atualizacao' => 'Há 40 min'],
            ['id' => 3, 'numero' => 'COMPRAS-0007/2026', 'assunto' => 'Renovação de licenças de software', 'tipo' => 'Compras', 'origem' => 'Tecnologia', 'responsavel' => 'Pedro Lima', 'iniciais' => 'PL', 'categoria' => 'providencia', 'situacao' => 'Em análise', 'prazo' => 'Amanhã', 'destaque' => 'azul', 'documentos' => 2, 'etiqueta' => 'Tecnologia', 'atualizacao' => 'Há 1 h'],
            ['id' => 4, 'numero' => 'CONTRATOS-0003/2026', 'assunto' => 'Revisão do contrato de manutenção', 'tipo' => 'Contratos', 'origem' => 'Jurídico', 'responsavel' => 'Beatriz Alves', 'iniciais' => 'BA', 'categoria' => 'providencia', 'situacao' => 'Em análise', 'prazo' => 'Em 3 dias', 'destaque' => 'azul', 'documentos' => 5, 'etiqueta' => 'Manutenção', 'atualizacao' => 'Há 2 h'],
            ['id' => 5, 'numero' => 'COMPRAS-0009/2026', 'assunto' => 'Materiais para a nova unidade', 'tipo' => 'Compras', 'origem' => 'Operações', 'responsavel' => 'Rafael Santos', 'iniciais' => 'RS', 'categoria' => 'receber', 'situacao' => 'Aguardando recebimento', 'prazo' => 'Em 4 dias', 'destaque' => 'laranja', 'documentos' => 2, 'etiqueta' => 'Nova unidade', 'atualizacao' => 'Há 3 h'],
            ['id' => 6, 'numero' => 'ADM-0011/2026', 'assunto' => 'Atualização de documentos admissionais', 'tipo' => 'Admissão', 'origem' => 'Recursos Humanos', 'responsavel' => 'Ana Souza', 'iniciais' => 'AS', 'categoria' => 'providencia', 'situacao' => 'Em análise', 'prazo' => 'Em 5 dias', 'destaque' => 'azul', 'documentos' => 3, 'etiqueta' => 'Pessoas', 'atualizacao' => 'Ontem'],
        ];
    }
}
