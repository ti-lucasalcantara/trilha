<?= $this->extend('organizacao/configuracoes') ?>

<?= $this->section('painelConfiguracoes') ?>
<section id="equipe" class="cartao-configuracoes" aria-labelledby="titulo-equipe">
    <header><div><h2 id="titulo-equipe">Equipe <span class="contagem-configuracoes" id="contagem-pessoas"></span></h2><p>Consulte as pessoas da organização e os setores aos quais estão vinculadas.</p></div><button type="button" class="btn btn-primary" data-novo="pessoas"><i class="fa-solid fa-plus" aria-hidden="true"></i>Adicionar pessoa</button></header>
    <label class="busca-equipe"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visualmente-oculto">Buscar por nome ou e-mail</span><input type="search" id="buscar-equipe" placeholder="Buscar por nome ou e-mail"></label>
    <div id="lista-pessoas" class="lista-configuracoes"></div>
</section>
<?= $this->endSection() ?>
