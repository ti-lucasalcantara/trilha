/** Controla a seleção de documentos e as informações do processo. */
(() => {
    'use strict';

    const documentos = Array.from(document.querySelectorAll('[data-documento]'));
    const campoPesquisa = document.getElementById('pesquisa-documentos');
    const mostrarExcluidos = document.getElementById('mostrar-documentos-excluidos');
    const areaVisualizador = document.getElementById('area-visualizador');
    const visualizadorPdf = document.getElementById('visualizador-pdf');
    const visualizadorImagem = document.getElementById('visualizador-imagem');
    const avisoVisualizador = document.getElementById('aviso-visualizador-documento');
    const dialogoAcao = document.getElementById('dialogo-acao');
    const dialogoExclusao = document.getElementById('dialogo-exclusao-documento');
    const divisaoProcesso = document.getElementById('divisao-processo');
    const botaoAmpliar = document.getElementById('ampliar-visualizador');
    const processoReal = document.querySelector('.area-processo');
    let documentoSelecionado = documentos.find(documento => documento.classList.contains('selecionado'));
    const tituloVisualizador = document.getElementById('titulo-visualizador');
    const botaoRenomear = document.getElementById('renomear-documento');
    const formNome = document.getElementById('form-nome-documento');
    const campoNome = formNome?.elements.namedItem('titulo');
    const erroNome = document.getElementById('erro-nome-documento');
    let salvandoNome = false;
    const acoes = {
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

    function indicarFalhaImagem() {
        if (!visualizadorImagem.getAttribute('src')) return;
        visualizadorImagem.hidden = true;
        avisoVisualizador.hidden = false;
    }

    visualizadorImagem?.addEventListener('error', indicarFalhaImagem);
    if (visualizadorImagem?.complete && !visualizadorImagem.naturalWidth) indicarFalhaImagem();

    function fecharEdicaoNome() {
        if (!formNome) return;
        formNome.hidden = true;
        botaoRenomear.hidden = documentoSelecionado?.dataset.excluido === '1';
        tituloVisualizador.hidden = false;
        erroNome.hidden = true;
    }

    if (formNome) {
        botaoRenomear.addEventListener('click', () => {
            if (salvandoNome) return;
            campoNome.value = documentoSelecionado.dataset.titulo;
            tituloVisualizador.hidden = true;
            botaoRenomear.hidden = true;
            formNome.hidden = false;
            campoNome.select();
        });
        campoNome.addEventListener('keydown', evento => {
            if (evento.key !== 'Escape' || salvandoNome) return;
            fecharEdicaoNome();
            botaoRenomear.focus();
        });
        formNome.addEventListener('submit', async evento => {
            evento.preventDefault();
            if (salvandoNome) return;
            const documento = documentoSelecionado;
            salvandoNome = true;
            campoNome.readOnly = true;
            erroNome.hidden = true;
            try {
                const resposta = await fetch(`${formNome.dataset.endereco}/${documento.dataset.documento}/nome`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(formNome),
                });
                const dados = await resposta.json();
                if (dados.csrf) {
                    document.querySelectorAll('input[type="hidden"]').forEach(input => {
                        if (input.name === formNome.dataset.csrf) input.value = dados.csrf;
                    });
                }
                if (!resposta.ok) throw new Error(dados.erro || 'Não foi possível atualizar o nome.');

                documento.dataset.titulo = dados.titulo;
                documento.querySelector('.nome-arquivo span').textContent = dados.titulo;
                const selecionar = documento.querySelector('.selecionar-documento');
                selecionar.title = dados.titulo;
                selecionar.setAttribute('aria-label', `Visualizar ${dados.titulo}`);
                const detalhes = documento.querySelector('.alternar-detalhes-documento');
                detalhes.setAttribute('aria-label', `${detalhes.getAttribute('aria-expanded') === 'true' ? 'Recolher' : 'Expandir'} detalhes de ${dados.titulo}`);
                if (documento === documentoSelecionado) {
                    tituloVisualizador.textContent = dados.titulo;
                    fecharEdicaoNome();
                    botaoRenomear.focus();
                }
                if (campoPesquisa.value) pesquisarDocumentos();
            } catch (falha) {
                if (documento !== documentoSelecionado) return;
                erroNome.textContent = falha instanceof SyntaxError ? 'Não foi possível atualizar o nome. Recarregue a página e tente novamente.' : falha.message;
                erroNome.hidden = false;
            } finally {
                campoNome.readOnly = false;
                salvandoNome = false;
            }
        });
    }

    const adicionar = document.getElementById('adicionar-documento');
    if (adicionar) {
        const escolha = document.getElementById('dialogo-adicionar-documento');
        const envio = document.getElementById('dialogo-enviar-documento');
        const area = document.getElementById('area-envio-documento');
        const arquivo = area.querySelector('input[type="file"]');
        const nome = document.getElementById('arquivo-envio-documento');
        const titulo = document.getElementById('titulo-envio-documento');
        adicionar.addEventListener('click', () => escolha.showModal());
        document.getElementById('escolher-envio-documento').addEventListener('click', () => {
            escolha.close();
            envio.showModal();
        });
        document.getElementById('voltar-adicionar-documento').addEventListener('click', () => {
            envio.close();
            escolha.showModal();
        });

        function atualizarArquivo() {
            const selecionado = arquivo.files[0];
            const excedeuLimite = selecionado && selecionado.size > Number(arquivo.dataset.limite);
            arquivo.setCustomValidity(excedeuLimite ? 'O arquivo excede o limite de tamanho.' : '');
            nome.textContent = excedeuLimite ? 'O arquivo excede o limite de tamanho.' : selecionado?.name ?? '';
            titulo.placeholder = selecionado ? selecionado.name.replace(/\.[^.]+$/, '') || selecionado.name : 'Usar o nome do arquivo';
        }
        arquivo.addEventListener('change', atualizarArquivo);
        area.addEventListener('dragover', evento => {
            evento.preventDefault();
            if (!arquivo.disabled) area.classList.add('recebendo-arquivo');
        });
        area.addEventListener('dragleave', evento => {
            if (!area.contains(evento.relatedTarget)) area.classList.remove('recebendo-arquivo');
        });
        area.addEventListener('drop', evento => {
            evento.preventDefault();
            area.classList.remove('recebendo-arquivo');
            if (arquivo.disabled) return;
            if (evento.dataTransfer.files.length !== 1) {
                nome.textContent = 'Arraste um arquivo por vez.';
                return;
            }
            arquivo.files = evento.dataTransfer.files;
            atualizarArquivo();
        });
    }

    /** Seleciona o documento e sincroniza sua identificação na área de visualização. */
    function selecionarDocumento(documento) {
        fecharEdicaoNome();
        documentoSelecionado = documento;
        documentos.forEach(function atualizarSelecao(item) {
            item.classList.toggle('selecionado', item === documento);
            item.querySelector('.selecionar-documento').setAttribute('aria-pressed', String(item === documento));
        });
        const dados = documento.dataset;
        areaVisualizador.classList.remove('sem-documento-ativo');
        const previa = dados.pdf || dados.leitura;
        visualizadorPdf.hidden = !previa;
        visualizadorImagem.hidden = !dados.imagem;
        visualizadorImagem.alt = dados.titulo;
        avisoVisualizador.hidden = Boolean(previa || dados.imagem);
        if (!dados.imagem) visualizadorImagem.removeAttribute('src');
        else if (visualizadorImagem.getAttribute('src') !== dados.imagem) visualizadorImagem.setAttribute('src', dados.imagem);
        else if (visualizadorImagem.complete && !visualizadorImagem.naturalWidth) indicarFalhaImagem();
        if (!previa) visualizadorPdf.removeAttribute('src');
        else if (visualizadorPdf.getAttribute('src') !== previa) visualizadorPdf.setAttribute('src', previa);
        const excluido = dados.excluido === '1';
        if (botaoRenomear) botaoRenomear.hidden = excluido;
        document.querySelectorAll('[data-documento-ativo]').forEach(botao => { botao.hidden = excluido; });
        document.querySelectorAll('[data-exclusao-documento]').forEach(botao => {
            botao.hidden = botao.dataset.exclusaoDocumento === 'excluir' ? excluido || dados.podeExcluir !== '1' : !excluido;
        });
        const baixar = document.getElementById('baixar-documento-ativo');
        baixar.hidden = !dados.download;
        if (dados.download) baixar.href = dados.download;
        else baixar.removeAttribute('href');
        document.getElementById('ajuda-visualizador-documento').textContent = excluido
            ? 'Somente um administrador pode restaurar este documento.'
            : dados.download ? 'Baixe o arquivo para abrir no seu computador.' : 'O arquivo ainda não está disponível para leitura.';
        tituloVisualizador.textContent = dados.titulo;
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
        let quantidadeDisponivel = 0;
        documentos.forEach(function filtrarDocumento(documento) {
            const texto = [documento.dataset.arquivo, documento.dataset.titulo, documento.dataset.numero, documento.dataset.tipo].join(' ').normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
            const disponivel = mostrarExcluidos?.checked || documento.dataset.excluido !== '1';
            if (disponivel) quantidadeDisponivel++;
            documento.hidden = !disponivel || !texto.includes(pesquisa);
            if (!documento.hidden) quantidadeVisivel++;
        });
        document.getElementById('quantidade-documentos').textContent = quantidadeDisponivel;
        const vazio = document.getElementById('documentos-vazios');
        vazio.hidden = quantidadeVisivel !== 0;
        vazio.textContent = pesquisa ? 'Nenhum documento encontrado. Tente outro termo.' : 'Nenhum documento disponível.';

        if (!mostrarExcluidos?.checked && documentoSelecionado?.dataset.excluido === '1') {
            documentoSelecionado.classList.remove('selecionado');
            documentoSelecionado.querySelector('.selecionar-documento').setAttribute('aria-pressed', 'false');
            documentoSelecionado = undefined;
            areaVisualizador.classList.add('sem-documento-ativo');
        }
        if (!documentoSelecionado) {
            const primeiro = documentos.find(documento => !documento.hidden);
            if (primeiro) selecionarDocumento(primeiro);
        }
    }

    /** Apresenta a ação e identifica seu alvo como processo ou documento. */
    function abrirAcao(botao) {
        const chave = botao.dataset.acao;
        const configuracao = acoes[chave];
        if (!configuracao) return;
        const dados = documentoSelecionado?.dataset;
        const usaDocumento = botao.dataset.ambito === 'documento';
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
            botaoDetalhes.setAttribute('aria-label', `${detalhes.hidden ? 'Expandir' : 'Recolher'} detalhes de ${documento.dataset.titulo}`);
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
        const painel = divisaoProcesso.querySelector('.painel-documentos');
        const preferencias = window.preferenciasTrilha;
        const chaveLargura = 'processo:largura-documentos';
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

        function aplicarLargura(largura, salvar = false) {
            const valor = Math.min(larguraMaxima(), Math.max(minimo, Math.round(largura)));
            divisaoProcesso.style.setProperty('--largura-documentos', `min(${valor}px, 50%)`);
            atualizarLarguraAcessivel();
            if (salvar) preferencias.gravar(chaveLargura, valor);
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
            if (inicio && painel.offsetWidth !== inicio.largura) preferencias.gravar(chaveLargura, painel.offsetWidth);
            inicio = null;
            divisoria.classList.remove('redimensionando');
        });
        divisoria.addEventListener('keydown', function ajustarComTeclado(evento) {
            const larguras = { ArrowLeft: painel.offsetWidth - 16, ArrowRight: painel.offsetWidth + 16, Home: minimo, End: larguraMaxima() };
            if (!Object.hasOwn(larguras, evento.key)) return;
            evento.preventDefault();
            aplicarLargura(larguras[evento.key], true);
        });
        const larguraSalva = preferencias.ler(chaveLargura);
        if (Number.isFinite(larguraSalva) && larguraSalva >= minimo && larguraSalva <= 520) {
            divisaoProcesso.style.setProperty('--largura-documentos', `min(${larguraSalva}px, 50%)`);
        }
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
    if (dialogoExclusao) {
        const form = dialogoExclusao.querySelector('form');
        const confirmar = document.getElementById('confirmar-exclusao-documento');
        const mensagens = {
            excluir: ['Excluir documento', 'O documento continuará na lista com a marca de excluído. Somente um administrador poderá restaurá-lo.', 'Excluir'],
            restaurar: ['Restaurar documento', 'O documento voltará a ficar disponível no processo.', 'Restaurar'],
            definitiva: ['Excluir definitivamente', 'O documento será removido da lista e esta ação não poderá ser desfeita.', 'Excluir definitivamente'],
        };
        document.querySelectorAll('[data-exclusao-documento]').forEach(botao => {
            botao.addEventListener('click', () => {
                const acao = botao.dataset.exclusaoDocumento;
                const [titulo, mensagem, rotulo] = mensagens[acao];
                form.action = `${form.dataset.endereco}/${documentoSelecionado.dataset.documento}/exclusao`;
                form.elements.namedItem('acao').value = acao;
                document.getElementById('titulo-exclusao-documento').textContent = titulo;
                document.getElementById('mensagem-exclusao-documento').textContent = mensagem;
                document.getElementById('referencia-exclusao-documento').textContent = documentoSelecionado.dataset.titulo;
                confirmar.textContent = rotulo;
                confirmar.className = acao === 'restaurar' ? 'btn btn-primary' : 'btn btn-danger';
                dialogoExclusao.showModal();
            });
        });
        document.getElementById('cancelar-exclusao-documento').addEventListener('click', () => dialogoExclusao.close());
        const id = new URLSearchParams(location.search).get('documento');
        const selecionado = documentos.find(documento => documento.dataset.documento === id && !documento.hidden);
        if (selecionado) selecionarDocumento(selecionado);
    }
    mostrarExcluidos?.addEventListener('change', pesquisarDocumentos);
    if (documentos.length) campoPesquisa.addEventListener('input', pesquisarDocumentos);
    else campoPesquisa.disabled = true;
    botaoAmpliar?.addEventListener('click', alternarLeituraAmpliada);
    document.getElementById('acompanhar-processo').addEventListener('click', function informarAcompanhamento() {
        document.getElementById('titulo-acao').textContent = 'Acompanhar processo';
        document.getElementById('descricao-acao').textContent = 'Esta ação ainda não está disponível.';
        document.getElementById('informacoes-processo-real').hidden = true;
        document.getElementById('referencia-acao').textContent = document.querySelector('.numero-processo').textContent;
        dialogoAcao.showModal();
    });
})();
