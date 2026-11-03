<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processo.css'), 'attr') ?>">
<link rel="stylesheet" href="<?= esc(base_url('assets/css/documentos.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/processo.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<div class="caminho-processo"><a href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos'), 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Processos</a><i class="fa-solid fa-chevron-right" aria-hidden="true"></i><span><?= esc($processo['numero']) ?></span><span class="indicacao-exemplo">Processo demonstrativo</span></div>
<header class="cabecalho-processo"><div><p class="numero-processo"><?= esc($processo['numero']) ?></p><h1><?= esc($processo['assunto']) ?></h1><div class="resumo-processo"><span class="situacao-processo roxo"><span></span><?= esc($processo['situacao']) ?></span><span><i class="fa-regular fa-building" aria-hidden="true"></i><?= esc($processo['unidade'] . ' / ' . $processo['setor']) ?></span><span><i class="fa-solid fa-shield-halved" aria-hidden="true"></i><?= esc($processo['acesso']) ?></span><span><i class="fa-regular fa-file-lines" aria-hidden="true"></i><?= esc((string) count($documentos)) ?> documentos</span></div></div><button type="button" id="acompanhar-processo" class="botao-acompanhar" aria-pressed="<?= $processo['acompanhado'] ? 'true' : 'false' ?>"><i class="<?= $processo['acompanhado'] ? 'fa-solid' : 'fa-regular' ?> fa-star" aria-hidden="true"></i><span><?= $processo['acompanhado'] ? 'Acompanhando' : 'Acompanhar' ?></span></button></header>

<!-- As ações do processo ocupam toda a largura, acima dos documentos e do PDF. -->
<section class="area-processo" data-acesso="<?= esc($processo['acesso'], 'attr') ?>" aria-label="Documentos e leitura do processo">
    <?= $this->include('organizacao/processo/tarefas') ?>
    <div class="divisao-processo" id="divisao-processo">
        <?= $this->include('organizacao/processo/documentos') ?>
        <?= $this->include('organizacao/processo/visualizador') ?>
    </div>
</section>
<p class="nota-leitura"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Todos os documentos utilizam o mesmo PDF de teste. As ações são prévias visuais e não alteram o processo.</p>
<?= $this->endSection() ?>

<?= $this->section('dialogos') ?>
<?= $this->include('organizacao/processo/dialogos') ?>
<?= $this->endSection() ?>
