<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processos.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/processos.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<div class="linha-contexto"><div class="caminho-pagina"><i class="fa-regular fa-building" aria-hidden="true"></i><?= esc($nomeOrganizacao) ?><span>/</span><span>Processos</span></div><span class="consulta-demonstrativa"><span></span>Consulta demonstrativa</span></div>
<div class="titulo-acoes"><div><p class="sobretitulo">CADA PROCESSO, UM CAMINHO</p><h1>Processos<span class="ponto-titulo">.</span></h1><p class="descricao-pagina">Localize, organize e acompanhe os processos da sua organização.</p></div><button type="button" class="btn btn-primary botao-novo" data-aviso="A tela de abertura será construída a seguir. Nenhum processo foi criado."><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</button></div>

<!-- O explorador organiza a consulta; sua seleção não concede acesso aos itens. -->
<div class="estrutura-explorador">
    <?= $this->include('organizacao/processos/explorador') ?>
    <section class="conteudo-explorador" aria-labelledby="titulo-consulta">
        <header class="cabecalho-consulta"><div><div class="caminho-consulta"><i class="fa-regular fa-folder-open" aria-hidden="true"></i><span>Processos</span><i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span id="caminho-selecao">Todos os processos</span></div><h2 id="titulo-consulta">Todos os processos</h2></div><span class="selo-demonstracao"><?= esc((string) count($processos)) ?> exemplos</span></header>
        <?= $this->include('organizacao/processos/filtros') ?>
        <?= $this->include('organizacao/processos/listagem') ?>
    </section>
</div>
<?= $this->endSection() ?>

