<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/processo.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<!-- A consulta real usa os documentos registrados e nunca carrega o PDF demonstrativo. -->
<nav class="caminho-processo" aria-label="Caminho do processo">
    <span>Processos</span><span aria-hidden="true">/</span><span><?= esc($processo['numero']) ?></span>
    <a class="voltar-processos acao-processo" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos'), 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i>Voltar</a>
</nav>
<?php if ($mensagem = session()->getFlashdata('processoAberto')): ?>
    <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
<?php endif ?>
<header class="cabecalho-processo">
    <div><h1><?= esc($processo['assunto']) ?></h1>
    <div class="resumo-processo">
        <span><?= esc($processo['situacao']) ?></span><span><?= esc($processo['unidade'] . ' / ' . $processo['setor']) ?></span>
        <?php if ($processo['acesso']): ?><span><?= esc($processo['acesso']) ?></span><?php endif ?>
    </div></div>
</header>
<?php if ($processo['descricao']): ?><p class="nota-simulacao"><?= esc($processo['descricao']) ?></p><?php endif ?>
<section class="painel-processos" aria-labelledby="documentos-consulta">
    <div class="cabecalho-painel"><div><h2 id="documentos-consulta">Documentos do processo</h2><p>Consulte os documentos registrados. A abertura de arquivos ainda depende da integração do armazenamento.</p></div></div>
    <?php if (!$documentos): ?><p class="vazio-simulacao">Nenhum documento cadastrado neste processo.</p><?php endif ?>
    <ul class="lista-simulacao">
        <?php foreach ($documentos as $documento): ?>
            <li><strong><?= esc($documento['titulo']) ?></strong><small><?= esc(implode(' · ', array_filter([$documento['numero_completo'], date('d/m/Y H:i', strtotime($documento['criado_em']))]))) ?></small></li>
        <?php endforeach ?>
    </ul>
</section>
<?= $this->endSection() ?>
