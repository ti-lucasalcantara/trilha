<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title><?= esc($tituloPagina) ?> · Trilha</title>
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg'), 'attr') ?>">
    <link rel="alternate icon" href="<?= esc(base_url('favicon.ico'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/tabler/tabler.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/organizacao.css'), 'attr') ?>">
    <?= $this->renderSection('estilos') ?>
    <script src="<?= esc(base_url('assets/js/layout.js'), 'attr') ?>" defer></script>
    <?= $this->renderSection('scripts') ?>
</head>
<body class="area-organizacao">
    <!-- Os fragmentos mantêm o mesmo cabeçalho e navegação nas próximas telas. -->
    <a class="atalho-conteudo" href="#conteudo-principal">Ir para o conteúdo</a>
    <?= $this->include('layout/cabecalho') ?>
    <?= $this->include('layout/navegacao') ?>
    <main id="conteudo-principal" class="conteudo-principal limite-pagina">
        <?= $this->renderSection('conteudo') ?>
    </main>
    <?= $this->include('layout/rodape') ?>
    <?= $this->renderSection('dialogos') ?>
    <div class="aviso-flutuante" id="aviso-interface" role="status" aria-live="polite" hidden></div>
</body>
</html>
