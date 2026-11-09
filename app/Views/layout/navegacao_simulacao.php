<?php
// Usa o mesmo endereço da organização para manter o contexto protegido da sessão.
$enderecoSimulado = site_url('organizacao/' . $identificadorOrganizacao . '/dashboard');
$enderecoConfiguracoes = site_url('organizacao/' . $identificadorOrganizacao . '/configuracoes');
$menusSimulados = [
    'dashboard' => ['Caixa de trabalho', 'fa-inbox'],
    'processos' => ['Processos', 'fa-code-branch'],
    'documentos' => ['Documentos', 'fa-file-lines'],
    'assinaturas' => ['Assinaturas', 'fa-signature'],
    'pastas' => ['Pastas', 'fa-folder'],
];
if (!$administradorSimulado) $menusSimulados += ['unidades' => ['Unidades', 'fa-building'], 'setores' => ['Setores', 'fa-sitemap']];
?>
<div class="barra-navegacao">
    <div class="limite-pagina linha-navegacao">
        <button class="alternar-menu" type="button" aria-expanded="false" aria-controls="navegacao-principal"><i class="fa-solid fa-bars" aria-hidden="true"></i>Menu da organização</button>
        <nav id="navegacao-principal" class="navegacao-principal" aria-label="Menu da organização">
            <?php foreach ($menusSimulados as $chave => [$rotulo, $icone]): ?>
                <?php if (!$administradorSimulado && $chave !== 'dashboard' && !in_array($chave, $consultasPermitidas, true)) continue; ?>
                <?php $destino = $chave === 'processos' ? site_url('organizacao/' . $identificadorOrganizacao . '/processos') : $enderecoSimulado . ($chave === 'dashboard' ? '' : '?modulo=' . $chave); ?>
                <a class="item-navegacao <?= $paginaAtiva === $chave ? 'ativo' : '' ?>" <?= $paginaAtiva === $chave ? 'aria-current="page"' : '' ?> href="<?= esc($destino, 'attr') ?>"><i class="fa-solid <?= esc($icone, 'attr') ?>" aria-hidden="true"></i><?= esc($rotulo) ?></a>
            <?php endforeach ?>
            <?php if ($administradorSimulado): ?><details class="menu-suspenso menu-configuracoes">
                <summary class="item-navegacao <?= $paginaAtiva === 'configuracoes' ? 'ativo' : '' ?>"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Configurações<i class="fa-solid fa-chevron-down seta-menu" aria-hidden="true"></i></summary>
                <div class="conteudo-menu">
                    <span class="rotulo-menu">ADMINISTRAÇÃO DA ORGANIZAÇÃO</span>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes, 'attr') ?>#estrutura"><i class="fa-solid fa-users" aria-hidden="true"></i>Estrutura e equipe</a>
                    <button type="button" class="opcao-menu" data-aviso="Tipos, campos, status e numeração serão configurados em telas próprias."><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Processos e documentos</button>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes, 'attr') ?>#permissoes"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Perfis e permissões</a>
                    <?php if ($modoSimulacao): ?><a class="opcao-menu" href="<?= esc(site_url('admin/clientes/' . $empresaSimulada . '/editar'), 'attr') ?>"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Editar dados da empresa</a><?php endif ?>
                </div>
            </details><?php endif ?>
        </nav>
        <span class="identificacao-previa"><span></span><?= $modoSimulacao ? 'Simulação' : 'Área da organização' ?></span>
    </div>
</div>
