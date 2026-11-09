<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<!-- A marcação de pagamentos altera somente a simulação no navegador. -->
<div id="financeiro-admin" data-hoje="<?= esc($hoje, 'attr') ?>">
    <div class="barra-consulta-admin">
        <label for="competencia-admin">Competência
            <select id="competencia-admin">
                <?php foreach (array_unique(array_map(static fn ($item) => substr($item['vencimento'], 0, 7), $lancamentos)) as $mes): ?>
                    <option value="<?= esc($mes, 'attr') ?>"><?= esc(substr($mes, 5, 2) . '/' . substr($mes, 0, 4)) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <label for="situacao-financeiro-admin">Situação
            <select id="situacao-financeiro-admin">
                <option value="">Todas</option><option value="pago">Pago</option><option value="aberto">Em aberto</option><option value="vencido">Vencido</option>
            </select>
        </label>
    </div>
    <div class="metricas-admin" aria-label="Resumo do caixa simulado">
        <article><span>Recebido</span><strong id="caixa-recebido"></strong></article>
        <article><span>Despesas pagas</span><strong id="caixa-despesas"></strong></article>
        <article><span>Saldo realizado</span><strong id="caixa-saldo"></strong></article>
        <article><span>A receber</span><strong id="caixa-receber"></strong><small id="caixa-vencido"></small></article>
    </div>
    <section class="cartao-admin" aria-label="Lançamentos simulados">
        <header><h2>Lançamentos</h2><p>O filtro de situação afeta a lista. Os totais consideram toda a competência.</p></header>
        <div class="tabela-admin" tabindex="0" role="region" aria-label="Entradas e saídas simuladas">
            <table>
                <thead><tr><th scope="col">Descrição</th><th scope="col">Vencimento</th><th scope="col">Valor</th><th scope="col">Situação</th><th scope="col">Ação</th></tr></thead>
                <tbody>
                    <?php foreach ($lancamentos as $item): ?>
                        <tr data-lancamento-admin data-tipo="<?= esc($item['tipo'], 'attr') ?>" data-centavos="<?= (int) $item['centavos'] ?>" data-vencimento="<?= esc($item['vencimento'], 'attr') ?>" data-pago="<?= $item['pago'] ? 'true' : 'false' ?>">
                            <th scope="row"><?= esc($item['descricao']) ?><small><?= $item['tipo'] === 'entrada' ? 'Entrada' : 'Despesa' ?></small></th>
                            <td><?= esc(date('d/m/Y', strtotime($item['vencimento']))) ?></td>
                            <td>R$ <?= esc(number_format($item['centavos'] / 100, 2, ',', '.')) ?></td>
                            <td><span class="situacao-admin" data-situacao-financeiro></span></td>
                            <td><button type="button" class="botao-admin secundario" data-alterar-pagamento></button></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <p id="financeiro-vazio-admin" class="estado-lista-admin" hidden>Nenhum lançamento corresponde ao filtro.</p>
    </section>
    <p class="nota-admin" role="status" id="aviso-financeiro-admin">Os exemplos voltam ao estado inicial ao recarregar.</p>
</div>
<?= $this->endSection() ?>
