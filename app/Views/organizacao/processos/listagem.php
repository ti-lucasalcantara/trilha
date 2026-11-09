<?php
// As cores identificam apenas as situações fictícias desta demonstração.
$coresSituacao = ['Aberto' => 'azul', 'Em análise' => 'laranja', 'Em andamento' => 'roxo', 'Concluído' => 'verde', 'Suspenso' => 'neutro'];
// Usa os mesmos rótulos no cabeçalho e no seletor de colunas.
$colunasConsulta = [
    'processo' => 'Processo',
    'setor' => 'Setor atual',
    'situacao' => 'Situação',
    'prazo' => 'Prazo',
    'acoes' => 'Ações',
];
?>
<div class="ferramentas-colunas">
    <button type="button" id="abrir-colunas" class="botao-filtros" aria-haspopup="dialog" aria-controls="dialogo-colunas">
        <i class="fa-solid fa-table-columns" aria-hidden="true"></i>Colunas
    </button>
</div>
<dialog id="dialogo-colunas" class="dialogo-colunas" aria-labelledby="titulo-colunas">
    <form method="dialog">
        <h2 id="titulo-colunas">Colunas da tabela</h2>
        <p>Marque as colunas que deseja exibir.</p>
        <div class="opcoes-colunas">
            <?php foreach ($colunasConsulta as $coluna => $rotulo): ?>
                <label><input type="checkbox" name="colunas" value="<?= esc($coluna, 'attr') ?>" checked><?= esc($rotulo) ?></label>
            <?php endforeach ?>
        </div>
        <p id="aviso-colunas" role="alert" hidden>Selecione pelo menos uma coluna.</p>
        <footer class="rodape-colunas">
            <button type="button" data-cancelar-dialogo class="botao-filtros">Cancelar</button>
            <button type="submit" value="aplicar" class="btn btn-primary">Aplicar</button>
        </footer>
    </form>
</dialog>
<div class="cartao-consulta"><div class="tabela-consulta-responsiva" tabindex="0" role="region" aria-label="Lista de processos"><table class="tabela-consulta">
<thead><tr>
    <?php foreach ($colunasConsulta as $coluna => $rotulo): ?>
        <th scope="col" data-coluna="<?= esc($coluna, 'attr') ?>"><span<?= $coluna === 'acoes' ? ' class="visualmente-oculto"' : '' ?>><?= esc($rotulo) ?></span></th>
    <?php endforeach ?>
