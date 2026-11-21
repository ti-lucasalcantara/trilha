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
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/estrutura', 'attr') ?>"><i class="fa-regular fa-building" aria-hidden="true"></i>Estrutura</a>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/equipe', 'attr') ?>"><i class="fa-solid fa-users" aria-hidden="true"></i>Equipe</a>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/perfis', 'attr') ?>"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Perfis e permissões</a>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/tipos-processo', 'attr') ?>"><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Tipos de processo</a>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/situacoes-processo', 'attr') ?>"><i class="fa-solid fa-list-check" aria-hidden="true"></i>Situações de processo</a>
                    <a class="opcao-menu" href="<?= esc($enderecoConfiguracoes . '/tipos-documento', 'attr') ?>"><i class="fa-regular fa-folder-open" aria-hidden="true"></i>Tipos de Documentos</a>
                    <?php if ($modoSimulacao): ?><a class="opcao-menu" href="<?= esc(site_url('admin/clientes/' . $empresaSimulada . '/editar'), 'attr') ?>"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Editar dados da empresa</a><?php endif ?>
                </div>
            </details><?php endif ?>
        </nav>
    </div>
</div>
