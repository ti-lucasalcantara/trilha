<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="color-scheme" content="light">
    <meta name="description" content="Acesse o Trilha, seu espaço para conectar processos, documentos e pessoas.">
    <title>Entrar · Trilha</title>
    <!-- Identifica a aplicação nas abas e nos favoritos do navegador. -->
    <link rel="icon" type="image/svg+xml" href="<?= esc(base_url('favicon.svg'), 'attr') ?>">
    <link rel="alternate icon" href="<?= esc(base_url('favicon.ico'), 'attr') ?>">
    <link rel="apple-touch-icon" href="<?= esc(base_url('img/favicon-t.png'), 'attr') ?>">
    <link rel="stylesheet" href="<?= esc(base_url('assets/vendor/tabler/tabler.min.css'), 'attr') ?>">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css" referrerpolicy="no-referrer">
    <link rel="stylesheet" href="<?= esc(base_url('assets/css/entrar.css'), 'attr') ?>">
    <script src="<?= esc(base_url('assets/js/entrar.js'), 'attr') ?>" defer></script>
</head>
<body class="pagina-entrada">
    <!-- Apresenta a marca e o formulário em colunas, com adaptação para celular. -->
    <main class="estrutura-entrada">
        <section class="painel-marca" aria-labelledby="titulo-marca">
            <div class="cabecalho-marca"><span class="simbolo-marca" aria-hidden="true">t<span></span></span><span>PROCESSOS E DOCUMENTOS CONECTADOS</span></div>
            <div class="conteudo-marca">
                <p class="chamada-marca"><span></span> Um novo caminho para o seu trabalho</p>
                <h1 id="titulo-marca">Menos papel.<br>Mais <span>possibilidades.</span></h1>
                <p class="descricao-marca">Conecte pessoas, documentos e decisões.<br>Cada etapa no lugar certo, do início ao fim.</p>
                <div class="ilustracao-processo" aria-hidden="true">
                    <div class="linha-fluxo"></div>
                    <div class="documento-fluxo"><span class="icone-documento"><i class="fa-regular fa-file-lines"></i></span><div><span class="legenda-fluxo">O PRIMEIRO PASSO</span><strong>Uma nova ideia</strong><span class="linhas-documento"><i></i><i></i></span></div><span class="ponto-fluxo laranja"></span></div>
                    <div class="analise-fluxo"><span class="icone-analise"><i class="fa-solid fa-arrow-right-arrow-left"></i></span><div><strong>Pessoas conectadas</strong><span>O trabalho segue em frente</span></div><span class="ponto-fluxo roxo"></span></div>
                    <div class="conclusao-fluxo"><span class="icone-conclusao"><i class="fa-solid fa-check"></i></span><div><strong>Mais uma etapa concluída</strong><span>Um caminho que fica registrado</span></div><span class="ponto-fluxo menta"></span></div>
                </div>
            </div>
            <div class="rodape-marca"><span class="pontos-marca" aria-hidden="true"><i></i><i></i><i></i></span><span>Clareza em cada etapa.</span></div>
        </section>

        <section class="painel-acesso" aria-labelledby="titulo-entrada">
            <div class="conteudo-acesso">
                <img class="logo-entrada" src="<?= esc(base_url('img/logo.png'), 'attr') ?>" alt="Trilha" width="2172" height="724">
                <div class="titulo-entrada"><p class="chamada-acesso">BEM-VINDO AO SEU ESPAÇO</p><h2 id="titulo-entrada">Bom ter você por aqui.</h2><p>Entre com seu e-mail e senha para continuar.</p></div>

                <div id="aviso-entrada" class="alert alert-info aviso-entrada" role="status" aria-live="polite" <?= $aviso === null ? 'hidden' : '' ?>><?= $aviso !== null ? esc($aviso) : '' ?></div>

                <!-- Coleta as credenciais sem efetuar autenticação nesta etapa. -->
                <form id="formulario-entrada" action="<?= esc(site_url('login'), 'attr') ?>" method="post">
                    <?= csrf_field() ?>
                    <div class="campo-entrada"><label class="form-label" for="email">E-mail</label><div class="grupo-campo"><span class="icone-campo" aria-hidden="true"><i class="fa-regular fa-envelope"></i></span><input class="form-control" id="email" name="email" type="email" placeholder="voce@empresa.com.br" autocomplete="username" inputmode="email" autocapitalize="none" spellcheck="false" maxlength="254" required></div></div>
                    <div class="campo-entrada"><label class="form-label" for="senha">Senha</label><div class="grupo-campo"><span class="icone-campo" aria-hidden="true"><i class="fa-solid fa-lock"></i></span><input class="form-control" id="senha" name="senha" type="password" placeholder="Digite sua senha" autocomplete="current-password" required><button class="alternar-senha" id="alternar-senha" type="button" aria-label="Mostrar senha" aria-pressed="false" aria-controls="senha"><i class="fa-regular fa-eye" aria-hidden="true"></i></button></div></div>
                    <div class="linha-recuperacao"><button type="button" id="recuperar-senha" class="botao-recuperacao" aria-controls="aviso-entrada">Esqueci minha senha</button></div>
                    <button class="btn btn-primary botao-entrar" type="submit"><span>Entrar na minha conta</span><i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button>
                </form>

                <div class="observacao-acesso"><i class="fa-solid fa-building" aria-hidden="true"></i><p>Seu trabalho em um só lugar.<br><span>Após entrar, você poderá escolher sua organização.</span></p></div>
                <p class="observacao-convite">Ainda não tem acesso? <span>Solicite um convite ao administrador da sua organização.</span></p>
            </div>
            <footer class="rodape-acesso"><span>© <?= date('Y') ?> Trilha</span><span>Seu próximo passo começa aqui.<span class="ponto-rodape" aria-hidden="true"></span></span></footer>
        </section>
    </main>
</body>
</html>
