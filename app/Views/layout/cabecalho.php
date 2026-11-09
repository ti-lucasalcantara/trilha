<!-- Mantém a identidade do produto e o contexto da organização sempre visíveis. -->
<header class="cabecalho-aplicacao">
    <div class="limite-pagina linha-cabecalho">
        <div class="identidade-contexto">
            <a class="marca-aplicacao" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard'), 'attr') ?>" aria-label="Trilha — caixa de trabalho">
                <img src="<?= esc(base_url('img/logo.png'), 'attr') ?>" alt="Trilha" width="132" height="46">
            </a>
            <div class="identificacao-empresa-real contexto-organizacao-cabecalho">
                <span class="icone-organizacao"><i class="fa-regular fa-building" aria-hidden="true"></i></span>
                <div>
                    <div class="nome-empresa-cabecalho"><strong><?= esc($nomeOrganizacao) ?></strong></div>
                    <?= $this->include('layout/contexto_trabalho') ?>
                </div>
            </div>
        </div>
        <div class="ferramentas-cabecalho">
            <button type="button" class="atalho-pesquisa" data-focar-pesquisa><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>Pesquisar nesta página</span><kbd>/</kbd></button>
            <details class="menu-suspenso menu-notificacoes"><summary class="botao-icone" aria-label="Notificações demonstrativas"><i class="fa-regular fa-bell" aria-hidden="true"></i><span class="ponto-notificacao"></span></summary><div class="conteudo-menu"><span class="rotulo-menu">NOTIFICAÇÕES · EXEMPLOS</span><p class="item-notificacao"><strong>Uma assinatura aguarda você</strong><small>Admissão de novo colaborador · Há 40 min</small></p><p class="item-notificacao"><strong>Novo processo recebido</strong><small>Financeiro · Há 15 min</small></p></div></details>
            <details class="menu-suspenso menu-usuario"><summary><span class="avatar-usuario">LC</span><span class="nome-usuario"><?= esc($nomeUsuario) ?><small>Área da organização</small></span><i class="fa-solid fa-chevron-down seta-menu" aria-hidden="true"></i></summary><div class="conteudo-menu"><span class="rotulo-menu">MINHA CONTA</span><button type="button" class="opcao-menu" data-aviso="A tela de perfil será construída em uma próxima etapa."><i class="fa-regular fa-user" aria-hidden="true"></i>Meu perfil</button><a class="opcao-menu" href="<?= esc(site_url('login'), 'attr') ?>"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>Voltar à tela de entrada</a></div></details>
        </div>
    </div>
</header>

