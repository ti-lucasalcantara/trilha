<?php

namespace App\Controllers\Admin;

use CodeIgniter\HTTP\ResponseInterface;
use DateTimeImmutable;

/** Apresenta o fluxo de caixa demonstrativo, separado das cobranças reais. */
class Financeiro extends BaseAdmin
{
    /** Monta exemplos de entradas e saídas em duas competências. */
    public function exibir(): ResponseInterface
    {
        $this->conexaoAdmin();
        $hoje = new DateTimeImmutable('today');
        $mes = $hoje->format('Y-m');
        $anterior = $hoje->modify('first day of last month')->format('Y-m');

        return $this->renderizar('financeiro', [
            'tituloPagina' => 'Financeiro',
            'descricaoPagina' => 'Acompanhe entradas, despesas e cobranças em aberto por competência.',
            'paginaAtiva' => 'financeiro',
            'indicacaoPagina' => 'Simulação financeira · Dados fictícios',
            'hoje' => $hoje->format('Y-m-d'),
            'lancamentos' => [
                ['descricao' => 'Empresa Horizonte · Mensalidade', 'tipo' => 'entrada', 'centavos' => 39900, 'vencimento' => $mes . '-05', 'pago' => true],
                ['descricao' => 'Norte Operações · Mensalidade', 'tipo' => 'entrada', 'centavos' => 79900, 'vencimento' => $mes . '-06', 'pago' => false],
                ['descricao' => 'Alvorada Serviços · Mensalidade', 'tipo' => 'entrada', 'centavos' => 19900, 'vencimento' => $mes . '-22', 'pago' => false],
                ['descricao' => 'Infraestrutura e armazenamento', 'tipo' => 'saida', 'centavos' => 18000, 'vencimento' => $mes . '-04', 'pago' => true],
                ['descricao' => 'Serviços operacionais', 'tipo' => 'saida', 'centavos' => 9500, 'vencimento' => $mes . '-25', 'pago' => false],
                ['descricao' => 'Empresa Horizonte · Mensalidade', 'tipo' => 'entrada', 'centavos' => 39900, 'vencimento' => $anterior . '-05', 'pago' => true],
                ['descricao' => 'Norte Operações · Mensalidade', 'tipo' => 'entrada', 'centavos' => 79900, 'vencimento' => $anterior . '-06', 'pago' => false],
                ['descricao' => 'Infraestrutura e armazenamento', 'tipo' => 'saida', 'centavos' => 17500, 'vencimento' => $anterior . '-04', 'pago' => true],
            ],
        ]);
    }
}
