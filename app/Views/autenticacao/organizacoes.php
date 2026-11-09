<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Escolher organização · Trilha</title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/tabler/tabler.min.css'), 'attr') ?>">
</head>
<body>
    <main class="container py-5">
        <h1>Escolha uma organização</h1>
        <div class="list-group">
            <?php foreach ($vinculos as $vinculo): ?>
                <a class="list-group-item list-group-item-action" href="<?= esc(site_url('organizacao/' . $vinculo['hash'] . '/dashboard'), 'attr') ?>"><?= esc($vinculo['organizacao']) ?></a>
            <?php endforeach ?>
        </div>
    </main>
</body>
</html>
