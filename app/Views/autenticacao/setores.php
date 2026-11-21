<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <title>Escolher setor · Trilha</title>
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/tabler/tabler.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/entrar.css'), 'attr') ?>">
</head>
<body class="pagina-entrada pagina-acesso-direto">
    <main class="cartao-acesso-direto cartao-seletores" aria-labelledby="titulo-setores">
        <img class="logo-entrada" src="<?= esc(base_url('img/logo.png'), 'attr') ?>" alt="Trilha" width="2172" height="724">
        <div class="titulo-entrada">
            <p class="organizacao-escolha-setor"><?= esc($organizacao) ?></p>
            <h1 id="titulo-setores">Em qual setor você quer trabalhar?</h1>
            <p>Escolha o setor para acessar a caixa de trabalho.</p>
        </div>
        <?php if ($erro = session()->getFlashdata('erroEscolhaSetor')): ?>
            <p class="alert alert-danger aviso-entrada" role="alert"><?= esc($erro) ?></p>
        <?php endif ?>
        <form action="<?= esc($acao, 'attr') ?>" method="post">
            <?= csrf_field() ?>
            <?php foreach ($unidades as $unidade): ?>
                <fieldset class="grupo-escolha-setor">
                    <legend><?= esc($unidade['nome']) ?></legend>
                    <?php foreach ($unidade['setores'] as $setor): ?>
                        <label class="opcao-escolha-setor">
                            <input class="form-check-input" type="radio" name="setor" value="<?= esc($setor['id'], 'attr') ?>" required>
                            <span><?= esc($setor['nome']) ?><?= $setor['sigla'] ? ' · ' . esc($setor['sigla']) : '' ?></span>
                        </label>
                    <?php endforeach ?>
                </fieldset>
            <?php endforeach ?>
            <button class="btn btn-primary botao-entrar" type="submit">Continuar</button>
        </form>
    </main>
</body>
</html>
