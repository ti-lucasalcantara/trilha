<?php

namespace App\Controllers\Organizacao;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\ResponseInterface;

/** Apresenta a área interna com dados fictícios, sem consultar o banco. */
class Painel extends BaseController
{
    /** Exibe somente a organização demonstrativa até existir identificação real. */
    public function exibir(string $identificadorOrganizacao): ResponseInterface
    {
        if ($identificadorOrganizacao !== '1') {
            throw PageNotFoundException::forPageNotFound();
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
