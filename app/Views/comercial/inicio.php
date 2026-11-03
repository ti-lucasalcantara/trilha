<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#7c3aed">
    <meta name="description" content="Conheça o Trilha: processos, documentos e equipes conectados. Explore a demonstração e encontre um plano para a sua empresa.">
    <title>Trilha — Menos papel. Mais trabalho fluindo.</title>
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg'), 'attr') ?>">
    <link rel="alternate icon" href="<?= esc(base_url('favicon.ico'), 'attr') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/comercial.css'), 'attr') ?>">
    <script src="<?= esc(base_url('assets/js/comercial.js'), 'attr') ?>" defer></script>
</head>
<body class="pagina-comercial">
    <a class="pular-conteudo" href="#conteudo">Ir para o conteúdo</a>
    <?= $this->include('comercial/cabecalho') ?>
    <main id="conteudo">
        <?= $this->include('comercial/apresentacao') ?>
        <?= $this->include('comercial/recursos') ?>
        <?= $this->include('comercial/planos') ?>
        <?= $this->include('comercial/perguntas') ?>
        <section class="convite-final" aria-labelledby="titulo-convite"><div class="largura-comercial"><div class="simbolo-convite" aria-hidden="true"><i class="fa-solid fa-route"></i></div><p class="chamada-secao">UM NOVO CAMINHO PARA A SUA EMPRESA</p><h2 id="titulo-convite">Seu próximo passo<br>pode ser mais simples<span>.</span></h2><p>Conheça o Trilha por dentro e imagine seus processos<br class="quebra-desktop"> acontecendo em um só lugar.</p><div class="acoes-convite"><a class="botao-comercial principal" href="<?= esc($enderecoDemonstracao, 'attr') ?>">Explorar a demonstração<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></a><a class="link-contato" href="<?= esc($enderecoContato, 'attr') ?>">Falar com a equipe<i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></div><small>Demonstração visual com dados fictícios. Produto em desenvolvimento.</small></div></section>
    </main>
    <?= $this->include('comercial/rodape') ?>
</body>
</html>
