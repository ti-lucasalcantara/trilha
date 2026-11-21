<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/caixa-trabalho.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<?php
$base = site_url('organizacao/' . $identificadorOrganizacao);
$dashboard = $base . '/dashboard';
$setorAtivo = $contextoTrabalho['setor'];
$cartoes = [
    'todos' => ['Processos na caixa', 'Visíveis no setor ativo', 'fa-inbox'],
    'vencidos' => ['Prazos vencidos', 'Com prazo anterior a hoje', 'fa-clock'],
    'proximos' => ['Próximos prazos', 'De hoje até os próximos 7 dias', 'fa-calendar-days'],
];
$atalhos = [
    ['processos', 'Explorar processos', 'fa-folder-open', '/processos'],
    ['documentos', 'Documentos', 'fa-file-lines', '/dashboard?modulo=documentos'],
    ['assinaturas', 'Assinaturas', 'fa-signature', '/dashboard?modulo=assinaturas'],
    ['pastas', 'Pastas', 'fa-folder-tree', '/pastas'],
];
if (!$administradorSimulado) {
    $atalhos[] = ['unidades', 'Unidades', 'fa-building', '/dashboard?modulo=unidades'];
    $atalhos[] = ['setores', 'Setores', 'fa-sitemap', '/dashboard?modulo=setores'];
}
$tituloLista = match ($filtro) {
    'vencidos' => 'Processos com prazo vencido',
    'proximos' => 'Processos com prazo próximo',
    default => 'Processos do setor',
};
?>
<div class="caixa-trabalho">
    <header class="caixa-abertura">
        <div class="caixa-introducao">
            <p class="caixa-setor"><i class="fa-solid fa-building" aria-hidden="true"></i><?= esc($setorAtivo['nome'] ?? 'Sem setor ativo') ?></p>
            <h1>Caixa de trabalho</h1>
            <p class="caixa-descricao">Acompanhe os processos do seu setor e veja quais prazos precisam de atenção.</p>
        </div>
        <?php if ($administradorSimulado && $setorAtivo): ?>
            <a class="caixa-novo" href="<?= esc($base . '/processos/novo', 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</a>
        <?php endif ?>
    </header>

    <nav class="caixa-indicadores" aria-label="Filtrar processos por prazo">
        <?php foreach ($cartoes as $chave => [$rotulo, $descricao, $icone]): ?>
            <a class="caixa-indicador caixa-indicador-<?= esc($chave, 'attr') ?>"
                href="<?= esc($dashboard . ($chave === 'todos' ? '' : '?filtro=' . $chave) . '#processos-caixa', 'attr') ?>"
                <?= $filtro === $chave ? 'aria-current="true"' : '' ?>>
                <span class="caixa-icone"><i class="fa-solid <?= esc($icone, 'attr') ?>" aria-hidden="true"></i></span>
                <span class="caixa-indicador-texto">
                    <span><?= esc($rotulo) ?></span>
                    <strong><?= number_format($indicadores[$chave], 0, ',', '.') ?></strong>
                    <small><?= esc($descricao) ?></small>
                </span>
                <i class="fa-solid fa-arrow-right caixa-seta" aria-hidden="true"></i>
            </a>
        <?php endforeach ?>
    </nav>

    <div class="caixa-grade">
        <section class="caixa-painel" id="processos-caixa" aria-labelledby="titulo-processos-caixa" tabindex="-1">
            <header class="caixa-painel-topo">
                <div>
                    <h2 id="titulo-processos-caixa"><?= esc($tituloLista) ?></h2>
                    <p><?= $filtro === 'todos' ? 'Atualizados mais recentemente' : 'Ordenados pelo prazo mais antigo' ?></p>
                </div>
            </header>

            <?php if ($processos): ?>
                <ul class="caixa-processos">
                    <?php foreach ($processos as $processo): ?>
                        <li>
                            <a class="caixa-processo" href="<?= esc($base . '/processos/' . $processo['hash_publico'], 'attr') ?>">
                                <span class="caixa-icone caixa-icone-processo"><i class="fa-regular fa-file-lines" aria-hidden="true"></i></span>
                                <span class="caixa-processo-texto">
                                    <small><?= esc($processo['numero'] ?: 'Sem número') ?></small>
                                    <strong><?= esc($processo['assunto']) ?></strong>
                                </span>
                                <span class="caixa-situacao"><?= esc($processo['situacao']) ?></span>
                                <span class="caixa-prazo<?= $processo['grupoPrazo'] === 'vencidos' ? ' caixa-prazo-vencido' : '' ?>">
                                    <?php if ($processo['prazo']): ?>
                                        <span><?= $processo['grupoPrazo'] === 'vencidos' ? 'Venceu' : 'Prazo' ?></span>
                                        <time datetime="<?= esc($processo['prazo'], 'attr') ?>"><?= $processo['prazo'] === $dataHoje ? 'Hoje' : esc(date('d/m/Y', strtotime($processo['prazo']))) ?></time>
                                    <?php else: ?>
                                        <span>Sem prazo</span>
                                    <?php endif ?>
                                </span>
                                <i class="fa-solid fa-chevron-right caixa-seta" aria-hidden="true"></i>
                            </a>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                <div class="caixa-vazio">
                    <span class="caixa-icone"><i class="fa-solid fa-inbox" aria-hidden="true"></i></span>
                    <?php if (!$setorAtivo): ?>
                        <h3>Sem setor de trabalho</h3>
                        <p><?= $administradorSimulado ? 'Cadastre uma unidade e um setor em Configurações.' : 'Solicite à administração um vínculo com um setor ativo.' ?></p>
                    <?php elseif ($filtro !== 'todos'): ?>
                        <h3><?= $filtro === 'vencidos' ? 'Nenhum prazo vencido' : 'Nenhum prazo próximo' ?></h3>
                        <a href="<?= esc($dashboard . '#processos-caixa', 'attr') ?>">Ver todos os processos da caixa</a>
                    <?php else: ?>
                        <h3>Nenhum processo na caixa</h3>
                        <p>Os processos disponíveis no setor ativo aparecerão aqui.</p>
                    <?php endif ?>
                </div>
            <?php endif ?>

            <?php if ($paginas > 1): ?>
                <nav class="caixa-paginacao" aria-label="Paginação dos processos">
                    <?php if ($pagina > 1): ?>
                        <a rel="prev" href="<?= esc($dashboard . '?' . http_build_query(['filtro' => $filtro, 'pagina' => $pagina - 1]) . '#processos-caixa', 'attr') ?>">Anterior</a>
                    <?php endif ?>
                    <span>Página <?= $pagina ?> de <?= $paginas ?></span>
                    <?php if ($pagina < $paginas): ?>
                        <a rel="next" href="<?= esc($dashboard . '?' . http_build_query(['filtro' => $filtro, 'pagina' => $pagina + 1]) . '#processos-caixa', 'attr') ?>">Próxima</a>
                    <?php endif ?>
                </nav>
            <?php endif ?>
        </section>

        <aside class="caixa-painel" aria-labelledby="titulo-atalhos-caixa">
            <header class="caixa-painel-topo"><h2 id="titulo-atalhos-caixa">Acesso rápido</h2></header>
            <ul class="caixa-atalhos">
                <?php foreach ($atalhos as [$chave, $rotulo, $icone, $caminho]): ?>
                    <?php if (!$administradorSimulado && !in_array($chave, $consultasPermitidas, true)) continue; ?>
                    <li><a href="<?= esc($base . $caminho, 'attr') ?>">
                        <span class="caixa-icone"><i class="fa-solid <?= esc($icone, 'attr') ?>" aria-hidden="true"></i></span>
                        <span><?= esc($rotulo) ?></span>
                        <i class="fa-solid fa-arrow-right caixa-seta" aria-hidden="true"></i>
                    </a></li>
                <?php endforeach ?>
            </ul>
        </aside>
    </div>

    <?php if ($acessoVinculo !== null): ?>
        <details class="caixa-painel caixa-vinculos">
            <summary>Meus setores e permissões</summary>
            <div class="grade-simulacao">
                <section aria-labelledby="titulo-setores-caixa">
                    <div class="cabecalho-painel"><h2 id="titulo-setores-caixa">Setores vinculados</h2></div>
                    <?php if (!$acessoVinculo['setores']): ?><p class="vazio-simulacao">Nenhum setor vinculado.</p><?php endif ?>
                    <ul class="lista-simulacao">
                        <?php foreach ($acessoVinculo['setores'] as $setor): ?>
                            <li><strong><?= esc($setor['setor']) ?></strong><small><?= esc($setor['unidade'] . ($setor['funcao'] ? ' · ' . $setor['funcao'] : '')) ?></small></li>
                        <?php endforeach ?>
                    </ul>
                </section>
                <section aria-labelledby="titulo-perfis-caixa">
                    <div class="cabecalho-painel"><h2 id="titulo-perfis-caixa">Perfis e permissões atribuídos</h2></div>
                    <?php if (!$acessoVinculo['perfis']): ?><p class="vazio-simulacao">Nenhum perfil atribuído.</p><?php endif ?>
                    <ul class="lista-simulacao">
                        <?php foreach ($acessoVinculo['perfis'] as $perfil): ?>
                            <?php $ambito = implode(' / ', array_filter([$perfil['unidade'], $perfil['setor']])); ?>
                            <li>
                                <strong><?= esc($perfil['perfil']) ?></strong>
                                <small><?= esc(($ambito ?: 'Organização inteira') . ' · ' . ucfirst($perfil['status'])) ?></small>
                                <?php foreach ($acessoVinculo['permissoes'][$perfil['id']] ?? [] as $permissao): ?>
                                    <small><?= esc($permissao['modulo'] . ' · ' . $permissao['descricao']) ?></small>
                                <?php endforeach ?>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </section>
            </div>
        </details>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
