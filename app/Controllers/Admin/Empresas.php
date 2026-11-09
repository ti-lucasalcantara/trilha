<?php

namespace App\Controllers\Admin;

use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
use App\Libraries\Admin\SimulacaoEmpresa;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Reúne os dados e o contexto de acesso de um cliente. */
class Empresas extends BaseAdmin
{
    /** Exibe o inventário e os indicadores reais da organização. */
    public function exibir(string $id): ResponseInterface
    {
        $banco = $this->conexaoAdmin();
        $empresa = (new Cadastros($banco))->obter('clientes', $id);
        $consulta = new ConsultaEmpresa($banco, $id);
        $categoria = $this->request->getGet('categoria');
        $pagina = $this->request->getGet('pagina');

        return $this->renderizar('empresa', [
            'tituloPagina' => $empresa['nome'],
            'descricaoPagina' => 'Inventário da empresa, armazenamento e visualização de vínculos.',
            'paginaAtiva' => 'clientes',
            'empresa' => $empresa,
            'armazenamento' => $consulta->armazenamento((string) $empresa['plano_versao_id']),
            'usuarios' => $consulta->usuarios(),
        ] + $consulta->inventario(is_string($categoria) ? $categoria : '', is_string($pagina) ? (int) $pagina : 1));
    }

    /** Inicia a prévia no dashboard sem assumir a identidade de uma conta. */
    public function simular(string $id): ResponseInterface
    {
        $banco = $this->conexaoAdmin();
        $empresa = (new Cadastros($banco))->obter('clientes', $id);
        $selecionado = $this->request->getPost('usuario');
        if (!is_string($selecionado)) {
            throw PageNotFoundException::forPageNotFound();
        }
        if ($selecionado !== 'administrador') {
            $usuarios = array_column((new ConsultaEmpresa($banco, $id))->usuarios(), null, 'id');
            if (!isset($usuarios[$selecionado])) throw PageNotFoundException::forPageNotFound();
        }

        $hash = $empresa['hash_publico'];
        session()->set(SimulacaoEmpresa::SESSAO, ['empresa' => $id, 'usuario' => $selecionado, 'hash' => $hash]);
        return redirect()->to(site_url('organizacao/' . $hash . '/dashboard'))->withCookies();
    }

    /** Encerra o modo de simulação antes de voltar à ficha. */
    public function encerrarSimulacao(string $id): ResponseInterface
    {
        $this->conexaoAdmin();
        $simulacao = session()->get(SimulacaoEmpresa::SESSAO);
        if (($simulacao['empresa'] ?? null) === $id) session()->remove(SimulacaoEmpresa::SESSAO);
        return redirect()->to(site_url('admin/clientes/' . $id))->withCookies();
    }
}
