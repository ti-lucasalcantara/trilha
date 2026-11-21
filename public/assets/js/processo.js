/** Controla a seleção de documentos e as informações do processo. */
(() => {
    'use strict';

    const documentos = Array.from(document.querySelectorAll('[data-documento]'));
    const campoPesquisa = document.getElementById('pesquisa-documentos');
    const dialogoAcao = document.getElementById('dialogo-acao');
    const divisaoProcesso = document.getElementById('divisao-processo');
    const botaoAmpliar = document.getElementById('ampliar-visualizador');
    const corpoDocumentos = document.getElementById('corpo-documentos');
    const botaoDocumentos = document.getElementById('alternar-documentos');
    const processoReal = document.querySelector('.area-processo');
    let documentoSelecionado = documentos[0];
    const acoes = {
        adicionar: 'Adicionar documento',
        remover: 'Remover documento',
        tramitar: 'Tramitar processo',
        acesso: 'Nível de acesso',
        historico: 'Histórico do processo',
        informacoes: 'Informações do processo',
        etiquetas: 'Gerenciar etiquetas',
        pastas: 'Organizar em pasta',
        concluir: 'Concluir processo',
        assinar: 'Assinar documento',
        bloco: 'Incluir em bloco de assinatura',
        editar: 'Editar documento',
        versoes: 'Versões do documento',
        solicitar: 'Solicitar assinatura',
    };

    if (!campoPesquisa || !dialogoAcao) return;

    /** Seleciona o documento e sincroniza sua identificação na área de visualização. */
    function selecionarDocumento(documento) {
        documentoSelecionado = documento;
        documentos.forEach(function atualizarSelecao(item) {
            item.classList.toggle('selecionado', item === documento);
            item.querySelector('.selecionar-documento').setAttribute('aria-pressed', String(item === documento));
        });
        const dados = documento.dataset;
        document.getElementById('titulo-visualizador').textContent = dados.arquivo;
        document.getElementById('metadados-documento-ativo').textContent = `Versão ${dados.versao} · ${dados.setor}`;
        document.getElementById('situacao-documento-ativo').className = `estado-documento ${dados.cor}`;
        document.getElementById('texto-situacao-documento').textContent = dados.situacao;
        document.getElementById('data-documento-ativo').textContent = `Incluído em ${dados.data}`;
        document.getElementById('posicao-documento').textContent = `Documento ${documentos.indexOf(documento) + 1} de ${documentos.length}`;
        document.getElementById('anuncio-documento').textContent = `${dados.titulo} selecionado. As opções agora se referem a este documento.`;
    }

    /** Filtra a lista sem perder a seleção do documento que já está em leitura. */
    function pesquisarDocumentos() {
        const pesquisa = campoPesquisa.value.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR').trim();
        let quantidadeVisivel = 0;
        documentos.forEach(function filtrarDocumento(documento) {
            const texto = [documento.dataset.arquivo, documento.dataset.titulo, documento.dataset.numero, documento.dataset.tipo].join(' ').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
            documento.hidden = !texto.includes(pesquisa);
            if (!documento.hidden) quantidadeVisivel++;
        });
        document.getElementById('documentos-vazios').hidden = quantidadeVisivel !== 0;
    }

    /** Apresenta a ação e identifica seu alvo como processo ou documento. */
    function abrirAcao(botao) {
        const chave = botao.dataset.acao;
        const configuracao = acoes[chave];
        if (!configuracao) return;
        const dados = documentoSelecionado?.dataset;
        const usaDocumento = botao.dataset.ambito === 'documento' || chave === 'remover';
        if (usaDocumento && !dados) return;
        const informacoes = document.getElementById('informacoes-processo-real');
        informacoes.hidden = chave !== 'informacoes';
        document.getElementById('titulo-acao').textContent = configuracao;
        document.getElementById('descricao-acao').textContent = chave === 'acesso'
            ? `Nível atual: ${usaDocumento ? dados.acesso : processoReal.dataset.acesso}.`
            : chave === 'informacoes' ? '' : 'Esta ação ainda não está disponível.';
        document.getElementById('referencia-acao').textContent = usaDocumento ? dados.arquivo : document.querySelector('.numero-processo').textContent;
        botao.closest('details')?.removeAttribute('open');
        dialogoAcao.showModal();
    }

    /** Amplia a leitura ocultando temporariamente a coluna de documentos. */
    function alternarLeituraAmpliada() {
        const ampliado = divisaoProcesso.classList.toggle('leitura-ampliada');
        botaoAmpliar.setAttribute('aria-pressed', String(ampliado));
        botaoAmpliar.setAttribute('aria-label', ampliado ? 'Mostrar lista de documentos' : 'Ampliar visualizador');
        botaoAmpliar.title = botaoAmpliar.getAttribute('aria-label');
        botaoAmpliar.querySelector('i').className = ampliado ? 'fa-solid fa-compress' : 'fa-solid fa-expand';
    }

    documentos.forEach(function configurarDocumento(documento) {
        documento.querySelector('.selecionar-documento').addEventListener('click', function abrirDocumento() { selecionarDocumento(documento); });
        const botaoDetalhes = documento.querySelector('.alternar-detalhes-documento');
        const detalhes = documento.querySelector('.detalhes-documento');
        // Expandir os metadados não troca o documento que está sendo lido.
        botaoDetalhes.addEventListener('click', function alternarDetalhesDocumento() {
            detalhes.hidden = !detalhes.hidden;
            botaoDetalhes.setAttribute('aria-expanded', String(!detalhes.hidden));
            botaoDetalhes.setAttribute('aria-label', `${detalhes.hidden ? 'Expandir' : 'Recolher'} detalhes de ${documento.dataset.arquivo}`);
        });
    });

    /** Ajusta a altura da coluna sem acoplar a rolagem dos documentos ao PDF. */
    function sincronizarAlturaDocumentos() {
        const alturaVisualizador = document.getElementById('area-visualizador').getBoundingClientRect().height;
        document.querySelector('.painel-documentos').style.setProperty('--altura-documentos', `${Math.ceil(alturaVisualizador)}px`);
    }

    /** Ajusta a largura da lista por arraste ou pelo teclado. */
    function configurarLarguraDocumentos() {
        const divisoria = document.getElementById('redimensionar-documentos');
        const painel = divisoria.parentElement;
        const minimo = 220;
        let inicio;

        function larguraMaxima() {
            return Math.min(520, Math.floor(divisaoProcesso.clientWidth * 0.5));
        }

        function atualizarLarguraAcessivel() {
            if (!divisoria.offsetWidth) return;
            divisoria.setAttribute('aria-valuemax', larguraMaxima());
            divisoria.setAttribute('aria-valuenow', painel.offsetWidth);
        }

        function aplicarLargura(largura) {
            const valor = Math.min(larguraMaxima(), Math.max(minimo, Math.round(largura)));
            divisaoProcesso.style.setProperty('--largura-documentos', `min(${valor}px, 50%)`);
            atualizarLarguraAcessivel();
        }

        divisoria.addEventListener('pointerdown', function iniciarAjuste(evento) {
            if (evento.button !== 0 || inicio) return;
            inicio = { ponteiro: evento.pointerId, posicao: evento.clientX, largura: painel.offsetWidth };
            divisoria.setPointerCapture(evento.pointerId);
            divisoria.classList.add('redimensionando');
            evento.preventDefault();
            divisoria.focus({ preventScroll: true });
        });
        divisoria.addEventListener('pointermove', function ajustar(evento) {
            if (inicio?.ponteiro !== evento.pointerId) return;
            aplicarLargura(inicio.largura + evento.clientX - inicio.posicao);
        });
        divisoria.addEventListener('lostpointercapture', function finalizarAjuste() {
            inicio = null;
            divisoria.classList.remove('redimensionando');
        });
        divisoria.addEventListener('keydown', function ajustarComTeclado(evento) {
            const larguras = { ArrowLeft: painel.offsetWidth - 16, ArrowRight: painel.offsetWidth + 16, Home: minimo, End: larguraMaxima() };
            if (!Object.hasOwn(larguras, evento.key)) return;
            evento.preventDefault();
            aplicarLargura(larguras[evento.key]);
        });
        new ResizeObserver(atualizarLarguraAcessivel).observe(divisaoProcesso);
    }

    const observadorVisualizador = new ResizeObserver(sincronizarAlturaDocumentos);
    observadorVisualizador.observe(document.getElementById('area-visualizador'));
    sincronizarAlturaDocumentos();
    configurarLarguraDocumentos();
    document.querySelectorAll('[data-acao]').forEach(function configurarAcao(botao) {
        botao.addEventListener('click', function apresentarAcao() { abrirAcao(botao); });
    });
    document.querySelectorAll('[data-fechar-acao]').forEach(function configurarFechamento(botao) {
        botao.addEventListener('click', function fecharAcao() { dialogoAcao.close(); });
    });
    if (documentos.length) campoPesquisa.addEventListener('input', pesquisarDocumentos);
    else campoPesquisa.disabled = true;
    botaoAmpliar?.addEventListener('click', alternarLeituraAmpliada);
    botaoDocumentos.addEventListener('click', function alternarListaDocumentos() {
        corpoDocumentos.hidden = !corpoDocumentos.hidden;
        botaoDocumentos.setAttribute('aria-expanded', String(!corpoDocumentos.hidden));
        botaoDocumentos.setAttribute('aria-label', corpoDocumentos.hidden ? 'Expandir lista de documentos' : 'Recolher lista de documentos');
        botaoDocumentos.querySelector('i').className = corpoDocumentos.hidden ? 'fa-solid fa-chevron-down' : 'fa-solid fa-chevron-up';
    });
    document.getElementById('acompanhar-processo').addEventListener('click', function informarAcompanhamento() {
        document.getElementById('titulo-acao').textContent = 'Acompanhar processo';
        document.getElementById('descricao-acao').textContent = 'Esta ação ainda não está disponível.';
        document.getElementById('informacoes-processo-real').hidden = true;
        document.getElementById('referencia-acao').textContent = document.querySelector('.numero-processo').textContent;
        dialogoAcao.showModal();
    });
})();
