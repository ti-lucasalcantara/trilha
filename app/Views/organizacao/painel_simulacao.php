<?= $this->extend('layout/principal') ?>

<?= $this->section('conteudo') ?>
<div class="titulo-acoes">
    <div>
        <h1><?= esc($paginaAtiva === 'dashboard' ? 'Caixa de trabalho' : $inventario['categorias'][$paginaAtiva]) ?><span class="ponto-titulo">.</span></h1>
        <p class="descricao-pagina"><?= $modoSimulacao ? 'Prévia de ' : '' ?><?= esc($nomeOrganizacao) ?> · <?= esc($nomeUsuario) ?></p>
    </div>
</div>

<?php if ($administradorSimulado || isset($inventario)): ?>
        <section class="painel-processos" aria-labelledby="titulo-processos-simulados">
            <div class="cabecalho-painel">
                <div><h2 id="titulo-processos-simulados"><?= esc($paginaAtiva === 'dashboard' ? 'Processos da organização' : $inventario['categorias'][$inventario['categoria']]) ?></h2>
                <p><?= (int) $inventario['total'] ?> registro(s) · Consulta ao banco da organização</p></div>
            </div>
            <?php if (!$inventario['registros']): ?>
                <p class="vazio-simulacao">Nenhum registro nesta seção.</p>
            <?php else: ?>
                <div class="tabela-responsiva" role="region" aria-label="Registros da organização" tabindex="0">
                    <table class="tabela-processos tabela-simulacao">
                        <thead><tr><?php foreach ($inventario['colunas'] as $rotulo): ?><th scope="col"><?= esc($rotulo) ?></th><?php endforeach ?></tr></thead>
                        <tbody>
                            <?php foreach ($inventario['registros'] as $registro): ?>
                                <tr><?php foreach ($inventario['colunas'] as $campo => $rotulo): ?><td><?= esc((string) ($registro[$campo] ?? '—')) ?></td><?php endforeach ?></tr>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                </div>
                <?php if ($inventario['paginas'] > 1): ?>
                    <nav class="paginacao-simulacao" aria-label="Paginação dos registros">
                        <?php foreach (['Anterior' => $inventario['pagina'] - 1, 'Próxima' => $inventario['pagina'] + 1] as $rotulo => $pagina): ?>
                            <?php if ($pagina > 0 && $pagina <= $inventario['paginas']): ?>
                                <a class="btn" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/dashboard') . '?' . http_build_query(['modulo' => $paginaAtiva, 'pagina' => $pagina]), 'attr') ?>"><?= esc($rotulo) ?></a>
                            <?php endif ?>
                        <?php endforeach ?>
                    </nav>
                <?php endif ?>
            <?php endif ?>
        </section>
<?php else: ?>
    <p class="nota-simulacao">Aqui estão os vínculos e as permissões atribuídas à sua conta nesta organização.</p>
    <div class="grade-simulacao">
        <section class="painel-processos" aria-labelledby="titulo-setores-simulacao">
            <div class="cabecalho-painel"><h2 id="titulo-setores-simulacao">Setores vinculados</h2></div>
            <?php if (!$setores): ?><p class="vazio-simulacao">Nenhum setor vinculado.</p><?php endif ?>
            <ul class="lista-simulacao">
                <?php foreach ($setores as $setor): ?>
                    <li><strong><?= esc($setor['setor']) ?></strong><small><?= esc($setor['unidade'] . ($setor['funcao'] ? ' · ' . $setor['funcao'] : '')) ?></small></li>
                <?php endforeach ?>
            </ul>
        </section>
        <section class="painel-processos" aria-labelledby="titulo-perfis-simulacao">
            <div class="cabecalho-painel"><h2 id="titulo-perfis-simulacao">Perfis e permissões atribuídos</h2></div>
            <?php if (!$perfis): ?><p class="vazio-simulacao">Nenhum perfil atribuído.</p><?php endif ?>
            <ul class="lista-simulacao">
                <?php foreach ($perfis as $perfil): ?>
                    <?php $ambito = implode(' / ', array_filter([$perfil['unidade'], $perfil['setor']])); ?>
                    <li>
                        <strong><?= esc($perfil['perfil']) ?></strong>
                        <small><?= esc(($ambito ?: 'Organização inteira') . ' · ' . ucfirst($perfil['status'])) ?></small>
                        <?php foreach ($permissoes[$perfil['id']] ?? [] as $permissao): ?>
                            <small><?= esc($permissao['modulo'] . ' · ' . $permissao['descricao']) ?></small>
                        <?php endforeach ?>
                    </li>
                <?php endforeach ?>
            </ul>
        </section>
    </div>
<?php endif ?>
<?= $this->endSection() ?>
