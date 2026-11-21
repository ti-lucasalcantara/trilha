<?= $this->extend('layout/principal') ?>

<?= $this->section('conteudo') ?>
<div class="titulo-acoes">
    <div>
        <h1><?= esc($inventario['categorias'][$paginaAtiva]) ?><span class="ponto-titulo">.</span></h1>
        <p class="descricao-pagina"><?= $modoSimulacao ? 'Prévia de ' : '' ?><?= esc($nomeOrganizacao) ?> · <?= esc($nomeUsuario) ?></p>
    </div>
</div>

<section class="painel-processos" aria-labelledby="titulo-inventario">
    <div class="cabecalho-painel">
        <div>
            <h2 id="titulo-inventario"><?= esc($inventario['categorias'][$inventario['categoria']]) ?></h2>
            <p><?= (int) $inventario['total'] ?> registro(s) · Consulta ao banco da organização</p>
        </div>
    </div>
    <?php if (!$inventario['registros']): ?>
        <p class="vazio-simulacao">Nenhum registro nesta seção.</p>
    <?php else: ?>
        <div class="tabela-responsiva" role="region" aria-label="Registros da organização" tabindex="0">
            <table class="tabela-processos tabela-simulacao">
                <thead><tr><?php foreach ($inventario['colunas'] as $rotulo): ?><th scope="col"><?= esc($rotulo) ?></th><?php endforeach ?></tr></thead>
                <tbody>
                    <?php foreach ($inventario['registros'] as $registro): ?>
                        <tr><?php foreach ($inventario['colunas'] as $campo => $rotulo): ?><td><?= esc((string) ($registro[$campo] ?? '—')) ?></td><?php endforeach ?></tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <?php if ($inventario['paginas'] > 1): ?>
            <nav class="paginacao-simulacao" aria-label="Paginação dos registros">
                <?php foreach (['Anterior' => $inventario['pagina'] - 1, 'Próxima' => $inventario['pagina'] + 1] as $rotulo => $pagina): ?>
                    <?php if ($pagina > 0 && $pagina <= $inventario['paginas']): ?>
                        <a class="btn" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard') . '?' . http_build_query(['modulo' => $paginaAtiva, 'pagina' => $pagina]), 'attr') ?>"><?= esc($rotulo) ?></a>
                    <?php endif ?>
                <?php endforeach ?>
            </nav>
        <?php endif ?>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
