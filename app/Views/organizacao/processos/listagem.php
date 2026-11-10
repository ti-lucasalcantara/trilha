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
if (!empty($consultaReal)) {
    $colunasConsulta = array_slice($colunasConsulta, 0, 2, true) + ['pasta' => 'Pasta'] + $colunasConsulta;
}
?>
<div class="ferramentas-colunas">
    <?php if (!empty($consultaReal) && $administradorSimulado): ?>
        <div class="acoes-lote-processos" id="acoes-lote-processos">
            <?php if (in_array('mover', $acoesLote, true)): ?>
                <button class="botao-filtros" type="button" data-acao-lote="mover" aria-haspopup="dialog" disabled <?= !$caminhosPastas ? 'data-lote-indisponivel title="Cadastre uma pasta no setor ativo"' : '' ?>><i class="fa-regular fa-folder" aria-hidden="true"></i>Mover para</button>
            <?php endif ?>
            <?php if (in_array('arquivar', $acoesLote, true)): ?>
                <button class="botao-filtros" type="button" data-acao-lote="arquivar" aria-haspopup="dialog" disabled><i class="fa-solid fa-box-archive" aria-hidden="true"></i>Arquivar</button>
            <?php endif ?>
            <?php if (in_array('excluir', $acoesLote, true)): ?>
                <button class="botao-filtros text-danger" type="button" data-acao-lote="excluir" aria-haspopup="dialog" disabled><i class="fa-regular fa-trash-can" aria-hidden="true"></i>Excluir</button>
            <?php endif ?>
            <?php if (in_array('restaurar', $acoesLote, true)): ?>
                <button class="botao-filtros" type="button" data-acao-lote="restaurar" aria-haspopup="dialog" disabled><i class="fa-solid fa-rotate-left" aria-hidden="true"></i>Restaurar selecionados</button>
            <?php endif ?>
            <span id="contagem-lote-processos" class="text-secondary" role="status">0 selecionados</span>
        </div>
    <?php endif ?>
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
        $enderecoProcesso = site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . ($processo['hash_publico'] ?? $processo['id']));
        $identificadorAcoes = 'acoes-processo-' . $processo['id'];
        $prazo = $processo['prazo'];
        $prazoVencido = $prazo !== '' && $processo['situacao'] !== 'Concluído' && $prazo < date('Y-m-d');
    ?>
    <tr data-registro="<?= esc((string) $processo['id'], 'attr') ?>" data-numero="<?= esc($processo['numero'], 'attr') ?>" data-assunto="<?= esc($processo['assunto'], 'attr') ?>" data-tipo="<?= esc($processo['tipo'], 'attr') ?>" data-situacao="<?= esc($processo['situacao'], 'attr') ?>" data-unidade="<?= esc($processo['unidade'], 'attr') ?>" data-setor="<?= esc($processo['setor'], 'attr') ?>" data-responsavel="<?= esc($processo['responsavel'], 'attr') ?>" data-acesso="<?= esc($processo['acesso'], 'attr') ?>" data-interessado="<?= esc($processo['interessado'], 'attr') ?>" data-etiqueta="<?= esc($processo['etiqueta'], 'attr') ?>" data-unidade-escopo="<?= esc((string) ($processo['unidadeEscopo'] ?? $processo['unidade']), 'attr') ?>" data-setor-escopo="<?= esc((string) ($processo['setorEscopo'] ?? $processo['setor']), 'attr') ?>" data-pastas-processo="<?= esc($processo['pastas'] ?? '', 'attr') ?>" data-etiquetas-processo="<?= esc(json_encode($processo['etiquetas'] ?? [$processo['etiqueta']], JSON_THROW_ON_ERROR), 'attr') ?>" data-grupo="<?= esc($processo['grupoPasta'], 'attr') ?>" data-pasta="<?= esc($processo['pasta'], 'attr') ?>" data-abertura="<?= esc($processo['abertura'], 'attr') ?>" data-atualizacao="<?= esc($processo['atualizacao'], 'attr') ?>" data-prazo="<?= esc($processo['prazo'], 'attr') ?>" data-acompanhado="<?= $processo['acompanhado'] ? 'true' : 'false' ?>" data-destinos="<?= esc(implode(', ', $processo['destinos']), 'attr') ?>">
        <td class="coluna-identificacao" data-coluna="processo">
            <?php if (!empty($consultaReal)): ?>
                <div class="identificacao-com-estrela">
                    <?php if ($administradorSimulado): ?>
                        <input class="form-check-input selecionar-processo" type="checkbox"
                            value="<?= esc($processo['hash_publico'], 'attr') ?>"
                            aria-label="<?= esc('Selecionar ' . $processo['numero'], 'attr') ?>">
                    <?php endif ?>
                    <?php if ($caixaProcessos === 'ativos'): ?><button type="button" class="estrela-processo"
                        data-acompanhamento="<?= esc($enderecoProcesso . '/acompanhamento', 'attr') ?>"
                        aria-pressed="<?= $processo['acompanhado'] ? 'true' : 'false' ?>"
                        aria-label="<?= esc('Acompanhar ' . $processo['numero'], 'attr') ?>"
                        title="<?= $processo['acompanhado'] ? 'Deixar de acompanhar' : 'Acompanhar' ?>">
                        <i class="<?= $processo['acompanhado'] ? 'fa-solid' : 'fa-regular' ?> fa-star" aria-hidden="true"></i>
                    </button><?php endif ?>
            <?php endif ?>
            <?php if (($caixaProcessos ?? 'ativos') === 'lixeira'): ?><div class="abrir-consulta"><?php else: ?><a class="abrir-consulta" href="<?= esc($enderecoProcesso, 'attr') ?>"><?php endif ?>
                <strong><?= esc($processo['assunto']) ?></strong>
                <span><?= esc($processo['numero']) ?></span>
            <?php if (($caixaProcessos ?? 'ativos') === 'lixeira'): ?></div><?php else: ?></a><?php endif ?>
            <?php if (!empty($consultaReal)): ?></div><?php endif ?>
        </td>
        <td data-coluna="setor"><?= esc(implode(', ', $processo['destinos'])) ?></td>
        <?php if (!empty($consultaReal)): ?>
            <?php $pastasLinha = array_intersect_key($caminhosPastas, array_flip(explode(',', $processo['pastas']))); ?>
            <td data-coluna="pasta">
                <?php $rotuloPasta = $pastasLinha ? implode(' · ', $pastasLinha) : 'Sem pasta'; ?>
                <?php if ($caixaProcessos === 'ativos' && $administradorSimulado && ($caminhosPastas || $pastasLinha)): ?>
                    <button type="button" class="botao-filtros pasta-na-lista"
                        data-mover-processo="<?= esc($enderecoProcesso . '/pasta', 'attr') ?>"
                        data-pastas-vinculadas="<?= esc(json_encode(array_keys($pastasLinha), JSON_THROW_ON_ERROR), 'attr') ?>"
                        aria-haspopup="dialog" aria-controls="dialogo-pasta-processo"
                        aria-label="<?= esc('Alterar pasta de ' . $processo['numero'] . ' · ' . $rotuloPasta, 'attr') ?>">
                        <i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($rotuloPasta) ?></span><i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                    </button>
                <?php else: ?>
                    <span class="text-secondary"><?= esc($rotuloPasta) ?></span>
                <?php endif ?>
            </td>
        <?php endif ?>
        <td data-coluna="situacao">
            <span class="selo-situacao <?= esc(($coresSituacao[$processo['situacao']] ?? 'neutro'), 'attr') ?>">
                <?php if (preg_match('/^#[0-9a-f]{6}$/iD', $processo['cor_situacao'] ?? '')): ?>
                    <span class="badge badge-dot me-1" style="background-color:<?= esc($processo['cor_situacao'], 'attr') ?>" aria-hidden="true"></span>
                <?php endif ?>
                <?= esc($processo['situacao']) ?>
            </span>
        </td>
        <td data-coluna="prazo" class="data-consulta<?= $prazoVencido ? ' prazo-vencido' : '' ?>">
            <?php if ($prazo !== ''): ?>
                <time datetime="<?= esc($prazo, 'attr') ?>"><?= esc(date('d/m/Y', strtotime($prazo))) ?></time>
                <?php if ($prazoVencido): ?><small>Vencido</small><?php endif ?>
            <?php else: ?>—<?php endif ?>
        </td>
        <td class="coluna-acoes" data-coluna="acoes">
            <button type="button" class="abrir-acoes" popovertarget="<?= esc($identificadorAcoes, 'attr') ?>" aria-label="Ações de <?= esc($processo['numero'], 'attr') ?>"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></button>
            <div id="<?= esc($identificadorAcoes, 'attr') ?>" class="acoes-consulta" popover>
                <?php if (($caixaProcessos ?? 'ativos') !== 'lixeira'): ?>
                    <a href="<?= esc($enderecoProcesso, 'attr') ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Abrir processo</a>
                <?php endif ?>
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
