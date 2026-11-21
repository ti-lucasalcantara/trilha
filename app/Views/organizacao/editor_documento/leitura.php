<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($documento['titulo']) ?></title>
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/editor-documento.css'), 'attr') ?>">
</head>
<body class="editor-leitura">
    <article class="editor-pagina">
        <h1 class="editor-titulo-papel"><?= esc($documento['titulo']) ?></h1>
        <div class="editor-texto"><?= $documento['conteudo_editor'] ?></div>
    </article>
</body>
</html>
