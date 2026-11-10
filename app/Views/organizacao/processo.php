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
    <span>Processos</span>
    <span aria-hidden="true">/</span>
    <span class="numero-processo"><?= esc($processo['numero']) ?></span>
    <a class="voltar-processos acao-processo" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos'), 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Voltar</a>
</nav>
<?php if (!empty($consultaReal) && ($mensagem = session()->getFlashdata('processoAberto'))): ?>
    <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
<?php endif ?>
<header class="cabecalho-processo">
    <div>
        <h1><?= esc($processo['assunto']) ?></h1>
        <div class="resumo-processo">
            <?php $coresSituacao = ['Aberto' => 'azul', 'Em análise' => 'laranja', 'Em andamento' => 'roxo', 'Concluído' => 'verde']; ?>
            <span class="estado-documento <?= esc($coresSituacao[$processo['situacao']] ?? 'neutro', 'attr') ?>"><span></span><?= esc($processo['situacao']) ?></span>
            <span><i class="fa-regular fa-building" aria-hidden="true"></i><?= esc($processo['unidade'] . ' / ' . $processo['setor']) ?></span>
            <span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= esc($processo['acesso']) ?></span>
        </div>
    </div>
    <?= $this->include('organizacao/processo/tarefas') ?>
</header>

<!-- Mantém a lista de documentos ao lado da área de leitura. -->
<section class="area-processo" data-acesso="<?= esc($processo['acesso'], 'attr') ?>" <?= !empty($consultaReal) ? 'data-processo-real' : '' ?> aria-label="Documentos e leitura do processo">
    <div class="divisao-processo" id="divisao-processo">
        <?= $this->include('organizacao/processo/documentos') ?>
        <?= $this->include('organizacao/processo/visualizador') ?>
    </div>
</section>
<?php if (!empty($consultaReal)): ?>
    <?php if ($processo['descricao']): ?><p class="nota-leitura"><?= esc($processo['descricao']) ?></p><?php endif ?>
    <p class="nota-leitura"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>As ações de documentos e tramitação estão em preparação.</p>
<?php else: ?>
    <p class="nota-leitura"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Todos os documentos utilizam o mesmo PDF de teste. As ações são prévias visuais e não alteram o processo.</p>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('dialogos') ?>
<?= $this->include(!empty($consultaReal) ? 'organizacao/processo/dialogos_reais' : 'organizacao/processo/dialogos') ?>
<?= $this->endSection() ?>
