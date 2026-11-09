<!-- A faixa fica acima do cabeçalho e permite encerrar a prévia a qualquer momento. -->
<?php if ($modoSimulacao): ?>
<div class="faixa-simulacao">
    <div class="limite-pagina">
        <strong><i class="fa-solid fa-eye" aria-hidden="true"></i> Modo simulação <small><?= $administradorSimulado ? 'Alterações reais' : 'Somente leitura' ?></small></strong>
        <form method="post" action="<?= esc(site_url('admin/clientes/' . $empresaSimulada . '/encerrar-simulacao'), 'attr') ?>">
            <?= csrf_field() ?>
            <button type="submit">Sair da simulação</button>
        </form>
    </div>
</div>
<?php endif ?>
<header class="cabecalho-aplicacao">
    <div class="limite-pagina linha-cabecalho">
        <div class="identidade-contexto">
            <a class="marca-aplicacao" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard'), 'attr') ?>" aria-label="Trilha — caixa de trabalho">
                <img src="<?= esc(base_url('img/logo.png'), 'attr') ?>" alt="Trilha" width="132" height="46">
            </a>
            <div class="identificacao-empresa-real">
                <span class="icone-organizacao"><i class="fa-regular fa-building" aria-hidden="true"></i></span>
                <div>
                    <div class="nome-empresa-cabecalho">
                        <strong><?= esc($nomeOrganizacao) ?></strong>
                        <?php if (!$modoSimulacao): ?>
                            <?php
                            $conta = session()->get(\App\Libraries\Organizacao\AcessoOrganizacao::SESSAO);
                            $organizacoesDisponiveis = is_string($conta) ? (new \App\Libraries\Organizacao\AcessoOrganizacao(db_connect()))->vinculos($conta) : [];
                            ?>
                            <?php if (count($organizacoesDisponiveis) > 1): ?>
                                <details class="menu-suspenso trocar-organizacao-cabecalho">
                                    <summary class="botao-icone" aria-label="Trocar de organização" title="Trocar de organização"><i class="fa-solid fa-pen" aria-hidden="true"></i></summary>
                                    <div class="conteudo-menu">
                                        <span class="rotulo-menu">Suas organizações</span>
                                        <?php foreach ($organizacoesDisponiveis as $organizacao): ?>
                                            <?php if ($organizacao['hash'] === $identificadorOrganizacao): ?>
                                                <span class="opcao-menu selecionada" aria-current="true"><?= esc($organizacao['organizacao']) ?></span>
                                            <?php else: ?>
                                                <a class="opcao-menu" href="<?= esc(site_url('organizacao/' . $organizacao['hash'] . '/dashboard'), 'attr') ?>"><?= esc($organizacao['organizacao']) ?></a>
                                            <?php endif ?>
                                        <?php endforeach ?>
                                    </div>
                                </details>
                            <?php endif ?>
                        <?php endif ?>
                    </div>
                    <?= $this->include('layout/contexto_trabalho') ?>
                </div>
            </div>
        </div>
        <div class="ferramentas-cabecalho identidade-usuario-real">
            <span class="avatar-usuario" aria-hidden="true"><?= esc(mb_strtoupper(mb_substr($nomeUsuario, 0, 1))) ?></span>
            <span class="nome-usuario"><?= esc($nomeUsuario) ?><small>Área da organização</small></span>
            <?php if (!$modoSimulacao): ?>
                <form method="post" action="<?= esc(site_url('sair'), 'attr') ?>">
                    <?= csrf_field() ?><button class="btn" type="submit">Sair</button>
                </form>
            <?php endif ?>
        </div>
    </div>
</header>
