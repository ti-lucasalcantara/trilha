<!-- Os comandos da lista são compartilhados pelos três cadastros. -->
<header class="cabecalho-lista-admin">
    <div><h2><?= esc($tituloPagina) ?> cadastrados</h2><p><?= count($registros) ?> registro(s)</p></div>
    <a class="botao-admin principal" href="<?= esc(site_url('admin/' . $cadastro . '/novo'), 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i> Novo <?= esc($singular) ?></a>
</header>
<?php if ($registros): ?>
    <label class="busca-admin"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visually-hidden">Buscar registros</span><input type="search" id="busca-cadastro-admin" placeholder="Buscar na lista…"></label>
    <p id="busca-vazia-admin" class="estado-lista-admin" role="status" hidden>Nenhum registro encontrado.</p>
<?php endif ?>
