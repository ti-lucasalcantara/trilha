<?php if (!$documentos): ?>
    <section class="area-visualizador" id="area-visualizador" aria-labelledby="titulo-visualizador">
        <header class="cabecalho-visualizador"><h2 id="titulo-visualizador">Visualização do documento</h2></header>
        <div class="moldura-pdf"><div class="pdf-indisponivel"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i><h3>Nenhum documento disponível</h3><p>Os documentos disponíveis para você aparecerão à esquerda.</p></div></div>
    </section>
    <?php return; ?>
<?php endif ?>
<?php $documentoInicial = $documentoAtivo ?? $documentos[0]; ?>
<?php $previa = $documentoInicial['pdf'] ?: $documentoInicial['leitura']; ?>
<!-- Os controles externos complementam o visualizador nativo do navegador. -->
<section class="area-visualizador <?= $documentoAtivo ? '' : 'sem-documento-ativo' ?>" id="area-visualizador" aria-label="Visualização do documento">
    <div class="moldura-pdf" id="visualizador-vazio"><div class="pdf-indisponivel"><i class="fa-regular fa-file" aria-hidden="true"></i><h3>Nenhum documento selecionado</h3><p>Selecione um documento na lista para visualizá-lo.</p></div></div>
    <header class="cabecalho-visualizador">
        <div class="identificacao-documento">
            <div class="nome-visualizador-documento">
                <h2 id="titulo-visualizador"><?= esc($documentoInicial['titulo']) ?></h2>
                <?php if ($administradorSimulado): ?>
                    <button type="button" class="acao-documento acao-icone" id="renomear-documento" aria-label="Editar nome do documento" title="Editar nome do documento" <?= $documentoInicial['excluido'] ? 'hidden' : '' ?>>
                        <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
                    </button>
                    <form id="form-nome-documento" class="flex-grow-1" method="post" hidden
                        data-endereco="<?= esc($enderecoProcesso . '/documentos', 'attr') ?>" data-csrf="<?= esc(csrf_token(), 'attr') ?>">
                        <?= csrf_field() ?>
                        <input class="form-control" id="nome-documento-edicao" type="text" name="titulo" maxlength="500" required aria-label="Nome do documento">
                    </form>
                <?php endif ?>
            </div>
            <p id="erro-nome-documento" class="text-danger small" role="alert" hidden></p>
        </div>
        <div class="tarefas-documento" role="group" aria-label="Opções do documento selecionado">
            <a class="acao-documento" id="baixar-documento-ativo" href="<?= esc($documentoInicial['download'], 'attr') ?>" <?= $documentoInicial['download'] === '' ? 'hidden' : '' ?>><i class="fa-solid fa-download" aria-hidden="true"></i>Baixar</a>
            <button type="button" class="acao-documento acao-suave" data-documento-ativo data-acao="assinar" data-ambito="documento" <?= $documentoInicial['excluido'] ? 'hidden' : '' ?>><i class="fa-solid fa-signature" aria-hidden="true"></i>Assinar</button>
            <button type="button" class="acao-documento" data-documento-ativo data-acao="bloco" data-ambito="documento" <?= $documentoInicial['excluido'] ? 'hidden' : '' ?>><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Bloco de assinatura</button>
            <button type="button" class="acao-documento acao-excluir-documento" data-exclusao-documento="excluir" <?= $documentoInicial['excluido'] || !$documentoInicial['podeExcluir'] ? 'hidden' : '' ?>><i class="fa-regular fa-trash-can" aria-hidden="true"></i>Excluir</button>
            <?php if ($administradorSimulado): ?>
                <button type="button" class="acao-documento" data-exclusao-documento="restaurar" <?= !$documentoInicial['excluido'] ? 'hidden' : '' ?>><i class="fa-solid fa-rotate-left" aria-hidden="true"></i>Restaurar</button>
                <button type="button" class="acao-documento acao-excluir-documento" data-exclusao-documento="definitiva" <?= !$documentoInicial['excluido'] ? 'hidden' : '' ?>><i class="fa-regular fa-trash-can" aria-hidden="true"></i>Excluir definitivamente</button>
            <?php endif ?>
            <details class="menu-suspenso menu-acoes-documento" data-documento-ativo <?= $documentoInicial['excluido'] ? 'hidden' : '' ?>>
                <summary class="acao-documento acao-icone" aria-label="Mais opções do documento" title="Mais opções do documento"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary>
                <div class="conteudo-menu">
                    <button type="button" class="opcao-menu" data-acao="editar" data-ambito="documento"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Editar documento</button>
                    <button type="button" class="opcao-menu" data-acao="versoes" data-ambito="documento"><i class="fa-solid fa-code-branch" aria-hidden="true"></i>Versões do documento</button>
                    <button type="button" class="opcao-menu" data-acao="solicitar" data-ambito="documento"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i>Solicitar assinatura</button>
                    <button type="button" class="opcao-menu" data-acao="acesso" data-ambito="documento"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Nível de acesso</button>
                </div>
            </details>
            <button type="button" id="ampliar-visualizador" class="acao-documento acao-icone" aria-label="Ampliar visualizador" title="Ampliar visualizador" aria-pressed="false"><i class="fa-solid fa-expand" aria-hidden="true"></i></button>
        </div>
    </header>
    <div class="moldura-pdf" id="moldura-documento-ativo">
        <iframe id="visualizador-pdf" title="Visualização do documento" <?= $previa ? 'src="' . esc($previa, 'attr') . '"' : 'hidden' ?>></iframe>
        <img id="visualizador-imagem" alt="<?= esc($documentoInicial['titulo'], 'attr') ?>" <?= $documentoInicial['imagem'] ? 'src="' . esc($documentoInicial['imagem'], 'attr') . '"' : 'hidden' ?>>
        <div class="pdf-indisponivel" id="aviso-visualizador-documento" role="status" <?= $previa || $documentoInicial['imagem'] ? 'hidden' : '' ?>><i class="fa-regular fa-file" aria-hidden="true"></i><h3>Pré-visualização indisponível</h3><p id="ajuda-visualizador-documento"><?= $documentoInicial['excluido'] ? 'Somente um administrador pode restaurar este documento.' : ($documentoInicial['download'] ? 'Baixe o arquivo para abrir no seu computador.' : 'O arquivo ainda não está disponível para leitura.') ?></p></div>
    </div>
    <footer class="rodape-visualizador">
        <div class="resumo-documento-ativo">
            <span id="metadados-documento-ativo">Versão <?= esc($documentoInicial['versao'] . ' · ' . $documentoInicial['setor']) ?></span>
            <span id="situacao-documento-ativo" class="estado-documento <?= esc($documentoInicial['cor'], 'attr') ?>"><span></span><span id="texto-situacao-documento"><?= esc($documentoInicial['situacao']) ?></span></span>
            <span id="data-documento-ativo">Incluído em <?= esc($documentoInicial['data']) ?></span>
        </div>
        <span id="posicao-documento">Documento 1 de <?= esc((string) count($documentos)) ?></span>
    </footer>
    <p id="anuncio-documento" class="visualmente-oculto" role="status" aria-live="polite"></p>
</section>
