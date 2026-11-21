<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Organizacao\AcessoOrganizacao;
use App\Libraries\Organizacao\ConsultaProcessos;
use App\Libraries\Organizacao\ContextoSetor;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;
use DateTimeImmutable;
use DateTimeZone;

class CaixaTrabalho extends BaseController
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
        $administrador = (bool) $contexto['fundador'];

        $modulo = $this->request->getGet('modulo');
        $categorias = ['processos', 'documentos', 'assinaturas', 'pastas', 'equipe', 'perfis', 'unidades', 'setores'];
        if (!in_array($modulo, [...$categorias, 'estrutura'], true)) $modulo = 'dashboard';
        $consultas = $administrador ? [] : $acesso->consultas($contexto);
        if (!$administrador && $modulo !== 'dashboard' && !in_array($modulo, $consultas, true)) throw PageNotFoundException::forPageNotFound();
        if (in_array($modulo, ['processos', 'pastas'], true)) {
            return redirect()->to(site_url('organizacao/' . $hash . '/' . $modulo));
        }
        if (in_array($modulo, ['estrutura', 'equipe', 'perfis'], true)) {
            return redirect()->to(site_url('organizacao/' . $hash . '/configuracoes/' . $modulo));
        }

        $contextoTrabalho = (new ContextoSetor($banco, $empresaId, $administrador ? null : $vinculo))->obter();
        $layout = [
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
        ];
        $consulta = new ConsultaEmpresa($banco, $empresaId);
        $pagina = $this->request->getGet('pagina');
        $pagina = is_string($pagina) ? (int) $pagina : 1;
        $setor = $contextoTrabalho['setor']['id'] ?? '';
        $resposta = $this->response->setHeader('Cache-Control', 'no-store, private');

        if ($modulo !== 'dashboard') {
            $inventario = $consulta->inventario($modulo, $pagina, $setor, $vinculo);
            return $resposta->setBody(view('organizacao/inventario', $layout + [
                'tituloPagina' => $inventario['categorias'][$modulo] . ($simulado ? ' · Simulação' : ''),
                'inventario' => $inventario,
            ]));
        }

        $hoje = new DateTimeImmutable('today', new DateTimeZone($empresa['fuso_horario']));
        $processos = (new ConsultaProcessos($banco, $empresaId, $setor))->listarParaCaixa($vinculo);
        return $resposta->setBody(view('organizacao/caixa_trabalho', $layout + [
            'tituloPagina' => 'Caixa de trabalho' . ($simulado ? ' · Simulação' : ''),
            'acessoVinculo' => $administrador ? null : $consulta->contextoUsuario($vinculo),
        ] + $this->resumir($processos, $hoje, $pagina)));
    }

    private function resumir(array $processos, DateTimeImmutable $hoje, int $pagina): array
    {
        $dataHoje = $hoje->format('Y-m-d');
        $limite = $hoje->modify('+7 days')->format('Y-m-d');
        $grupos = ['todos' => [], 'vencidos' => [], 'proximos' => []];

        foreach ($processos as $processo) {
            $prazo = substr($processo['prazo'] ?? '', 0, 10);
            $grupo = match (true) {
                $prazo === '' => null,
                $prazo < $dataHoje => 'vencidos',
                $prazo <= $limite => 'proximos',
                default => null,
            };
            $processo['prazo'] = $prazo;
            $processo['grupoPrazo'] = $grupo;
            $grupos['todos'][] = $processo;
            if ($grupo !== null) $grupos[$grupo][] = $processo;
        }

        $filtro = $this->request->getGet('filtro');
        $filtro = is_string($filtro) && isset($grupos[$filtro]) ? $filtro : 'todos';
        $selecionados = $grupos[$filtro];
        if ($filtro !== 'todos') {
            usort($selecionados, static fn (array $a, array $b): int => $a['prazo'] <=> $b['prazo']);
        }
        $porPagina = 8;
        $paginas = max(1, (int) ceil(count($selecionados) / $porPagina));
        $pagina = max(1, min($pagina, $paginas));

        return [
            'indicadores' => array_map('count', $grupos),
            'processos' => array_slice($selecionados, ($pagina - 1) * $porPagina, $porPagina),
            'filtro' => $filtro,
            'pagina' => $pagina,
            'paginas' => $paginas,
            'dataHoje' => $dataHoje,
        ];
    }
}
