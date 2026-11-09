<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processos.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/processos.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<!-- A consulta combina a árvore de navegação com a lista paginada. -->
<div class="estrutura-explorador">
    <?= $this->include('organizacao/processos/explorador') ?>
    <section class="conteudo-explorador" aria-labelledby="titulo-consulta">
        <header class="titulo-acoes cabecalho-consulta">
            <h1 id="titulo-consulta">Processos</h1>
            <?php if (!empty($consultaReal)): ?>
                <a class="btn btn-primary botao-novo" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/novo'), 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</a>
            <?php else: ?>
                <button type="button" class="btn btn-primary botao-novo" data-aviso="A tela de abertura será construída a seguir. Nenhum processo foi criado."><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</button>
            <?php endif ?>
        </header>
        <?php if (!empty($pastaRealCriada)): ?>
            <p class="alert alert-success" role="status"><?= esc($pastaRealCriada) ?></p>
        <?php elseif (!empty($consultaReal) && ($erroPastaReal = session()->getFlashdata('erroPastaReal'))): ?>
            <p class="alert alert-danger" role="alert"><?= esc($erroPastaReal) ?></p>
        <?php endif ?>
        <p id="caminho-selecao" class="caminho-consulta" hidden></p>
        <?= $this->include('organizacao/processos/filtros') ?>
        <?= $this->include('organizacao/processos/listagem') ?>
    </section>
    <?php if (!empty($consultaReal)): ?>
        <?= $this->include('organizacao/processos/nova_pasta_real') ?>
    <?php endif ?>
</div>
<div id="aviso-copia-processo" class="aviso-copia-processo" role="status" popover="manual"></div>
<?= $this->endSection() ?>
