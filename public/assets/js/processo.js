/** Controla a seleção de documentos e as prévias de ações sem gravar dados. */
(() => {
    'use strict';

    const documentos = Array.from(document.querySelectorAll('[data-documento]'));
    const campoPesquisa = document.getElementById('pesquisa-documentos');
    const visualizador = document.getElementById('visualizador-pdf');
    const dialogoAcao = document.getElementById('dialogo-acao');
    const divisaoProcesso = document.getElementById('divisao-processo');
    const botaoAmpliar = document.getElementById('ampliar-visualizador');
    const corpoDocumentos = document.getElementById('corpo-documentos');
    const botaoDocumentos = document.getElementById('alternar-documentos');
    const processoReal = document.querySelector('[data-processo-real]');
    let documentoSelecionado = documentos[0];
    const acoes = {
        adicionar: ['Adicionar documento', 'Escolha como deseja preparar um novo documento para o processo.'],
        remover: ['Remover documento', 'Prévia da solicitação de remoção do documento selecionado.'],
        tramitar: ['Tramitar processo', 'Escolha um destino demonstrativo para avaliar o encaminhamento.'],
        acesso: ['Nível de acesso', 'Prévia da configuração de acesso ao item indicado abaixo.'],
        historico: ['Histórico do processo', 'Movimentações fictícias para avaliar a apresentação do histórico.'],
        informacoes: ['Informações do processo', 'Dados demonstrativos do processo que está sendo visualizado.'],
        etiquetas: ['Gerenciar etiquetas', 'Prévia da organização do processo por etiquetas.'],
        pastas: ['Organizar em pasta', 'Prévia da associação do processo a uma pasta.'],
        concluir: ['Concluir processo', 'A conclusão e suas condições serão implementadas posteriormente.'],
        assinar: ['Assinar documento', 'Confira o documento e a versão antes de uma futura assinatura.'],
        bloco: ['Incluir em bloco de assinatura', 'Selecione um bloco demonstrativo para o documento indicado.'],
        editar: ['Editar documento', 'O editor e as regras de edição serão construídos em uma próxima etapa.'],
        versoes: ['Versões do documento', 'Prévia da consulta às versões do documento selecionado.'],
        solicitar: ['Solicitar assinatura', 'A seleção de signatários e o acompanhamento serão construídos posteriormente.'],
    };

    if (!campoPesquisa || !dialogoAcao) return;

    /** Seleciona o documento e sincroniza sua identificação com o PDF de teste. */
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
        if (visualizador) visualizador.title = `PDF — ${dados.arquivo}`;
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

    /** Apresenta a prévia correta e identifica seu alvo como processo ou documento. */
    function abrirAcao(botao) {
        const chave = botao.dataset.acao;
        const configuracao = acoes[chave];
        if (!configuracao) return;
        const dados = documentoSelecionado?.dataset;
        const usaDocumento = botao.dataset.ambito === 'documento' || chave === 'remover';
        if (usaDocumento && !dados) return;
        if (processoReal) {
            const informacoes = document.getElementById('informacoes-processo-real');
            informacoes.hidden = chave !== 'informacoes';
            document.getElementById('titulo-acao').textContent = configuracao[0];
            document.getElementById('descricao-acao').textContent = chave === 'acesso'
                ? `Nível atual: ${usaDocumento ? dados.acesso : processoReal.dataset.acesso}.`
                : chave === 'informacoes' ? '' : 'Esta ação ainda não está disponível.';
            document.getElementById('referencia-acao').textContent = usaDocumento ? dados.arquivo : document.querySelector('.numero-processo').textContent;
            botao.closest('details')?.removeAttribute('open');
            dialogoAcao.showModal();
            return;
        }
        document.getElementById('ambito-acao').textContent = usaDocumento ? 'Ação sobre o documento selecionado' : 'Ação sobre o processo';
        document.getElementById('titulo-acao').textContent = configuracao[0];
        document.getElementById('descricao-acao').textContent = configuracao[1];
        document.getElementById('referencia-acao').textContent = usaDocumento ? `${dados.numero} · ${dados.titulo} · Versão ${dados.versao}` : document.querySelector('.numero-processo').textContent;
        const painelExiste = Boolean(document.querySelector(`[data-painel="${chave}"]`));
        document.querySelectorAll('[data-painel]').forEach(function escolherPainel(painel) {
            painel.hidden = painel.dataset.painel !== (painelExiste ? chave : 'padrao');
        });
        document.getElementById('explicacao-acao').textContent = chave === 'versoes' ? `Versão atual do documento: ${dados.versao}. O histórico de versões será implementado posteriormente.` : configuracao[1];
        document.getElementById('versao-assinatura').textContent = `Versão ${dados.versao} · ${dados.situacao}`;
        document.getElementById('nivel-acesso-previa').value = usaDocumento ? dados.acesso : document.querySelector('.area-processo').dataset.acesso;
        document.querySelectorAll('.menu-suspenso[open]').forEach(function fecharMenu(menu) { menu.open = false; });
        dialogoAcao.showModal();
    }

    /** Alterna o formulário ilustrativo de criação pelo editor ou envio de arquivo. */
    function selecionarOrigem(botao) {
        const usaEditor = botao.dataset.origem === 'editor';
        document.querySelectorAll('[data-origem]').forEach(function atualizarOrigem(item) {
            item.setAttribute('aria-pressed', String(item === botao));
        });
        document.getElementById('previa-editor').hidden = !usaEditor;
        document.getElementById('previa-upload').hidden = usaEditor;
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
    document.querySelectorAll('[data-origem]').forEach(function configurarOrigem(botao) {
        botao.addEventListener('click', function escolherOrigem() { selecionarOrigem(botao); });
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
    document.getElementById('acompanhar-processo').addEventListener('click', function alternarAcompanhamento(evento) {
        if (processoReal) {
            document.getElementById('titulo-acao').textContent = 'Acompanhar processo';
            document.getElementById('descricao-acao').textContent = 'Esta ação ainda não está disponível.';
            document.getElementById('informacoes-processo-real').hidden = true;
            document.getElementById('referencia-acao').textContent = document.querySelector('.numero-processo').textContent;
            dialogoAcao.showModal();
            return;
        }
        const botao = evento.currentTarget;
        const acompanhado = botao.getAttribute('aria-pressed') !== 'true';
        botao.setAttribute('aria-pressed', String(acompanhado));
        botao.querySelector('i').className = acompanhado ? 'fa-solid fa-star' : 'fa-regular fa-star';
    });
})();
