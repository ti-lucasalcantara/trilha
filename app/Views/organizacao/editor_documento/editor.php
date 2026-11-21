<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/editor-documento.css'), 'attr') ?>">
<?= $this->endSection() ?>
<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/editor-documento.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<?php
$estadoEditor = $erro !== '' ? 'Alterações não salvas' : ($dados['revisao'] !== '' ? 'Rascunho salvo' : 'Novo documento');
$comandosEditor = [
    [
        ['undo', 'rotate-left', 'Desfazer', 'Ctrl+Z'],
        ['redo', 'rotate-right', 'Refazer', 'Ctrl+Shift+Z'],
    ],
    [
        ['bold', 'bold', 'Negrito', 'Ctrl+B'],
        ['italic', 'italic', 'Itálico', 'Ctrl+I'],
        ['underline', 'underline', 'Sublinhado', 'Ctrl+U'],
        ['strikeThrough', 'strikethrough', 'Tachado', null],
    ],
    [
        ['insertUnorderedList', 'list-ul', 'Lista com marcadores', null],
        ['insertOrderedList', 'list-ol', 'Lista numerada', null],
    ],
];
?>
<div class="editor-documento">
    <nav class="editor-caminho" aria-label="Localização">
        <a href="<?= esc($enderecoProcesso, 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Voltar ao processo</a>
        <span aria-hidden="true">/</span>
        <span><?= esc($processo['numero']) ?></span>
    </nav>

    <form id="editor-documento-form" method="post" action="<?= esc($enderecoEditor, 'attr') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="chave" value="<?= esc($dados['chave'], 'attr') ?>">
        <input type="hidden" name="revisao" value="<?= esc($dados['revisao'], 'attr') ?>">
        <input type="hidden" name="setor_contexto" value="<?= esc($setorEditor['id'], 'attr') ?>">
        <input type="hidden" name="conteudo">

        <header class="editor-cabecalho">
            <div class="editor-identidade">
                <span class="editor-marca" aria-hidden="true"><i class="fa-regular fa-file-lines"></i></span>
                <div>
                    <h1>Editor de documento</h1>
                    <p id="editor-estado" class="editor-estado" data-estado="<?= $erro !== '' ? 'alterado' : ($dados['revisao'] !== '' ? 'salvo' : 'novo') ?>" role="status"><?= esc($estadoEditor) ?></p>
                </div>
            </div>
            <div class="editor-acoes">
                <button type="button" class="btn editor-botao" id="editor-previa" aria-pressed="false" aria-controls="editor-pagina" disabled>
                    <i class="fa-regular fa-eye" aria-hidden="true"></i> <span>Pré-visualizar</span>
                </button>
                <button type="button" class="btn editor-botao" id="editor-imprimir" hidden>
                    <i class="fa-solid fa-print" aria-hidden="true"></i> Imprimir / PDF
                </button>
                <button type="submit" class="btn editor-botao" id="editor-salvar" name="acao" value="rascunho" aria-keyshortcuts="Control+S Meta+S" disabled>
                    <i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Salvar rascunho
                </button>
                <button type="submit" class="btn btn-primary editor-botao-principal" id="editor-finalizar" name="acao" value="finalizar" disabled>
                    <i class="fa-solid fa-check" aria-hidden="true"></i> Finalizar documento
                </button>
            </div>
        </header>

        <?php if ($erro !== ''): ?>
            <p class="alert alert-danger editor-aviso" role="alert"><?= esc($erro) ?></p>
        <?php endif ?>
        <?php if (!$tipos): ?>
            <p class="alert alert-warning editor-aviso" role="status">Cadastre um tipo de documento ativo com versão publicada para habilitar a edição.</p>
        <?php endif ?>
        <noscript><p class="alert alert-warning editor-aviso">Ative o JavaScript para editar e salvar o documento.</p></noscript>
        <p id="editor-erro-local" class="alert alert-danger editor-aviso" role="alert" hidden></p>

        <fieldset id="editor-campos" class="editor-grade" <?= !$tipos ? 'disabled' : '' ?>>
            <legend class="visualmente-oculto">Documento</legend>
            <aside class="editor-lateral" aria-label="Informações e navegação do documento">
                <section class="editor-painel" aria-labelledby="editor-detalhes">
                    <h2 id="editor-detalhes"><i class="fa-solid fa-sliders" aria-hidden="true"></i> Detalhes do documento</h2>
                    <div class="editor-detalhes-campos">
                        <div>
                            <label class="form-label" for="editor-titulo">Título</label>
                            <input class="form-control" id="editor-titulo" name="titulo" placeholder="Ex. Parecer técnico" maxlength="500" value="<?= esc($dados['titulo'], 'attr') ?>" required>
                        </div>
                        <div>
                            <label class="form-label" for="editor-tipo">Tipo de documento</label>
                            <select class="form-select" name="tipo" id="editor-tipo" required>
                                <option value="">Selecione o tipo</option>
                                <?php foreach ($tipos as $tipo): ?>
                                    <option value="<?= esc((string) $tipo['id'], 'attr') ?>" <?= (string) $tipo['id'] === $dados['tipo'] ? 'selected' : '' ?>><?= esc($tipo['nome']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </div>
                        <?php if ($simulacaoSemVinculo): ?>
                            <div>
                                <label class="form-label" for="editor-autor">Registrar em nome de</label>
                                <select class="form-select" name="autor" id="editor-autor" required>
                                    <option value="">Selecione o autor</option>
                                    <?php foreach ($autores as $autor): ?>
                                        <option value="<?= esc((string) $autor['vinculo_id'], 'attr') ?>" <?= (string) $autor['vinculo_id'] === $dados['autor'] ? 'selected' : '' ?>><?= esc($autor['nome']) ?></option>
                                    <?php endforeach ?>
                                </select>
                            </div>
                        <?php endif ?>
                    </div>
                </section>

                <?php if ($rascunhos): ?>
                    <details class="editor-painel editor-rascunhos">
                        <summary><i class="fa-regular fa-folder-open" aria-hidden="true"></i> Rascunhos do processo <span><?= count($rascunhos) ?></span></summary>
                        <ul>
                            <?php foreach ($rascunhos as $rascunho): ?>
                                <li><a href="<?= esc($enderecoEditor . '?rascunho=' . $rascunho['chave'], 'attr') ?>" <?= $rascunho['chave'] === $dados['chave'] ? 'aria-current="page"' : '' ?>><?= esc($rascunho['titulo']) ?></a></li>
                            <?php endforeach ?>
                        </ul>
                        <?php if ($dados['revisao'] !== ''): ?>
                            <a class="editor-novo" href="<?= esc($enderecoEditor, 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i> Novo documento</a>
                        <?php endif ?>
                    </details>
                <?php endif ?>

                <section class="editor-painel editor-estrutura" aria-labelledby="editor-estrutura-titulo">
                    <h2 id="editor-estrutura-titulo"><i class="fa-solid fa-list-ul" aria-hidden="true"></i> Estrutura</h2>
                    <p id="editor-estrutura-vazia">Adicione títulos ao texto para navegar pelas seções.</p>
                    <ul id="editor-estrutura-lista" hidden></ul>
                </section>
            </aside>

            <section class="editor-area" aria-label="Área de edição">
                <div class="editor-ferramentas">
                    <div class="editor-barra" id="editor-barra" role="group" aria-label="Formatação do documento">
                        <?php foreach ($comandosEditor as $grupo => $comandos): ?>
                            <?php if ($grupo === 1): ?>
                                <div class="editor-grupo">
                                    <select class="editor-seletor" id="editor-bloco" aria-label="Estilo de texto">
                                        <option value="p">Parágrafo</option>
                                        <option value="h2">Título de seção</option>
                                        <option value="h3">Subtítulo</option>
                                        <option value="blockquote">Citação</option>
                                    </select>
                                </div>
                            <?php endif ?>
                            <div class="editor-grupo">
                                <?php foreach ($comandos as [$comando, $icone, $rotulo, $atalho]): ?>
                                    <button type="button" class="editor-ferramenta" data-comando="<?= $comando ?>" aria-label="<?= $rotulo ?>" title="<?= $rotulo ?><?= $atalho ? ' (' . $atalho . ')' : '' ?>" <?= $grupo > 0 ? 'aria-pressed="false"' : 'disabled' ?>>
                                        <i class="fa-solid fa-<?= $icone ?>" aria-hidden="true"></i>
                                    </button>
                                <?php endforeach ?>
                            </div>
                        <?php endforeach ?>
                        <div class="editor-grupo">
                            <label class="editor-alinhamento">
                                <i class="fa-solid fa-align-left" id="editor-icone-alinhamento" aria-hidden="true"></i>
                                <select id="editor-alinhamento" aria-label="Alinhamento do texto">
                                    <option value="left">À esquerda</option>
                                    <option value="center">Centralizado</option>
                                    <option value="right">À direita</option>
                                    <option value="justify">Justificado</option>
                                </select>
                            </label>
                        </div>
                        <div class="editor-grupo">
                            <button type="button" class="editor-ferramenta" id="editor-inserir-tabela" aria-label="Inserir tabela" title="Inserir tabela" aria-haspopup="dialog"><i class="fa-solid fa-table" aria-hidden="true"></i></button>
                        </div>
                    </div>
                    <div class="editor-tabela-acoes" role="group" aria-label="Editar tabela" hidden>
                        <button type="button" data-tabela="adicionar-linha"><i class="fa-solid fa-plus" aria-hidden="true"></i> Linha</button>
                        <button type="button" data-tabela="adicionar-coluna"><i class="fa-solid fa-plus" aria-hidden="true"></i> Coluna</button>
                        <button type="button" data-tabela="remover-linha"><i class="fa-solid fa-minus" aria-hidden="true"></i> Linha</button>
                        <button type="button" data-tabela="remover-coluna"><i class="fa-solid fa-minus" aria-hidden="true"></i> Coluna</button>
                        <button type="button" data-tabela="remover-tabela"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Tabela</button>
                    </div>
                    <p class="editor-modo-previa" id="editor-modo-previa" hidden><i class="fa-regular fa-eye" aria-hidden="true"></i> Pré-visualização</p>
                </div>
                <div class="editor-mesa">
                    <article id="editor-pagina" class="editor-pagina">
                        <h2 id="editor-titulo-previa" class="editor-titulo-papel" hidden></h2>
                        <div id="editor-conteudo" class="editor-texto" contenteditable="false" role="textbox" aria-label="Conteúdo do documento" aria-multiline="true" data-placeholder="Comece a escrever seu documento…"><?= $dados['conteudo'] ?></div>
                    </article>
                </div>
                <footer class="editor-rodape">
                    <span id="editor-contagem">0 palavras</span>
                    <span><i class="fa-regular fa-file" aria-hidden="true"></i> A4 · Retrato</span>
                </footer>
            </section>
        </fieldset>
    </form>

    <dialog class="editor-dialogo" id="editor-dialogo-tabela" aria-labelledby="editor-titulo-tabela">
        <form method="dialog">
            <h2 id="editor-titulo-tabela">Inserir tabela</h2>
            <div class="editor-dimensoes">
                <div><label class="form-label" for="editor-colunas">Colunas</label><input type="number" class="form-control" id="editor-colunas" name="colunas" value="2" min="1" max="8" required></div>
                <div><label class="form-label" for="editor-linhas">Linhas de dados</label><input type="number" class="form-control" id="editor-linhas" name="linhas" value="2" min="1" max="20" required></div>
            </div>
            <div class="editor-acoes">
                <button type="submit" class="btn editor-botao" value="cancelar" formnovalidate>Cancelar</button>
                <button type="submit" class="btn btn-primary" value="inserir">Inserir tabela</button>
            </div>
        </form>
    </dialog>
    <dialog class="editor-dialogo" id="editor-dialogo-finalizar" aria-labelledby="editor-titulo-finalizar" aria-describedby="editor-descricao-finalizar">
        <form method="dialog">
            <h2 id="editor-titulo-finalizar">Finalizar documento?</h2>
            <p id="editor-descricao-finalizar">O documento receberá um número oficial e será incluído no processo <?= esc($processo['numero']) ?>. A edição deste rascunho será encerrada.</p>
            <div class="editor-acoes">
                <button type="submit" class="btn editor-botao" value="cancelar" autofocus>Continuar editando</button>
                <button type="submit" class="btn btn-primary" value="finalizar">Finalizar documento</button>
            </div>
        </form>
    </dialog>
</div>
<?= $this->endSection() ?>