</tr></thead><tbody id="corpo-consulta">
    <?php foreach ($processos as $processo):
        $enderecoProcesso = site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . $processo['id']);
        $identificadorAcoes = 'acoes-processo-' . $processo['id'];
        $prazo = $processo['prazo'];
        $prazoVencido = $prazo !== '' && $processo['situacao'] !== 'Concluído' && $prazo < date('Y-m-d');
    ?>
    <tr data-registro="<?= esc((string) $processo['id'], 'attr') ?>" data-numero="<?= esc($processo['numero'], 'attr') ?>" data-assunto="<?= esc($processo['assunto'], 'attr') ?>" data-tipo="<?= esc($processo['tipo'], 'attr') ?>" data-situacao="<?= esc($processo['situacao'], 'attr') ?>" data-unidade="<?= esc($processo['unidade'], 'attr') ?>" data-setor="<?= esc($processo['setor'], 'attr') ?>" data-responsavel="<?= esc($processo['responsavel'], 'attr') ?>" data-acesso="<?= esc($processo['acesso'], 'attr') ?>" data-interessado="<?= esc($processo['interessado'], 'attr') ?>" data-etiqueta="<?= esc($processo['etiqueta'], 'attr') ?>" data-unidade-escopo="<?= esc((string) ($processo['unidadeEscopo'] ?? $processo['unidade']), 'attr') ?>" data-setor-escopo="<?= esc((string) ($processo['setorEscopo'] ?? $processo['setor']), 'attr') ?>" data-pastas-processo="<?= esc($processo['pastas'] ?? '', 'attr') ?>" data-etiquetas-processo="<?= esc(json_encode($processo['etiquetas'] ?? [$processo['etiqueta']], JSON_THROW_ON_ERROR), 'attr') ?>" data-grupo="<?= esc($processo['grupoPasta'], 'attr') ?>" data-pasta="<?= esc($processo['pasta'], 'attr') ?>" data-abertura="<?= esc($processo['abertura'], 'attr') ?>" data-atualizacao="<?= esc($processo['atualizacao'], 'attr') ?>" data-prazo="<?= esc($processo['prazo'], 'attr') ?>" data-acompanhado="<?= $processo['acompanhado'] ? 'true' : 'false' ?>" data-destinos="<?= esc(implode(', ', $processo['destinos']), 'attr') ?>">
        <td class="coluna-identificacao" data-coluna="processo">
            <a class="abrir-consulta" href="<?= esc($enderecoProcesso, 'attr') ?>">
                <strong><?= esc($processo['assunto']) ?></strong>
                <span><?= esc($processo['numero']) ?></span>
            </a>
        </td>
        <td data-coluna="setor"><?= esc(implode(', ', $processo['destinos'])) ?></td>
        <td data-coluna="situacao"><span class="selo-situacao <?= esc(($coresSituacao[$processo['situacao']] ?? 'neutro'), 'attr') ?>"><?= esc($processo['situacao']) ?></span></td>
        <td data-coluna="prazo" class="data-consulta<?= $prazoVencido ? ' prazo-vencido' : '' ?>">
            <?php if ($prazo !== ''): ?>
                <time datetime="<?= esc($prazo, 'attr') ?>"><?= esc(date('d/m/Y', strtotime($prazo))) ?></time>
                <?php if ($prazoVencido): ?><small>Vencido</small><?php endif ?>
            <?php else: ?>—<?php endif ?>
        </td>
        <td class="coluna-acoes" data-coluna="acoes">
            <button type="button" class="abrir-acoes" popovertarget="<?= esc($identificadorAcoes, 'attr') ?>" aria-label="Ações de <?= esc($processo['numero'], 'attr') ?>"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></button>
            <div id="<?= esc($identificadorAcoes, 'attr') ?>" class="acoes-consulta" popover>
                <a href="<?= esc($enderecoProcesso, 'attr') ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Abrir processo</a>
                <?php if (empty($consultaReal)): ?>
                <button type="button" class="acompanhar-processo"><i class="<?= $processo['acompanhado'] ? 'fa-solid' : 'fa-regular' ?> fa-star" aria-hidden="true"></i><span><?= $processo['acompanhado'] ? 'Deixar de acompanhar' : 'Acompanhar' ?></span></button>
                <?php endif ?>
                <button type="button" class="copiar-numero"><i class="fa-regular fa-copy" aria-hidden="true"></i>Copiar número</button>
            </div>
        </td>
    </tr>
    <?php endforeach ?>
</tbody></table></div>
<div id="consulta-vazia" class="estado-vazio" hidden><i class="fa-regular fa-folder-open" aria-hidden="true"></i><h3>Nenhum processo nesta visualização</h3><p>Escolha outra pasta, ajuste a pesquisa ou limpe os filtros.</p><button type="button" data-limpar-consulta>Limpar tudo</button></div>
<!-- A paginação é local nesta etapa; não representa uma consulta ao banco. -->
<footer class="paginacao-consulta"><div class="resumo-paginacao"><span id="resumo-resultados" role="status" aria-live="polite"></span><label>Por página<select id="itens-por-pagina"><option>5</option><option selected>10</option><option>20</option></select></label></div><nav class="controles-paginacao" aria-label="Páginas de processos"><button type="button" id="pagina-anterior" aria-label="Página anterior"><i class="fa-solid fa-chevron-left" aria-hidden="true"></i></button><div id="numeros-paginas"></div><button type="button" id="pagina-seguinte" aria-label="Próxima página"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button></nav></footer>
</div>
