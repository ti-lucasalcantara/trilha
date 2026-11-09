<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<?php
// A cota exibida vem da versão associada ao contrato, inclusive em planos já revisados.
$enderecoEmpresa = site_url('admin/clientes/' . $empresa['id']);
$formatarGb = static fn (?float $valor): string => $valor === null ? '—' : ($valor > 0 && $valor < 0.01 ? '< 0,01 GB' : number_format($valor, 2, ',', '.') . ' GB');
$percentual = $armazenamento['cota'] > 0 ? min(100, $armazenamento['utilizado'] / $armazenamento['cota'] * 100) : null;
?>
<div class="barra-consulta-admin">
    <a class="botao-admin secundario" href="<?= esc(site_url('admin/clientes'), 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Clientes</a>
    <a class="botao-admin secundario" href="<?= esc($enderecoEmpresa . '/editar', 'attr') ?>"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Editar empresa</a>
</div>
<div class="metricas-admin" aria-label="Armazenamento da empresa">
    <article><span>Plano contratado</span><strong><?= esc($armazenamento['plano'] ?? 'Sem plano') ?></strong></article>
    <article><span>Cota do plano</span><strong><?= esc($formatarGb($armazenamento['cota'])) ?></strong></article>
    <article><span>Em uso</span><strong><?= esc($formatarGb($armazenamento['utilizado'])) ?></strong></article>
    <article><span>Disponível</span><strong><?= esc($formatarGb($armazenamento['disponivel'])) ?></strong></article>
</div>
<section class="cartao-admin" aria-label="Uso de armazenamento">
    <header>
        <h2>Armazenamento</h2>
        <p><?= $armazenamento['cota'] === null ? 'Sem cota configurada no plano contratado.' : 'O uso é calculado pelos tamanhos dos arquivos registrados para esta empresa.' ?></p>
        <?php if ($percentual !== null): ?><progress class="progresso-admin" value="<?= esc((string) $percentual, 'attr') ?>" max="100" aria-label="Percentual da cota em uso"></progress><?php endif ?>
        <?php if ($armazenamento['excedente'] > 0): ?><p class="excedente-admin">Excedente de <?= esc($formatarGb($armazenamento['excedente'])) ?>.</p><?php endif ?>
    </header>
</section>

<section class="cartao-admin" aria-labelledby="titulo-simulacao-admin">
    <header>
        <h2 id="titulo-simulacao-admin">Simular como usuário</h2>
        <p>Confira os setores, perfis e permissões atribuídos ao vínculo selecionado.</p>
        <?php if (!$usuarios): ?>
            <p class="nota-admin">Nenhum usuário interno ativo vinculado a esta empresa.</p>
        <?php else: ?>
            <form class="barra-consulta-admin interna" method="get" action="<?= esc($enderecoEmpresa . '/simular', 'attr') ?>">
                <label for="usuario-simulacao-admin">Usuário
                    <select id="usuario-simulacao-admin" name="usuario" required>
                        <option value="">Selecione um usuário</option>
                        <?php foreach ($usuarios as $usuario): ?>
                            <option value="<?= esc((string) $usuario['id'], 'attr') ?>"><?= esc($usuario['nome'] . ' · ' . $usuario['email']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <button type="submit" class="botao-admin principal">Abrir simulação</button>
            </form>
        <?php endif ?>
    </header>
</section>

<section class="cartao-admin" id="inventario-admin" aria-labelledby="titulo-inventario-admin">
    <header>
        <h2 id="titulo-inventario-admin">Inventário da empresa</h2>
        <p>Consulte registros do banco por categoria. A lista de documentos e arquivos exibe metadados.</p>
        <form class="barra-consulta-admin interna" method="get" action="<?= esc($enderecoEmpresa . '#inventario-admin', 'attr') ?>">
            <label for="categoria-inventario-admin">Categoria
                <select name="categoria" id="categoria-inventario-admin">
                    <?php foreach ($categorias as $chave => $rotulo): ?>
                        <option value="<?= esc($chave, 'attr') ?>" <?= $chave === $categoria ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
            <button type="submit" class="botao-admin secundario">Consultar</button>
        </form>
    </header>
    <?php if (!$registros): ?>
        <p class="estado-lista-admin">Nenhum registro nesta categoria.</p>
    <?php else: ?>
        <div class="tabela-admin" tabindex="0" role="region" aria-label="<?= esc($categorias[$categoria], 'attr') ?>">
            <table>
                <thead><tr><?php foreach ($colunas as $rotulo): ?><th scope="col"><?= esc($rotulo) ?></th><?php endforeach ?></tr></thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr><?php foreach ($colunas as $campo => $rotulo): ?><td><?= esc((string) ($registro[$campo] ?? '—')) ?></td><?php endforeach ?></tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
        <nav class="paginacao-admin" aria-label="Paginação do inventário">
            <span><?= (int) $total ?> registro(s) · Página <?= (int) $pagina ?> de <?= (int) $paginas ?></span>
            <?php if ($pagina > 1): ?><a class="botao-admin secundario" href="<?= esc($enderecoEmpresa . '?' . http_build_query(['categoria' => $categoria, 'pagina' => $pagina - 1]) . '#inventario-admin', 'attr') ?>">Anterior</a><?php endif ?>
            <?php if ($pagina < $paginas): ?><a class="botao-admin secundario" href="<?= esc($enderecoEmpresa . '?' . http_build_query(['categoria' => $categoria, 'pagina' => $pagina + 1]) . '#inventario-admin', 'attr') ?>">Próxima</a><?php endif ?>
        </nav>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
