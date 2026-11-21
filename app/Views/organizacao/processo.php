<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processo.css'), 'attr') ?>">
<link rel="stylesheet" href="<?= esc(base_url('assets/css/documentos.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/processo.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<nav class="caminho-processo" aria-label="Caminho do processo">
    <a href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard'), 'attr') ?>">Caixa de trabalho</a>
    <span aria-hidden="true">/</span>
    <a href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos'), 'attr') ?>">Processos</a>
    <span aria-hidden="true">/</span>
    <span class="numero-processo" aria-current="page"><?= esc($processo['numero']) ?></span>
</nav>
<?php if (($mensagem = session()->getFlashdata('processoAberto'))): ?>
    <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
<?php endif ?>
<?php if ($mensagem = session()->getFlashdata('sucessoEnvioDocumento')): ?>
    <p class="alert alert-success mb-3" role="status"><?= esc($mensagem) ?></p>
<?php elseif ($mensagem = session()->getFlashdata('erroEnvioDocumento')): ?>
    <p class="alert alert-danger mb-3" role="alert"><?= esc($mensagem) ?></p>
<?php endif ?>
<?php if ($mensagem = session()->getFlashdata('sucessoPastaProcesso')): ?>
    <p class="alert alert-success mb-3" role="status"><?= esc($mensagem) ?></p>
<?php elseif ($mensagem = session()->getFlashdata('erroPastaProcesso')): ?>
    <p class="alert alert-danger mb-3" role="alert"><?= esc($mensagem) ?></p>
<?php endif ?>
<header class="cabecalho-processo">
    <div>
        <h1><?= esc($processo['assunto']) ?></h1>
        <?php if (!empty($processo['descricao_formatada'])): ?>
            <div class="descricao-cabecalho-processo"><?= $processo['descricao_formatada'] ?></div>
        <?php elseif ($processo['descricao']): ?>
            <p class="descricao-cabecalho-processo descricao-processo-texto"><?= esc($processo['descricao']) ?></p>
        <?php endif ?>
        <div class="resumo-processo">
            <?php $coresSituacao = ['Aberto' => 'azul', 'Em análise' => 'laranja', 'Em andamento' => 'roxo', 'Concluído' => 'verde']; ?>
            <?php if (mb_strtolower(trim($processo['situacao'])) !== 'iniciado'): ?>
                <span class="estado-documento <?= esc($coresSituacao[$processo['situacao']] ?? 'neutro', 'attr') ?>"><span></span><?= esc($processo['situacao']) ?></span>
            <?php endif ?>
            <span><i class="fa-regular fa-building" aria-hidden="true"></i><strong>Setor de origem</strong> <?= esc($processo['setor']) ?></span>
            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><strong>Visibilidade</strong> <?= esc($processo['acesso']) ?></span>
        </div>
    </div>
    <?= $this->include('organizacao/processo/tarefas') ?>
</header>
<p id="erro-acompanhar-processo" class="alert alert-danger mb-3" role="alert" hidden></p>

<!-- Mantém a lista de documentos ao lado da área de leitura. -->
<section class="area-processo" data-acesso="<?= esc($processo['acesso'], 'attr') ?>" aria-label="Documentos e leitura do processo">
    <div class="divisao-processo" id="divisao-processo">
        <?= $this->include('organizacao/processo/documentos') ?>
        <?= $this->include('organizacao/processo/visualizador') ?>
        <div id="redimensionar-documentos" class="redimensionar-documentos" role="separator" tabindex="0"
            aria-label="Largura da lista de documentos" aria-orientation="vertical" aria-controls="corpo-documentos"
            aria-valuemin="220" aria-valuemax="520" aria-valuenow="248"
            title="Arraste ou use as setas para ajustar a largura">
            <span aria-hidden="true"><i class="fa-solid fa-ellipsis-vertical"></i></span>
        </div>
    </div>
</section>

<?= $this->endSection() ?>

<?= $this->section('dialogos') ?>
<?= $this->include('organizacao/processo/dialogos_reais') ?>
<?php if ($administradorSimulado): ?>
    <?= $this->include('organizacao/processo/envio_documento') ?>
<?php endif ?>
<?= $this->endSection() ?>
