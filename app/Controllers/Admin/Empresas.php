<?php

namespace App\Controllers\Admin;

use App\Libraries\Admin\Cadastros;
use App\Libraries\Admin\ConsultaEmpresa;
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

    /** Mostra o contexto de um usuário vinculado sem assumir sua sessão. */
    public function simular(string $id): ResponseInterface
    {
        $banco = $this->conexaoAdmin();
        $empresa = (new Cadastros($banco))->obter('clientes', $id);
        $consulta = new ConsultaEmpresa($banco, $id);
        $selecionado = $this->request->getGet('usuario');
        $usuarios = array_column($consulta->usuarios(), null, 'id');
        if (!is_string($selecionado) || !isset($usuarios[$selecionado])) {
            throw PageNotFoundException::forPageNotFound();
        }

        $usuario = $usuarios[$selecionado];
        return $this->renderizar('simulacao', [
            'tituloPagina' => 'Simulação de acesso',
            'descricaoPagina' => $empresa['nome'],
            'paginaAtiva' => 'clientes',
            'indicacaoPagina' => 'Simulação · Somente leitura',
            'empresa' => $empresa, 'usuario' => $usuario,
        ] + $consulta->contextoUsuario((string) $usuario['vinculo_id']));
    }
}
