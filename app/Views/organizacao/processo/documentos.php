<?php
$iconesFormatos = ['pdf' => 'fa-file-pdf', 'xlsx' => 'fa-file-excel', 'xls' => 'fa-file-excel', 'jpg' => 'fa-file-image', 'png' => 'fa-file-image', 'docx' => 'fa-file-word'];
?>
<aside class="painel-documentos" aria-labelledby="titulo-documentos">
    <div class="topo-documentos">
        <h2 id="titulo-documentos">Documentos<span id="quantidade-documentos"><?= esc((string) $quantidadeDocumentosAtivos) ?></span></h2>
        <div class="acoes-lista-documentos">
            <?php if ($administradorSimulado): ?>
                <button type="button" class="acao-documento acao-suave" id="adicionar-documento" aria-haspopup="dialog" aria-controls="dialogo-adicionar-documento"><i class="fa-solid fa-plus" aria-hidden="true"></i>Adicionar</button>
            <?php endif ?>
        </div>
    </div>
    <div id="corpo-documentos">
        <label class="pesquisa-documentos"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visualmente-oculto">Pesquisar documento por nome, formato ou número</span><input type="search" id="pesquisa-documentos" placeholder="Buscar documento…" autocomplete="off"></label>
        <?php if ($quantidadeDocumentosAtivos < count($documentos)): ?>
            <label class="filtro-documentos-excluidos"><input type="checkbox" id="mostrar-documentos-excluidos" autocomplete="off"> Mostrar excluídos</label>
        <?php endif ?>
        <!-- O foco permite rolar a lista pelo teclado sem mover o visualizador. -->
        <div class="lista-documentos" role="region" aria-label="Lista de arquivos do processo" tabindex="0">
            <?php foreach ($documentos as $documento): ?>
            <?php $selecionado = $documento['id'] === ($documentoAtivo['id'] ?? null); ?>
            <article class="documento-compacto <?= $selecionado ? 'selecionado' : '' ?> <?= $documento['excluido'] ? 'documento-excluido' : '' ?>"
                <?= $documento['excluido'] ? 'hidden' : '' ?>
                data-documento="<?= esc($documento['id'], 'attr') ?>"
                data-excluido="<?= $documento['excluido'] ? '1' : '0' ?>"
                data-pode-excluir="<?= $documento['podeExcluir'] ? '1' : '0' ?>"
                data-numero="<?= esc($documento['numero'], 'attr') ?>"
                data-titulo="<?= esc($documento['titulo'], 'attr') ?>"
                data-arquivo="<?= esc($documento['nomeArquivo'], 'attr') ?>"
                data-download="<?= esc($documento['download'], 'attr') ?>"
                data-pdf="<?= esc($documento['pdf'], 'attr') ?>"
                data-leitura="<?= esc($documento['leitura'], 'attr') ?>"
                data-imagem="<?= esc($documento['imagem'], 'attr') ?>"
                data-formato="<?= esc($documento['formato'], 'attr') ?>"
                data-tipo="<?= esc($documento['tipo'], 'attr') ?>"
                data-autor="<?= esc($documento['autor'], 'attr') ?>"
                data-setor="<?= esc($documento['setor'], 'attr') ?>"
                data-data="<?= esc($documento['data'], 'attr') ?>"
                data-situacao="<?= esc($documento['situacao'], 'attr') ?>"
                data-acesso="<?= esc($documento['acesso'], 'attr') ?>"
                data-versao="<?= esc($documento['versao'], 'attr') ?>"
                data-descricao="<?= esc($documento['descricao'], 'attr') ?>"
                data-cor="<?= esc($documento['cor'], 'attr') ?>">
                <div class="linha-documento">
                    <button type="button" class="selecionar-documento" aria-pressed="<?= $selecionado ? 'true' : 'false' ?>" aria-controls="area-visualizador" aria-label="Visualizar <?= esc($documento['titulo'], 'attr') ?>" title="<?= esc($documento['titulo'], 'attr') ?>">
                        <span class="icone-formato <?= esc($documento['formato'], 'attr') ?>"><i class="fa-regular <?= esc($iconesFormatos[$documento['formato']] ?? 'fa-file', 'attr') ?>" aria-hidden="true"></i></span>
                        <span class="identificacao-arquivo">
                            <span class="nome-arquivo"><span><?= esc($documento['titulo']) ?></span></span>
                            <span class="inclusao-arquivo"><?= esc($documento['tipo'] . ' · ' . $documento['numero']) ?></span>
                            <span class="inclusao-arquivo"><?= esc($documento['data']) ?></span>
                            <?php if ($documento['excluido']): ?><span class="estado-documento vermelho"><span aria-hidden="true"></span>Excluído</span><?php endif ?>
                        </span>
                    </button>
                    <button type="button" class="alternar-detalhes-documento" aria-expanded="false" aria-controls="detalhes-documento-<?= esc($documento['id'], 'attr') ?>" aria-label="Expandir detalhes de <?= esc($documento['titulo'], 'attr') ?>"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
                </div>
                <div class="detalhes-documento" id="detalhes-documento-<?= esc($documento['id'], 'attr') ?>" hidden>
                    <p><?= esc($documento['descricao']) ?></p>
                    <dl>
                        <div><dt>Número</dt><dd><?= esc($documento['numero']) ?></dd></div>
                        <div><dt>Tipo</dt><dd><?= esc($documento['tipo']) ?></dd></div>
                        <div><dt>Autor</dt><dd><?= esc($documento['autor']) ?></dd></div>
                        <div><dt>Setor</dt><dd><?= esc($documento['setor']) ?></dd></div>
                        <div><dt>Inclusão</dt><dd><?= esc($documento['data']) ?></dd></div>
                        <div><dt>Acesso</dt><dd><?= esc($documento['acesso']) ?></dd></div>
                        <div><dt>Versão</dt><dd><?= esc($documento['versao']) ?></dd></div>
                        <div><dt>Situação</dt><dd><span class="estado-documento <?= esc($documento['cor'], 'attr') ?>"><span></span><?= esc($documento['situacao']) ?></span></dd></div>
                    </dl>
                </div>
            </article>
            <?php endforeach ?>
        </div>
        <p id="documentos-vazios" class="documentos-vazios" role="status" <?= $quantidadeDocumentosAtivos ? 'hidden' : '' ?>>Nenhum documento disponível.</p>
    </div>
</aside>
