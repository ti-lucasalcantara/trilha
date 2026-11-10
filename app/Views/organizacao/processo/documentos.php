<?php
// Os formatos indicam os arquivos de origem; a prévia continua usando o PDF de teste.
$iconesFormatos = ['pdf' => 'fa-file-pdf', 'xlsx' => 'fa-file-excel', 'xls' => 'fa-file-excel', 'jpg' => 'fa-file-image', 'png' => 'fa-file-image', 'docx' => 'fa-file-word'];
?>
<aside class="painel-documentos" aria-labelledby="titulo-documentos">
    <div class="topo-documentos">
        <h2 id="titulo-documentos">Documentos<span><?= esc((string) count($documentos)) ?></span></h2>
        <div class="acoes-lista-documentos">
            <button type="button" class="acao-documento acao-suave" data-acao="adicionar" data-ambito="processo"><i class="fa-solid fa-plus" aria-hidden="true"></i>Adicionar</button>
            <button type="button" id="alternar-documentos" class="acao-documento acao-icone" aria-expanded="true" aria-controls="corpo-documentos" aria-label="Recolher lista de documentos"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button>
        </div>
    </div>
    <div id="corpo-documentos">
        <label class="pesquisa-documentos"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visualmente-oculto">Pesquisar documento por nome, formato ou número</span><input type="search" id="pesquisa-documentos" placeholder="Buscar documento…" autocomplete="off"></label>
        <!-- O foco permite rolar a lista pelo teclado sem mover o visualizador. -->
        <div class="lista-documentos" role="region" aria-label="Lista de arquivos do processo" tabindex="0">
            <?php foreach ($documentos as $indice => $documento): ?>
            <article class="documento-compacto <?= $indice === 0 ? 'selecionado' : '' ?>"
                data-documento="<?= esc($documento['id'], 'attr') ?>"
                data-numero="<?= esc($documento['numero'], 'attr') ?>"
                data-titulo="<?= esc($documento['titulo'], 'attr') ?>"
                data-arquivo="<?= esc($documento['nomeArquivo'], 'attr') ?>"
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
                    <button type="button" class="selecionar-documento" aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>" aria-controls="area-visualizador" aria-label="Visualizar <?= esc($documento['nomeArquivo'], 'attr') ?>" title="<?= esc($documento['nomeArquivo'], 'attr') ?>">
                        <span class="icone-formato <?= esc($documento['formato'], 'attr') ?>"><i class="fa-regular <?= esc($iconesFormatos[$documento['formato']] ?? 'fa-file', 'attr') ?>" aria-hidden="true"></i></span>
                        <span class="identificacao-arquivo">
                            <span class="nome-arquivo"><?php if (!empty($consultaReal)): ?><span><?= esc($documento['nomeArquivo']) ?></span><?php else: ?><span><?= esc($documento['titulo']) ?></span><span>.<?= esc($documento['formato']) ?></span><?php endif ?></span>
                            <span class="inclusao-arquivo"><?= esc($documento['data']) ?></span>
                        </span>
                    </button>
                    <button type="button" class="alternar-detalhes-documento" aria-expanded="false" aria-controls="detalhes-documento-<?= esc($documento['id'], 'attr') ?>" aria-label="Expandir detalhes de <?= esc($documento['nomeArquivo'], 'attr') ?>"><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
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
        <p id="documentos-vazios" class="documentos-vazios" role="status" <?= $documentos ? 'hidden' : '' ?>><?= $documentos ? 'Nenhum documento encontrado. Tente outro termo.' : 'Nenhum documento disponível.' ?></p>
    </div>
    <div id="redimensionar-documentos" class="redimensionar-documentos" role="separator" tabindex="0"
         aria-label="Largura da lista de documentos" aria-orientation="vertical" aria-controls="corpo-documentos"
         aria-valuemin="220" aria-valuemax="520" aria-valuenow="248"
         title="Arraste ou use as setas para ajustar a largura"></div>
</aside>
