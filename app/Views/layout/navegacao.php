<!-- Agrupa os módulos no menu horizontal, sem criar links para telas inexistentes. -->
<div class="barra-navegacao">
    <div class="limite-pagina linha-navegacao">
        <button class="alternar-menu" type="button" aria-expanded="false" aria-controls="navegacao-principal"><i class="fa-solid fa-bars" aria-hidden="true"></i>Menu da organização</button>
        <nav id="navegacao-principal" class="navegacao-principal" aria-label="Menu da organização">
            <a class="item-navegacao <?= $paginaAtiva === 'dashboard' ? 'ativo' : '' ?>" <?= $paginaAtiva === 'dashboard' ? 'aria-current="page"' : '' ?> href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard'), 'attr') ?>"><i class="fa-solid fa-inbox" aria-hidden="true"></i>Caixa de trabalho</a>
            <a class="item-navegacao <?= $paginaAtiva === 'processos' ? 'ativo' : '' ?>" <?= $paginaAtiva === 'processos' ? 'aria-current="page"' : '' ?> href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos'), 'attr') ?>"><i class="fa-solid fa-code-branch" aria-hidden="true"></i>Processos</a>
            <button type="button" class="item-navegacao" data-aviso="O módulo de documentos será construído após as telas de processos."><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Documentos</button>
            <?php if ($paginaAtiva === 'dashboard'): ?>
            <button type="button" class="item-navegacao" data-filtrar="assinar"><i class="fa-solid fa-signature" aria-hidden="true"></i>Assinaturas<span class="contador-menu"><?= esc((string) $quantidades['assinar']) ?></span></button>
            <?php else: ?>
            <button type="button" class="item-navegacao" data-aviso="A tela de assinaturas será construída em uma próxima etapa."><i class="fa-solid fa-signature" aria-hidden="true"></i>Assinaturas</button>
            <?php endif ?>
            <button type="button" class="item-navegacao" data-aviso="As telas de pastas e subpastas serão construídas em uma próxima etapa."><i class="fa-regular fa-folder" aria-hidden="true"></i>Pastas</button>
            <details class="menu-suspenso menu-configuracoes"><summary class="item-navegacao <?= $paginaAtiva === 'configuracoes' ? 'ativo' : '' ?>"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Configurações<i class="fa-solid fa-chevron-down seta-menu" aria-hidden="true"></i></summary><div class="conteudo-menu"><span class="rotulo-menu">ADMINISTRAÇÃO DA ORGANIZAÇÃO</span><a class="opcao-menu" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/configuracoes'), 'attr') ?>"><i class="fa-solid fa-users" aria-hidden="true"></i>Estrutura e equipe</a><button type="button" class="opcao-menu" data-aviso="Tipos, campos, status e numeração serão configurados em telas próprias."><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Processos e documentos</button><a class="opcao-menu" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/configuracoes'), 'attr') ?>#permissoes"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Perfis e permissões</a></div></details>
        </nav>
        <span class="identificacao-previa"><span></span>Prévia visual</span>
    </div>
</div>
