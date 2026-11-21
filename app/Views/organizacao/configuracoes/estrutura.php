<?= $this->extend('organizacao/configuracoes') ?>

<?= $this->section('painelConfiguracoes') ?>
<section id="estrutura" aria-label="Unidades e setores">
    <article class="cartao-configuracoes">
        <header><div><h2>Unidades e setores <span class="contagem-configuracoes" id="contagem-unidades"></span></h2><p>Abra uma unidade para consultar e gerenciar seus setores.</p></div><button type="button" class="btn btn-primary" data-novo="unidades"><i class="fa-solid fa-plus" aria-hidden="true"></i>Nova unidade</button></header>
        <div id="lista-unidades" class="lista-configuracoes"></div>
    </article>
    <!-- Cada unidade recebe uma cópia independente do painel de setores. -->
    <template id="modelo-setores-da-unidade">
        <section class="setores-da-unidade">
            <header><div><h3>Setores <span class="contagem-configuracoes"></span></h3><p class="nome-unidade"></p></div><button type="button" class="controle-configuracoes" data-novo="setores"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo setor</button></header>
            <div class="lista-configuracoes"></div>
        </section>
    </template>
</section>
<?= $this->endSection() ?>
