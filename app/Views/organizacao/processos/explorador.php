<!-- Usa listas e detalhes nativos para permitir navegação por mouse e teclado. -->
<aside class="painel-explorador" aria-label="Explorador de processos">
    <div class="titulo-explorador"><span>Explorador</span><button id="alternar-explorador" type="button" aria-controls="arvore-explorador" aria-expanded="true" aria-label="Recolher explorador"><i class="fa-solid fa-angles-left" aria-hidden="true"></i></button></div>
    <nav id="arvore-explorador" class="arvore-explorador" aria-label="Navegação por unidades e pastas">
        <div class="atalhos-explorador">
            <button type="button" class="item-explorador selecionado" data-escopo="todos" data-rotulo="Todos os processos" aria-current="true"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Todos os processos</span></button>
            <button type="button" class="item-explorador" data-escopo="acompanhados" data-rotulo="Acompanhados"><i class="fa-regular fa-star" aria-hidden="true"></i><span>Acompanhados</span></button>
        </div>
        <div class="grupo-explorador"><details class="ramo-explorador"><summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-solid fa-sitemap" aria-hidden="true"></i><span>Unidades e setores</span></summary><ul>
            <?php foreach ($unidades as $unidade => $setores): ?>
            <li><details class="ramo-explorador"><summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($unidade) ?></span></summary><ul><li><button type="button" class="item-explorador" data-escopo="unidade" data-valor="<?= esc($unidade, 'attr') ?>" data-rotulo="<?= esc($unidade, 'attr') ?>"><i class="fa-solid fa-border-all" aria-hidden="true"></i><span>Toda a unidade</span></button></li>
                <?php foreach (array_keys($setores) as $setor): ?>
                <li><button type="button" class="item-explorador" data-escopo="setor" data-unidade="<?= esc($unidade, 'attr') ?>" data-valor="<?= esc($setor, 'attr') ?>" data-rotulo="<?= esc($unidade . ' / ' . $setor, 'attr') ?>"><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($setor) ?></span></button></li>
                <?php endforeach ?>
            </ul></details></li>
            <?php endforeach ?>
        </ul></details></div>
        <div class="secao-pastas">
            <details id="ramo-pastas" class="ramo-explorador">
                <summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-folder" aria-hidden="true"></i><span>Pastas</span></summary>
                <ul id="pastas-explorador" data-pastas="<?= esc(json_encode($pastas, JSON_THROW_ON_ERROR), 'attr') ?>"></ul>
            </details>
            <button type="button" class="controle-pasta adicionar-pasta" data-acao-pasta="criar" aria-label="Nova pasta" title="Nova pasta"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
        </div>
        <p id="aviso-pastas" class="aviso-pastas" role="status"></p>
    </nav>
    <div id="redimensionar-explorador" class="redimensionar-explorador" role="separator" tabindex="0"
         aria-label="Largura do Explorador" aria-orientation="vertical" aria-controls="arvore-explorador"
         aria-valuemin="180" aria-valuemax="480" aria-valuenow="208"
         title="Arraste ou use as setas para ajustar a largura"></div>
</aside>

<div id="aviso-movimento-pasta" class="aviso-desfazer" hidden>
    <span role="status">Pasta movida.</span>
    <button type="button" id="desfazer-movimento-pasta">Desfazer</button>
    <span class="progresso-desfazer" aria-hidden="true"></span>
</div>

<!-- O modelo é clonado para cada pasta, inclusive as subpastas criadas na prévia. -->
<template id="modelo-pasta">
    <li class="no-pasta">
        <div class="linha-pasta">
            <button type="button" class="controle-pasta expandir-pasta" aria-expanded="false" hidden><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button>
            <button type="button" class="item-explorador nome-pasta" draggable="true"><i class="fa-regular fa-folder" aria-hidden="true"></i><span></span></button>
            <button type="button" class="controle-pasta abrir-acoes-pasta"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></button>
            <div class="acoes-consulta acoes-pasta" popover>
                <button type="button" data-acao-pasta="subpasta">Criar subpasta</button>
                <button type="button" data-acao-pasta="renomear">Renomear</button>
                <button type="button" data-acao-pasta="visibilidade">Alterar visibilidade</button>
                <button type="button" data-acao-pasta="mover">Mover para…</button>
                <button type="button" data-acao-pasta="remover">Remover</button>
            </div>
        </div>
        <ul class="filhas-pasta" hidden></ul>
    </li>
</template>

<dialog id="dialogo-pasta" class="dialogo-colunas dialogo-pasta" aria-labelledby="titulo-pasta">
    <form method="dialog">
        <h2 id="titulo-pasta"></h2>
        <p id="contexto-pasta"></p>
        <label id="rotulo-nome-pasta">Nome da pasta<input name="nome" maxlength="100"></label>
        <label id="rotulo-escopo-pasta">Onde criar
            <select name="local">
                <option value="">Minhas pastas</option>
                <?php foreach ($unidades as $unidade => $setores): ?>
                    <optgroup label="<?= esc($unidade, 'attr') ?>">
                        <?php foreach (array_keys($setores) as $setor): ?>
                            <option><?= esc($unidade . ' / ' . $setor) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endforeach ?>
            </select>
        </label>
        <label id="rotulo-visibilidade-pasta">Visibilidade
            <select name="visibilidade">
                <option value="">Privada · somente eu</option>
                <?php foreach ($unidades as $unidade => $setores): ?>
                    <optgroup label="<?= esc($unidade, 'attr') ?>">
                        <?php foreach (array_keys($setores) as $setor): ?>
                            <option value="<?= esc($unidade . ' / ' . $setor, 'attr') ?>">Compartilhar com <?= esc($setor) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endforeach ?>
            </select>
        </label>
        <fieldset id="destinos-pasta" class="destinos-pasta" hidden>
            <legend>Destino da pasta</legend>
            <p>Escolha a raiz ou uma pasta do mesmo local.</p>
            <div id="arvore-destinos-pasta"></div>
        </fieldset>
        <p id="erro-pasta" class="erro-pasta" role="alert" hidden></p>
        <footer class="rodape-colunas">
            <button type="submit" value="cancelar" formnovalidate class="botao-filtros">Cancelar</button>
            <button type="submit" value="salvar" class="btn btn-primary" id="confirmar-pasta"></button>
        </footer>
    </form>
</dialog>
