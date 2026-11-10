<?php if (!$documentos): ?>
    <section class="area-visualizador" id="area-visualizador" aria-labelledby="titulo-visualizador">
        <header class="cabecalho-visualizador"><h2 id="titulo-visualizador">Visualização do documento</h2></header>
        <div class="moldura-pdf"><div class="pdf-indisponivel"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i><h3>Nenhum documento disponível</h3><p>Os documentos disponíveis para você aparecerão à esquerda.</p></div></div>
    </section>
    <?php return; ?>
<?php endif ?>
<?php $documentoInicial = $documentos[0]; ?>
<!-- Os controles externos complementam o visualizador nativo do navegador. -->
<section class="area-visualizador" id="area-visualizador" aria-labelledby="titulo-visualizador">
    <header class="cabecalho-visualizador">
        <div class="identificacao-documento">
            <h2 id="titulo-visualizador"><?= esc($documentoInicial['nomeArquivo']) ?></h2>
            <div class="resumo-documento-ativo">
                <span id="metadados-documento-ativo">Versão <?= esc($documentoInicial['versao'] . ' · ' . $documentoInicial['setor']) ?></span>
                <span id="situacao-documento-ativo" class="estado-documento <?= esc($documentoInicial['cor'], 'attr') ?>"><span></span><span id="texto-situacao-documento"><?= esc($documentoInicial['situacao']) ?></span></span>
            </div>
        </div>
        <div class="tarefas-documento" aria-label="Opções do documento selecionado">
            <button type="button" class="acao-documento acao-suave" data-acao="assinar" data-ambito="documento"><i class="fa-solid fa-signature" aria-hidden="true"></i>Assinar</button>
            <button type="button" class="acao-documento" data-acao="bloco" data-ambito="documento"><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Bloco de assinatura</button>
            <details class="menu-suspenso menu-acoes-documento">
                <summary class="acao-documento acao-icone" aria-label="Mais opções do documento" title="Mais opções do documento"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary>
                <div class="conteudo-menu">
                    <button type="button" class="opcao-menu" data-acao="editar" data-ambito="documento"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>Editar documento</button>
                    <button type="button" class="opcao-menu" data-acao="versoes" data-ambito="documento"><i class="fa-solid fa-code-branch" aria-hidden="true"></i>Versões do documento</button>
                    <button type="button" class="opcao-menu" data-acao="solicitar" data-ambito="documento"><i class="fa-regular fa-paper-plane" aria-hidden="true"></i>Solicitar assinatura</button>
                    <button type="button" class="opcao-menu" data-acao="acesso" data-ambito="documento"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Nível de acesso</button>
                    <?php if ($pdfDisponivel): ?>
                        <a class="opcao-menu" href="<?= esc($enderecoPdf, 'attr') ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Abrir PDF</a>
                    <?php endif ?>
                    <button type="button" class="opcao-menu" data-acao="remover" data-ambito="documento"><i class="fa-regular fa-trash-can" aria-hidden="true"></i>Remover documento</button>
                </div>
            </details>
            <?php if ($pdfDisponivel): ?>
                <a class="acao-documento acao-icone" href="<?= esc($enderecoPdf, 'attr') ?>" download="documento-demonstrativo.pdf" aria-label="Baixar PDF" title="Baixar PDF"><i class="fa-solid fa-download" aria-hidden="true"></i></a>
            <?php endif ?>
            <button type="button" id="ampliar-visualizador" class="acao-documento acao-icone" aria-label="Ampliar visualizador" title="Ampliar visualizador" aria-pressed="false"><i class="fa-solid fa-expand" aria-hidden="true"></i></button>
        </div>
    </header>
    <div class="moldura-pdf">
        <?php if ($pdfDisponivel): ?>
            <iframe id="visualizador-pdf" src="<?= esc($enderecoPdf . '#page=1&view=FitH&navpanes=0', 'attr') ?>" title="PDF — <?= esc($documentoInicial['nomeArquivo'], 'attr') ?>"></iframe>
        <?php elseif (!empty($consultaReal)): ?>
            <div class="pdf-indisponivel" role="status"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i><h3>Pré-visualização indisponível</h3><p>A exibição do arquivo depende da integração com o armazenamento.</p></div>
        <?php else: ?>
            <div class="pdf-indisponivel" role="status"><i class="fa-regular fa-file-pdf" aria-hidden="true"></i><h3>PDF de teste ainda não disponível</h3><p>Adicione o arquivo em public/pdf/teste.pdf e atualize a página.</p></div>
        <?php endif ?>
    </div>
    <div class="rodape-visualizador">
        <span id="data-documento-ativo">Incluído em <?= esc($documentoInicial['data']) ?></span>
        <span id="posicao-documento">Documento 1 de <?= esc((string) count($documentos)) ?></span>
    </div>
    <p id="anuncio-documento" class="visualmente-oculto" role="status" aria-live="polite"></p>
</section>
