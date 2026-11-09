<!-- Usa listas e detalhes nativos para permitir navegação por mouse e teclado. -->
<aside class="painel-explorador" aria-label="Explorador de processos">
    <div class="titulo-explorador"><span>Explorador</span><button id="alternar-explorador" type="button" aria-controls="arvore-explorador" aria-expanded="true" aria-label="Recolher explorador"><i class="fa-solid fa-angles-left" aria-hidden="true"></i></button></div>
    <nav id="arvore-explorador" class="arvore-explorador" aria-label="Navegação por unidades e pastas">
        <div class="atalhos-explorador">
            <button type="button" class="item-explorador selecionado" data-escopo="todos" data-rotulo="Todos os processos" aria-current="true"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Todos os processos</span></button>
            <?php if ($mostrarAcompanhados ?? true): ?>
            <button type="button" class="item-explorador" data-escopo="acompanhados" data-rotulo="Acompanhados"><i class="fa-regular fa-star" aria-hidden="true"></i><span>Acompanhados</span></button>
            <?php endif ?>
        </div>
        <div class="secao-pastas">
            <details id="ramo-pastas" class="ramo-explorador" data-pasta-preferencia="raiz" <?= !empty($pastaRealCriada) ? 'open data-pasta-recem-criada' : '' ?>>
                <summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-folder" aria-hidden="true"></i><span>Pastas</span></summary>
                <?php if (!empty($consultaReal)): ?>
                    <ul id="pastas-explorador" data-pastas-reais="<?= esc(json_encode($pastasReais, JSON_THROW_ON_ERROR), 'attr') ?>"></ul>
                    <p id="pastas-reais-vazias" class="nota-pastas-reais" <?= $pastasReais ? 'hidden' : '' ?>>Nenhuma pasta cadastrada.</p>
                <?php else: ?>
                    <ul id="pastas-explorador" data-pastas="<?= esc(json_encode($pastas, JSON_THROW_ON_ERROR), 'attr') ?>"></ul>
                <?php endif ?>
            </details>
            <?php if (empty($consultaReal)): ?>
            <button type="button" class="controle-pasta adicionar-pasta" data-acao-pasta="criar" aria-label="Nova pasta" title="Nova pasta"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
            <?php else: ?>
            <button type="button" id="abrir-pasta-real" class="controle-pasta adicionar-pasta" aria-haspopup="dialog" aria-controls="dialogo-pasta-real" aria-label="Nova pasta" title="Nova pasta"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
            <?php endif ?>
        </div>
        <p id="aviso-pastas" class="aviso-pastas" role="status"></p>
    </nav>
    <div id="redimensionar-explorador" class="redimensionar-explorador" role="separator" tabindex="0"
         aria-label="Largura do Explorador" aria-orientation="vertical" aria-controls="arvore-explorador"
         aria-valuemin="180" aria-valuemax="480" aria-valuenow="208"
         title="Arraste ou use as setas para ajustar a largura"></div>
</aside>

<div id="aviso-movimento-pasta" class="aviso-desfazer" data-historico="<?= esc(json_encode($historicoPasta ?? null), 'attr') ?>" hidden>
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

<dialog id="dialogo-pasta" class="dialogo-colunas dialogo-pasta" aria-labelledby="titulo-pasta"
    <?php if (!empty($consultaReal)): ?>
        data-endereco="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/pastas/acao'), 'attr') ?>"
        data-csrf-nome="<?= esc(csrf_token(), 'attr') ?>" data-csrf-valor="<?= esc(csrf_hash(), 'attr') ?>"
        data-setor="<?= esc($contextoTrabalho['setor']['id'] ?? '', 'attr') ?>"
    <?php endif ?>>
    <form method="dialog">
        <h2 id="titulo-pasta"></h2>
        <p id="contexto-pasta"></p>
        <label id="rotulo-nome-pasta">Nome da pasta<input name="nome" maxlength="<?= empty($consultaReal) ? 100 : 200 ?>"></label>
        <label id="rotulo-visibilidade-pasta">Visibilidade
            <select name="visibilidade">
                <?php if (!empty($consultaReal)): ?>
                    <option value="setor">Compartilhada com o setor ativo</option>
                    <option value="privada">Privada · visível ao criador</option>
                <?php else: ?>
                <option value="">Privada · somente eu</option>
                <?php foreach ($unidades as $unidade => $setores): $rotuloUnidade = $rotulosUnidades[$unidade] ?? (string) $unidade; ?>
                    <optgroup label="<?= esc((string) $unidade, 'attr') ?>">
                        <?php foreach ($setores as $setor => $nomeSetor): $rotuloSetor = is_string($nomeSetor) ? $nomeSetor : (string) $setor; ?>
                            <option value="<?= esc($rotuloUnidade . ' / ' . $rotuloSetor, 'attr') ?>">Compartilhar com <?= esc($setor) ?></option>
                        <?php endforeach ?>
                    </optgroup>
                <?php endforeach ?>
                <?php endif ?>
            </select>
        </label>
        <?php if (!empty($consultaReal) && $modoSimulacao): ?>
            <label id="rotulo-responsavel-subpasta" hidden>Registrar em nome de
                <select name="responsavel">
                    <option value="">Selecione um usuário interno</option>
                    <?php foreach ($responsaveisPasta as $usuario): ?>
                        <option value="<?= esc((string) $usuario['vinculo_id'], 'attr') ?>"><?= esc($usuario['nome'] . ' · ' . $usuario['email']) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
        <?php endif ?>
        <fieldset id="destinos-pasta" class="destinos-pasta" hidden>
            <legend>Destino da pasta</legend>
            <p>Escolha a raiz ou uma pasta do mesmo local.</p>
            <div id="arvore-destinos-pasta"></div>
        </fieldset>
        <p id="erro-pasta" class="erro-pasta" role="alert" hidden></p>
        <footer class="rodape-colunas">
            <button type="button" value="cancelar" data-cancelar-dialogo class="botao-filtros">Cancelar</button>
            <button type="submit" value="salvar" class="btn btn-primary" id="confirmar-pasta"></button>
        </footer>
    </form>
</dialog>
