<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/configuracoes.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?php if (isset($configuracoes)): ?>
<script src="<?= esc(base_url('assets/js/configuracoes.js'), 'attr') ?>" defer></script>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<header class="cabecalho-configuracoes">
    <p class="contexto-configuracoes"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Configurações da organização</p>
    <h1><?= esc($tituloPagina) ?></h1>
</header>

<!-- A mesma interface recebe os dados do banco quando há uma sessão administrativa. -->
<div class="area-configuracoes"<?= isset($configuracoes) ? ' data-configuracoes="' . esc(json_encode($configuracoes, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE), 'attr') . '"' : '' ?>>
    <nav class="navegacao-configuracoes" aria-label="Configurações da organização">
        <?php foreach ([
            'estrutura' => ['Estrutura', 'fa-regular fa-building'],
            'equipe' => ['Equipe', 'fa-solid fa-users'],
            'perfis' => ['Perfis e permissões', 'fa-solid fa-shield-halved'],
            'tipos-processo' => ['Tipos de processo', 'fa-regular fa-file-lines'],
            'situacoes-processo' => ['Situações de processo', 'fa-solid fa-list-check'],
            'tipos-documento' => ['Tipos de Documentos', 'fa-regular fa-folder-open'],
        ] as $secao => [$rotulo, $icone]): ?>
            <a href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/configuracoes/' . $secao), 'attr') ?>"
                <?= $secaoConfiguracoes === $secao ? 'aria-current="page"' : '' ?>>
                <i class="<?= esc($icone, 'attr') ?>" aria-hidden="true"></i><?= esc($rotulo) ?>
            </a>
        <?php endforeach ?>
    </nav>
    <div class="conteudo-configuracoes">
        <?= $this->renderSection('painelConfiguracoes') ?>
        <?php if (($mensagem = session()->getFlashdata('sucessoSimulacao'))): ?>
            <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
        <?php elseif (($mensagem = session()->getFlashdata('erroSimulacao'))): ?>
            <p class="alert alert-danger" role="alert"><?= esc($mensagem) ?></p>
        <?php endif ?>
        <p id="aviso-configuracoes" class="nota-configuracoes" role="status"></p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('dialogos') ?>
<?php if (isset($configuracoes)): ?>
<dialog id="dialogo-configuracoes" class="dialogo-configuracoes" aria-labelledby="titulo-edicao-configuracoes">
    <form method="dialog">
        <h2 id="titulo-edicao-configuracoes"></h2>
        <div id="campos-configuracoes"></div>
        <p id="erro-configuracoes" role="alert" hidden></p>
        <footer>
            <button class="controle-configuracoes" type="button" value="cancelar" data-cancelar-dialogo>Cancelar</button>
            <button class="btn btn-primary" id="confirmar-configuracoes" type="submit" value="confirmar">Salvar</button>
        </footer>
    </form>
</dialog>
<?php endif ?>
<?= $this->endSection() ?>
