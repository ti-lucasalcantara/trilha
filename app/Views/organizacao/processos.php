<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<?php if ($administradorSimulado && in_array('encaminhar', $acoesLote, true)): ?>
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/select2/select2.min.css'), 'attr') ?>">
<?php endif ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processos.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<?php if ($administradorSimulado && in_array('encaminhar', $acoesLote, true)): ?>
    <script src="<?= esc(base_url('assets/vendor/jquery/jquery.min.js'), 'attr') ?>" defer></script>
    <script src="<?= esc(base_url('assets/vendor/select2/select2.min.js'), 'attr') ?>" defer></script>
    <script src="<?= esc(base_url('assets/vendor/select2/pt-BR.js'), 'attr') ?>" defer></script>
<?php endif ?>
<script src="<?= esc(base_url('assets/js/processos.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<!-- A consulta combina a árvore de navegação com a lista paginada. -->
<div class="estrutura-explorador" data-caixa-processos="<?= esc($caixaProcessos, 'attr') ?>">
    <?= $this->include('organizacao/processos/explorador') ?>
    <section class="conteudo-explorador" aria-labelledby="titulo-consulta">
        <header class="titulo-acoes cabecalho-consulta">
            <h1 id="titulo-consulta"><?= match ($caixaProcessos ?? 'ativos') { 'arquivados' => 'Processos arquivados', 'lixeira' => 'Lixeira', default => 'Processos' } ?></h1>
            <?php if ($administradorSimulado && $caixaProcessos === 'ativos'): ?>
                <a class="btn btn-primary text-white botao-novo" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/novo'), 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</a>
                <?php endif ?>
        </header>
        <?php if (($mensagem = session()->getFlashdata('processoAberto'))): ?>
            <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
        <?php endif ?>
        <?php if (($mensagem = session()->getFlashdata('sucessoPastaProcesso'))): ?>
            <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
        <?php elseif (($mensagem = session()->getFlashdata('erroPastaProcesso'))): ?>
            <p class="alert alert-danger" role="alert"><?= esc($mensagem) ?></p>
        <?php endif ?>
        <?php if (!empty($pastaRealCriada)): ?>
            <p class="alert alert-success" role="status"><?= esc($pastaRealCriada) ?></p>
        <?php elseif (($erroPastaReal = session()->getFlashdata('erroPastaReal'))): ?>
            <p class="alert alert-danger" role="alert"><?= esc($erroPastaReal) ?></p>
        <?php endif ?>
        <p id="caminho-selecao" class="caminho-consulta" hidden></p>
        <?= $this->include('organizacao/processos/filtros') ?>
        <?= $this->include('organizacao/processos/listagem') ?>
    </section>
    <?php if ($administradorSimulado): ?>
        <?= $this->include('organizacao/processos/nova_pasta_real') ?>
        <?= $this->include('organizacao/processos/pasta_processo') ?>
        <?= $this->include('organizacao/processos/lote') ?>
    <?php endif ?>
</div>
<div id="aviso-copia-processo" class="aviso-copia-processo" role="status" popover="manual"></div>

    <div id="aviso-acompanhamento" class="aviso-copia-processo aviso-acompanhamento" role="status" popover="manual"></div>

<?= $this->endSection() ?>
