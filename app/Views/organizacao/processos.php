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
            <button type="button" class="btn btn-primary botao-novo" data-aviso="A tela de abertura será construída a seguir. Nenhum processo foi criado."><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</button>
        </header>
        <p id="caminho-selecao" class="caminho-consulta" hidden></p>
        <?= $this->include('organizacao/processos/filtros') ?>
        <?= $this->include('organizacao/processos/listagem') ?>
    </section>
</div>
<div id="aviso-copia-processo" class="aviso-copia-processo" role="status" popover="manual"></div>
<?= $this->endSection() ?>
