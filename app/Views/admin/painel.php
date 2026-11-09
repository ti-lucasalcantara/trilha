<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<!-- Os indicadores levam às páginas responsáveis por cada cadastro. -->
<div class="indicadores-admin" aria-label="Indicadores da plataforma">
    <a href="<?= esc(site_url('admin/clientes'), 'attr') ?>"><span>Clientes</span><strong><?= $totalClientes ?></strong><i class="fa-regular fa-building" aria-hidden="true"></i></a>
    <a href="<?= esc(site_url('admin/planos'), 'attr') ?>"><span>Planos</span><strong><?= $totalPlanos ?></strong><i class="fa-solid fa-layer-group" aria-hidden="true"></i></a>
    <a href="<?= esc(site_url('admin/usuarios'), 'attr') ?>"><span>Usuários</span><strong><?= $totalUsuarios ?></strong><i class="fa-solid fa-users" aria-hidden="true"></i></a>
</div>
<section class="cartao-admin" aria-labelledby="titulo-caixa-painel-admin">
    <header class="cabecalho-lista-admin">
        <div><h2 id="titulo-caixa-painel-admin">Financeiro</h2><p>Consulte recebimentos, despesas e cobranças na simulação de caixa.</p></div>
        <a class="botao-admin secundario" href="<?= esc(site_url('admin/financeiro'), 'attr') ?>"><i class="fa-solid fa-wallet" aria-hidden="true"></i> Abrir financeiro</a>
    </header>
</section>
<?= $this->endSection() ?>
