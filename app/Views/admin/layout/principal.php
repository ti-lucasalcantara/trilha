<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="dark light">
    <title><?= esc($tituloPagina) ?> · Trilha Admin</title>
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/tabler/tabler.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/admin.css'), 'attr') ?>">
    <script src="<?= esc(base_url('assets/js/admin.js'), 'attr') ?>" defer></script>
</head>
<body class="area-admin">
    <a class="atalho-admin" href="#conteudo-admin">Ir para o conteúdo</a>
    <?= $this->include('admin/layout/menu') ?>
    <main id="conteudo-admin" class="conteudo-admin">
        <!-- O controle de tema fica no extremo direito em todas as páginas. -->
        <header class="cabecalho-admin">
            <div>
                <p class="rotulo-admin">ADMINISTRAÇÃO DA PLATAFORMA</p>
                <h1><?= esc($tituloPagina) ?></h1>
                <p><?= esc($descricaoPagina) ?></p>
            </div>
            <button type="button" id="alternar-tema-admin" aria-label="Ativar tema claro" title="Ativar tema claro">
                <i class="fa-solid fa-sun" aria-hidden="true"></i>
            </button>
        </header>
        <p class="previa-admin"><span></span> <?= esc($indicacaoPagina ?? 'Banco local · Desenvolvimento') ?></p>
        <?php if ($mensagem = session()->getFlashdata('sucessoAdmin')): ?>
            <p class="mensagem-admin sucesso" role="status"><?= esc($mensagem) ?></p>
        <?php elseif ($mensagem = session()->getFlashdata('erroAdmin')): ?>
            <p class="mensagem-admin erro" role="alert"><?= esc($mensagem) ?></p>
        <?php endif ?>
        <?= $this->renderSection('conteudo') ?>
    </main>
</body>
</html>
