/** Implementa a navegação, os filtros e a paginação locais da consulta demonstrativa. */
(() => {
    'use strict';

    const corpoConsulta = document.getElementById('corpo-consulta');
    const campoPesquisa = document.getElementById('pesquisa-processos');
    if (!corpoConsulta || !campoPesquisa) return;

    const registros = Array.from(corpoConsulta.querySelectorAll('[data-registro]'));
    const itensExplorador = Array.from(document.querySelectorAll('[data-escopo]'));
    const camposFiltros = Array.from(document.querySelectorAll('[data-filtro]'));
    const seletorOrdenacao = document.getElementById('ordenacao-processos');
    const seletorQuantidade = document.getElementById('itens-por-pagina');
    const numerosPaginas = document.getElementById('numeros-paginas');
    const botaoAnterior = document.getElementById('pagina-anterior');
    const botaoSeguinte = document.getElementById('pagina-seguinte');
    const filtrosAplicados = document.getElementById('filtros-aplicados');
    const botaoFiltros = document.getElementById('alternar-filtros');
    const painelFiltros = document.getElementById('filtros-avancados');
    const dialogoColunas = document.getElementById('dialogo-colunas');
    const avisoColunas = document.getElementById('aviso-colunas');
    const opcoesColunas = Array.from(dialogoColunas.querySelectorAll('input[name="colunas"]'));
    const tabelaConsulta = corpoConsulta.closest('table');
    const cabecalhosColunas = Array.from(tabelaConsulta.querySelectorAll('th[data-coluna]'));
    const botaoExplorador = document.getElementById('alternar-explorador');
    const arvoreExplorador = document.getElementById('arvore-explorador');
    const estruturaExplorador = document.querySelector('.estrutura-explorador');
    const contextoDemo = document.querySelector('#seletor-trabalho-cabecalho[data-demonstrativo="true"]');
    const consultaCelular = window.matchMedia('(max-width: 650px)');
    const preferencias = window.preferenciasTrilha;
    const barraLote = document.getElementById('acoes-lote-processos');
    const seletoresLote = Array.from(document.querySelectorAll('.selecionar-processo'));
    let itemSelecionado = itensExplorador[0];
    let paginaAtual = 1;
    let quantidadePaginas = 1;
    let temporizadorCopia;
    let temporizadorAcompanhamento;
    let filaAlteracoes = Promise.resolve();

    /** Serializa requisições da listagem que renovam o mesmo token CSRF. */
    function enviarAlteracaoProcessos(endereco, dados) {
        async function enviar() {
            const { dataset } = document.getElementById('dialogo-pasta');
            const parametros = new URLSearchParams({ ...dados, [dataset.csrfNome]: dataset.csrfValor });
            const resposta = await fetch(endereco, {
                method: 'POST', headers: { 'X-Requested-With': 'XMLHttpRequest' }, body: parametros,
            });
            const resultado = await resposta.json().catch(() => null);
            if (resultado?.csrf) {
                dataset.csrfValor = resultado.csrf;
                document.getElementsByName(dataset.csrfNome).forEach(campo => { campo.value = resultado.csrf; });
            }
            if (!resposta.ok || !resultado) throw new Error(resultado?.erro ?? 'Não foi possível salvar. Atualize a página e tente novamente.');
            return resultado;
        }
        const envio = filaAlteracoes.then(enviar);
        filaAlteracoes = envio.catch(() => {});
        return envio;
    }

    /** Separa as consultas de cada setor sem perder as preferências de layout. */
    function chaveConsulta(incluirCaixa = true) {
        const unidade = document.getElementById('unidade-trabalho')?.value ?? '';
        const setor = document.getElementById('setor-trabalho')?.value ?? '';
        const caixa = estruturaExplorador.dataset.caixaProcessos;
        return `processos:consulta:${JSON.stringify(incluirCaixa && caixa && caixa !== 'ativos' ? [unidade, setor, caixa] : [unidade, setor])}`;
    }

    function chaveItem(item) {
        const { escopo, grupo, valor, rotulo } = item.dataset;
        return JSON.stringify([escopo, grupo, valor, escopo === 'pasta-real' ? null : rotulo]);
    }

    /** Recupera os filtros disponíveis e a página usada na última consulta do setor. */
    function restaurarConsulta() {
        const salvo = preferencias.ler(chaveConsulta()) ?? {};
        campoPesquisa.value = typeof salvo.pesquisa === 'string' ? salvo.pesquisa : '';
        camposFiltros.forEach(campo => {
            const valor = salvo.filtros?.[campo.dataset.filtro];
            campo.value = typeof valor === 'string' ? valor : '';
            if (campo.tagName === 'SELECT' && campo.selectedIndex === -1) campo.selectedIndex = 0;
        });
        [seletorOrdenacao, seletorQuantidade].forEach(function restaurarSeletor(campo) {
            const valor = salvo[campo.id];
            if (Array.from(campo.options).some(opcao => opcao.value === valor)) campo.value = valor;
            else campo.selectedIndex = campo.id === 'itens-por-pagina' ? 1 : 0;
        });
        paginaAtual = Number.isSafeInteger(salvo.pagina) && salvo.pagina > 0 ? salvo.pagina : 1;
        const item = Array.from(arvoreExplorador.querySelectorAll('[data-escopo]')).find(botao => chaveItem(botao) === salvo.item);
        selecionarItem(item ?? itensExplorador[0], false);
        atualizarConsulta();
    }

    /** Permite pesquisar textos sem diferenciar acentos ou letras maiúsculas. */
    function normalizarTexto(texto) {
        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    }

    /** Formata a data demonstrativa sem conversões de fuso horário. */
    function formatarData(data) {
        return data.split('-').reverse().join('/');
    }

    /** Aplica o contexto no demo; a consulta real já chega filtrada do servidor. */
    function pertenceAoContexto(registro) {
        return !contextoDemo || (registro.dataset.unidade === contextoDemo.elements.namedItem('unidade').value
            && registro.dataset.setor === contextoDemo.elements.namedItem('setor').value);
    }

    /** Verifica o vínculo do exemplo com a unidade, setor ou pasta selecionados. */
    function correspondeExplorador(registro) {
        const selecao = itemSelecionado.dataset;
        const dados = registro.dataset;
        if (selecao.escopo === 'acompanhados') return dados.acompanhado === 'true';
        if (selecao.escopo === 'unidade') return dados.unidadeEscopo === selecao.valor;
        if (selecao.escopo === 'setor') return dados.unidadeEscopo === selecao.unidade && dados.setorEscopo === selecao.valor;
        if (selecao.escopo === 'pasta-real') return dados.pastasProcesso.split(',').includes(selecao.valor);
        if (selecao.escopo === 'grupo-pasta') {
            return Array.from(itemSelecionado.closest('.no-pasta').querySelectorAll('[data-escopo="pasta"]')).some(function contemProcesso(subpasta) {
                return dados.grupo === subpasta.dataset.grupo && dados.pasta === subpasta.dataset.valor;
            });
        }
        if (selecao.escopo === 'pasta') return dados.grupo === selecao.grupo && dados.pasta === selecao.valor;
        if (selecao.escopo === 'pasta-local') return false;
        return true;
    }

    /** Combina o explorador com os campos e o período de abertura. */
    function filtrarRegistros() {
        const pesquisa = normalizarTexto(campoPesquisa.value.trim());
        const inicio = document.getElementById('abertura-inicial').value;
        const fim = document.getElementById('abertura-final').value;
        const periodoInvalido = Boolean(inicio && fim && inicio > fim);
        document.getElementById('aviso-periodo').hidden = !periodoInvalido;
        document.getElementById('abertura-final').setAttribute('aria-invalid', String(periodoInvalido));

        return registros.filter(function verificarRegistro(registro) {
            if (periodoInvalido || !correspondeExplorador(registro)) return false;
            const dados = registro.dataset;
            if (!pertenceAoContexto(registro)) return false;
            const texto = normalizarTexto([dados.numero, dados.assunto, dados.interessado].join(' '));
            if (!texto.includes(pesquisa)) return false;
            return camposFiltros.every(function verificarCampo(campo) {
                if (!campo.value) return true;
                if (campo.dataset.filtro === 'etiqueta') return JSON.parse(dados.etiquetasProcesso).includes(campo.value);
                if (campo.dataset.filtro === 'aberturaInicial') return dados.abertura >= campo.value;
                if (campo.dataset.filtro === 'aberturaFinal') return dados.abertura <= campo.value;
                return dados[campo.dataset.filtro] === campo.value;
            });
        });
    }

    /** Ordena os exemplos pelas datas exibidas na consulta. */
    function ordenarRegistros(resultado) {
        const ordenacao = seletorOrdenacao.value;
        return resultado.sort(function compararRegistros(primeiro, segundo) {
            if (ordenacao === 'prazo') return (primeiro.dataset.prazo || '9999').localeCompare(segundo.dataset.prazo || '9999');
            if (ordenacao === 'abertura-antiga') return primeiro.dataset.abertura.localeCompare(segundo.dataset.abertura);
            if (ordenacao === 'abertura-recente') return segundo.dataset.abertura.localeCompare(primeiro.dataset.abertura);
            return segundo.dataset.atualizacao.localeCompare(primeiro.dataset.atualizacao);
        });
    }

    /** Adiciona um filtro removível usando somente texto, sem inserir HTML. */
    function adicionarFiltroAplicado(rotulo, remover) {
        const botao = document.createElement('button');
        botao.type = 'button';
        botao.className = 'filtro-aplicado';
        const texto = document.createElement('span');
        texto.textContent = rotulo;
        const icone = document.createElement('i');
        icone.className = 'fa-solid fa-xmark';
        icone.setAttribute('aria-hidden', 'true');
        botao.append(texto, icone);
        botao.setAttribute('aria-label', `Remover filtro ${rotulo}`);
        botao.addEventListener('click', remover);
        filtrosAplicados.append(botao);
    }

    /** Reconstrói os indicadores dos filtros para facilitar a remoção individual. */
    function atualizarFiltrosAplicados() {
        filtrosAplicados.replaceChildren();
        if (itemSelecionado.dataset.escopo !== 'todos') {
            adicionarFiltroAplicado(itemSelecionado.dataset.rotulo, function removerEscopo() { selecionarItem(itensExplorador[0]); });
        }
        if (campoPesquisa.value.trim()) {
            adicionarFiltroAplicado(`Busca: ${campoPesquisa.value.trim()}`, function removerPesquisa() {
                campoPesquisa.value = '';
                atualizarFiltros();
            });
        }
        let quantidadeFiltros = 0;
        camposFiltros.forEach(function representarCampo(campo) {
            if (!campo.value) return;
            quantidadeFiltros++;
            const valor = campo.type === 'date' ? formatarData(campo.value) : campo.value;
            adicionarFiltroAplicado(`${campo.dataset.rotulo}: ${valor}`, function removerCampo() {
                campo.value = '';
                atualizarFiltros();
            });
        });
        const contador = document.getElementById('total-filtros');
        contador.textContent = String(quantidadeFiltros);
        contador.hidden = quantidadeFiltros === 0;
        filtrosAplicados.hidden = !filtrosAplicados.childElementCount;
    }

    /** Desenha os controles de páginas com indicação acessível da página atual. */
    function atualizarPaginacao(total) {
        numerosPaginas.replaceChildren();
        for (let numero = 1; numero <= quantidadePaginas; numero++) {
            const botao = document.createElement('button');
            botao.type = 'button';
            botao.textContent = String(numero);
            botao.setAttribute('aria-label', `Página ${numero}`);
            if (numero === paginaAtual) botao.setAttribute('aria-current', 'page');
            botao.addEventListener('click', function escolherPagina() {
                paginaAtual = numero;
                atualizarConsulta();
                numerosPaginas.querySelector('[aria-current="page"]')?.focus();
            });
            numerosPaginas.append(botao);
        }
        botaoAnterior.disabled = paginaAtual === 1 || total === 0;
        botaoSeguinte.disabled = paginaAtual >= quantidadePaginas || total === 0;
        document.querySelector('.controles-paginacao').hidden = total === 0;
        document.getElementById('resumo-resultados').textContent = `${total} ${total === 1 ? 'processo' : 'processos'}`;
    }

    /** Exibe somente os registros filtrados que pertencem à página selecionada. */
    function atualizarConsulta() {
        const resultado = ordenarRegistros(filtrarRegistros());
        const quantidadePorPagina = Number(seletorQuantidade.value);
        quantidadePaginas = Math.max(1, Math.ceil(resultado.length / quantidadePorPagina));
        paginaAtual = Math.min(paginaAtual, quantidadePaginas);
        registros.forEach(function ocultarRegistro(registro) { registro.hidden = true; });
        const inicio = (paginaAtual - 1) * quantidadePorPagina;
        resultado.slice(inicio, inicio + quantidadePorPagina).forEach(function mostrarRegistro(registro) {
            corpoConsulta.append(registro);
            registro.hidden = false;
        });
        document.querySelector('.tabela-consulta-responsiva').hidden = resultado.length === 0;
        document.getElementById('consulta-vazia').hidden = resultado.length !== 0;
        atualizarSelecaoLote();
        atualizarFiltrosAplicados();
        atualizarPaginacao(resultado.length);
        preferencias.gravar(chaveConsulta(), {
            pesquisa: campoPesquisa.value,
            filtros: Object.fromEntries(camposFiltros.map(campo => [campo.dataset.filtro, campo.value])),
            [seletorOrdenacao.id]: seletorOrdenacao.value,
            [seletorQuantidade.id]: seletorQuantidade.value,
            pagina: paginaAtual,
            item: chaveItem(itemSelecionado),
        });
    }

    /** Retira da seleção processos que deixaram a página visível. */
    function atualizarSelecaoLote() {
        if (!barraLote) return;
        seletoresLote.forEach(campo => {
            if (campo.closest('tr').hidden || campo.closest('td').hidden) campo.checked = false;
        });
        const quantidade = seletoresLote.filter(campo => campo.checked).length;
        barraLote.querySelectorAll('[data-acao-lote]').forEach(botao => {
            botao.disabled = quantidade === 0 || botao.hasAttribute('data-lote-indisponivel');
        });
        document.getElementById('contagem-lote-processos').textContent = quantidade === 1 ? '1 selecionado' : `${quantidade} selecionados`;
    }

    /** Reinicia a paginação quando a pesquisa ou algum filtro muda. */
    function atualizarFiltros() {
        paginaAtual = 1;
        atualizarConsulta();
    }

    /** Atualiza a seleção e mostra o caminho apenas dentro da árvore. */
    function selecionarItem(item, atualizar = true) {
        itemSelecionado.classList.remove('selecionado');
        itemSelecionado.removeAttribute('aria-current');
        itemSelecionado = item;
        if (!item.hasAttribute('data-voltar-ativos')) {
            item.classList.add('selecionado');
            item.setAttribute('aria-current', 'true');
        }
        const caminho = document.getElementById('caminho-selecao');
        caminho.textContent = item.dataset.rotulo;
        caminho.hidden = ['todos', 'acompanhados'].includes(item.dataset.escopo);
        if (atualizar) atualizarFiltros();
    }

    /** Remove a seleção do explorador e todos os filtros, preservando acompanhamentos locais. */
    function limparConsulta() {
        campoPesquisa.value = '';
        camposFiltros.forEach(function limparCampo(campo) { campo.value = ''; });
        selecionarItem(itensExplorador[0]);
        campoPesquisa.focus();
    }

    /** Alterna um acompanhamento somente durante a permanência nesta página. */
    function alternarAcompanhamento(botao) {
        const registro = botao.closest('[data-registro]');
        const acompanhado = registro.dataset.acompanhado !== 'true';
        registro.dataset.acompanhado = String(acompanhado);
        botao.querySelector('span').textContent = acompanhado ? 'Deixar de acompanhar' : 'Acompanhar';
        botao.querySelector('i').className = `${acompanhado ? 'fa-solid' : 'fa-regular'} fa-star`;
        botao.closest('[popover]').hidePopover();
        atualizarConsulta();
        const destinoFoco = registro.hidden ? itemSelecionado : registro.querySelector('.abrir-acoes');
        destinoFoco.focus();
    }

    /** Persiste a estrela e atualiza o filtro Acompanhados sem recarregar a página. */
    async function salvarEstrelaProcesso(botao) {
        const registro = botao.closest('[data-registro]');
        const aviso = document.getElementById('aviso-acompanhamento');
        const tinhaFoco = document.activeElement === botao;
        botao.disabled = true;
        try {
            const estado = registro.dataset.acompanhado === 'true' ? '0' : '1';
            const { acompanhado } = await enviarAlteracaoProcessos(botao.dataset.acompanhamento, { acompanhar: estado });
            registro.dataset.acompanhado = String(acompanhado);
            botao.setAttribute('aria-pressed', String(acompanhado));
            botao.title = acompanhado ? 'Deixar de acompanhar' : 'Acompanhar';
            botao.querySelector('i').className = `${acompanhado ? 'fa-solid' : 'fa-regular'} fa-star`;
            atualizarConsulta();
            aviso.textContent = acompanhado ? 'Processo marcado como acompanhado.' : 'Acompanhamento removido.';
        } catch (falha) {
            aviso.textContent = falha.message;
        } finally {
            botao.disabled = false;
            if (tinhaFoco && document.activeElement === document.body) {
                (registro.hidden ? itemSelecionado : botao).focus();
            }
            aviso.showPopover();
            clearTimeout(temporizadorAcompanhamento);
            temporizadorAcompanhamento = setTimeout(() => aviso.hidePopover(), 2000);
        }
    }

    /** Copia o número do processo e informa o resultado da operação. */
    async function copiarNumero(botao) {
        const registro = botao.closest('[data-registro]');
        const aviso = document.getElementById('aviso-copia-processo');
        try {
            await navigator.clipboard.writeText(registro.dataset.numero);
            aviso.textContent = 'Número do processo copiado.';
        } catch {
            aviso.textContent = `Selecione o número para copiar: ${registro.dataset.numero}`;
        }
        botao.closest('[popover]').hidePopover();
        registro.querySelector('.abrir-acoes').focus();
        aviso.showPopover();
        clearTimeout(temporizadorCopia);
        temporizadorCopia = setTimeout(function ocultarAviso() { aviso.hidePopover(); }, 5000);
    }

    /** Recolhe a árvore em telas pequenas e mantém uma faixa compacta no desktop. */
    function definirExplorador(aberto) {
        arvoreExplorador.hidden = !aberto;
        estruturaExplorador.classList.toggle('explorador-recolhido', !aberto);
        botaoExplorador.setAttribute('aria-expanded', String(aberto));
        botaoExplorador.setAttribute('aria-label', aberto ? 'Recolher explorador' : 'Expandir explorador');
        botaoExplorador.querySelector('i').className = aberto ? 'fa-solid fa-angles-left' : 'fa-solid fa-angles-right';
    }

    /** Permite ajustar a divisória por arraste ou pelas teclas de navegação. */
    function configurarLarguraExplorador() {
        const divisoria = document.getElementById('redimensionar-explorador');
        const painel = divisoria.parentElement;
        const minimo = 180;
        let inicio;

        function larguraMaxima() {
            return Math.max(minimo, Math.min(480, Math.floor(estruturaExplorador.clientWidth * 0.48)));
        }

        function sincronizarLargura() {
            divisoria.setAttribute('aria-valuemax', larguraMaxima());
            if (divisoria.offsetWidth) divisoria.setAttribute('aria-valuenow', painel.offsetWidth);
        }

        function aplicarLargura(largura) {
            const valor = Math.min(larguraMaxima(), Math.max(minimo, Math.round(largura)));
            estruturaExplorador.style.setProperty('--largura-explorador', `min(${valor}px, 48%)`);
            sincronizarLargura();
        }
        const larguraSalva = preferencias.ler('processos:largura');
        if (Number.isFinite(larguraSalva) && larguraSalva >= minimo && larguraSalva <= 480) {
            estruturaExplorador.style.setProperty('--largura-explorador', `min(${larguraSalva}px, 48%)`);
        }

        divisoria.addEventListener('pointerdown', function iniciarAjuste(evento) {
            if (evento.button !== 0 || inicio) return;
            inicio = { ponteiro: evento.pointerId, posicao: evento.clientX, largura: painel.offsetWidth };
            divisoria.setPointerCapture(evento.pointerId);
            divisoria.classList.add('redimensionando');
            evento.preventDefault();
            divisoria.focus();
        });
        divisoria.addEventListener('pointermove', function ajustar(evento) {
            if (inicio?.ponteiro !== evento.pointerId) return;
            const escala = painel.getBoundingClientRect().width / painel.offsetWidth;
            aplicarLargura(inicio.largura + (evento.clientX - inicio.posicao) / escala);
        });
        divisoria.addEventListener('lostpointercapture', function finalizarAjuste() {
            if (inicio) preferencias.gravar('processos:largura', painel.offsetWidth);
            inicio = null;
            divisoria.classList.remove('redimensionando');
        });
        divisoria.addEventListener('keydown', function ajustarComTeclado(evento) {
            const larguras = { ArrowLeft: painel.offsetWidth - 16, ArrowRight: painel.offsetWidth + 16, Home: minimo, End: larguraMaxima() };
            if (!Object.hasOwn(larguras, evento.key)) return;
            evento.preventDefault();
            aplicarLargura(larguras[evento.key]);
            preferencias.gravar('processos:largura', painel.offsetWidth);
        });
        new ResizeObserver(sincronizarLargura).observe(painel);
    }

    /** Reflete a exibição atual ao abrir, descartando escolhas canceladas. */
    function abrirColunas() {
        opcoesColunas.forEach(function marcarOpcao(opcao, indice) {
            opcao.checked = !cabecalhosColunas[indice].hidden;
        });
        avisoColunas.hidden = true;
        dialogoColunas.showModal();
        opcoesColunas[0].focus();
    }

    /** Aplica a seleção ao cabeçalho e aos registros de todas as páginas. */
    function aplicarColunas(evento) {
        if (evento.submitter?.value !== 'aplicar') return;

        const colunasVisiveis = new Set(opcoesColunas.filter(opcao => opcao.checked).map(opcao => opcao.value));
        if (!colunasVisiveis.size) {
            evento.preventDefault();
            avisoColunas.hidden = false;
            opcoesColunas[0].focus();
            return;
        }

        mostrarColunas(colunasVisiveis);
        preferencias.gravar('processos:colunas', Array.from(colunasVisiveis));
        if (opcoesColunas.some(opcao => opcao.value === 'pasta')) preferencias.gravar('processos:coluna-pasta-configurada', true);
    }

    /** Aplica as mesmas colunas à tabela inteira, inclusive às páginas ocultas. */
    function mostrarColunas(colunasVisiveis) {
        tabelaConsulta.querySelectorAll('[data-coluna]').forEach(function atualizarColuna(celula) {
            celula.hidden = !colunasVisiveis.has(celula.dataset.coluna);
        });
        tabelaConsulta.style.setProperty('--quantidade-colunas', colunasVisiveis.size);
        atualizarSelecaoLote();
    }

    function chaveExplorador() {
        return `processos:explorador:${consultaCelular.matches ? 'celular' : 'desktop'}`;
    }

    function restaurarExplorador() {
        definirExplorador(preferencias.ler(chaveExplorador()) ?? !consultaCelular.matches);
    }

    /** Mantém os ramos do explorador abertos por setor e por pasta. */
    function configurarPreferenciasPastas() {
        const ramos = Array.from(arvoreExplorador.querySelectorAll('details[data-pasta-preferencia]'));
        function restaurarRamos() {
            ramos.forEach(function restaurarRamo(ramo) {
                const salvo = preferencias.ler(`${chaveConsulta(false)}:pasta:${ramo.dataset.pastaPreferencia}`);
                if (ramo.hasAttribute('data-pasta-recem-criada')) {
                    ramo.removeAttribute('data-pasta-recem-criada');
                    preferencias.gravar(`${chaveConsulta(false)}:pasta:${ramo.dataset.pastaPreferencia}`, true);
                    return;
                }
                ramo.open = salvo === true;
            });
        }
        restaurarRamos();
        ramos.forEach(ramo => ramo.addEventListener('toggle', function guardarRamo() {
            preferencias.gravar(`${chaveConsulta(false)}:pasta:${ramo.dataset.pastaPreferencia}`, ramo.open);
        }));
        document.addEventListener('contexto-trabalho-alterado', restaurarRamos);
    }

    /** Gerencia as pastas da prévia sem alterar os vínculos dos processos. */
    function configurarPastas() {
        const arvore = document.getElementById('pastas-explorador');
        const ramo = document.getElementById('ramo-pastas');
        const raizPastas = ramo.querySelector('summary');
        const modelo = document.getElementById('modelo-pasta');
        const dialogo = document.getElementById('dialogo-pasta');
        const formulario = dialogo.querySelector('form');
        const nome = formulario.elements.namedItem('nome');
        const visibilidade = formulario.elements.namedItem('visibilidade');
        const confirmar = document.getElementById('confirmar-pasta');
        const erro = document.getElementById('erro-pasta');
        const aviso = document.getElementById('aviso-pastas');
        const destinos = document.getElementById('destinos-pasta');
        const real = Boolean(dialogo.dataset.endereco);
        const mostrarProcessos = document.getElementById('mostrar-processos-pastas');
        const chaveMostrarProcessos = 'processos:mostrar-processos-pastas';
        const processosPorPasta = new Map();
        if (mostrarProcessos) {
            mostrarProcessos.checked = preferencias.ler(chaveMostrarProcessos) === true;
            registros.forEach(registro => {
                for (const pastaId of registro.dataset.pastasProcesso.split(',').filter(Boolean)) {
                    if (!processosPorPasta.has(pastaId)) processosPorPasta.set(pastaId, []);
                    processosPorPasta.get(pastaId).push(registro);
                }
            });
        }
        const responsavel = formulario.elements.namedItem('responsavel');
        let gravando = false;
        const arvoreDestinos = document.getElementById('arvore-destinos-pasta');
        const titulos = { criar: 'Nova pasta', subpasta: 'Nova subpasta', renomear: 'Renomear pasta', visibilidade: 'Alterar visibilidade', mover: 'Mover pasta', confirmarMovimento: 'Confirmar movimento', remover: 'Remover pasta' };
        const listasDestino = new Map();
        let sequencia = 0;
        let acao;
        let pasta;
        let pastaArrastada;
        let alvoArraste;
        let destinoArraste;
        const setorDemo = () => `${contextoDemo.elements.namedItem('unidade').value} / ${contextoDemo.elements.namedItem('setor').value}`;

        /** Exibe as pastas demonstrativas ligadas ao setor em uso. */
        function atualizarPastasDoSetor() {
            const setor = setorDemo();
            const registrosDoSetor = registros.filter(pertenceAoContexto);
            function atualizarPasta(no) {
                const filhas = Array.from(no.querySelector('.filhas-pasta').children).map(atualizarPasta);
                const selecao = no.querySelector('.nome-pasta').dataset;
                const contemProcessos = selecao.escopo === 'pasta' && registrosDoSetor.some(registro =>
                    registro.dataset.grupo === selecao.grupo && registro.dataset.pasta === selecao.valor
                );
                no.hidden = no.dataset.local !== setor && !contemProcessos && !filhas.includes(true);
                return !no.hidden;
            }
            Array.from(arvore.children).forEach(atualizarPasta);
        }
        const avisoMovimento = document.getElementById('aviso-movimento-pasta');
        const desfazer = document.getElementById('desfazer-movimento-pasta');
        const barraDesfazer = avisoMovimento.querySelector('.progresso-desfazer');
        const prazoDesfazer = 5000;
        let executarReversao;
        let temporizadorMovimento;
        let animacaoMovimento;

        /** Envia as ações reais com CSRF e conserva o token renovado na página. */
        async function gravarPasta(tipo, no, destino = null, token = '') {
            if (gravando) throw new Error('Aguarde a gravação em andamento.');
            gravando = true;
            const id = elemento => elemento?.querySelector('.nome-pasta')?.dataset.valor ?? '';
            const dados = {
                acao: tipo, pasta: id(no), destino: id(destino?.closest('.no-pasta')),
                origem: id(no?.parentElement.closest('.no-pasta')), nome: nome.value.trim(), token,
                visibilidade: visibilidade.value, setor_contexto: dialogo.dataset.setor,
                responsavel: responsavel?.value ?? '',
            };
            try {
                return await enviarAlteracaoProcessos(dialogo.dataset.endereco, dados);
            } finally {
                gravando = false;
            }
        }

        function atualizarEstadoVazio() {
            if (real) document.getElementById('pastas-reais-vazias').hidden = Boolean(arvore.childElementCount);
        }

        /** Atualiza o caminho mostrado na consulta sem trocar as chaves originais. */
        function atualizarCaminhos(no) {
            const botao = no.querySelector('.nome-pasta');
            const superior = no.parentElement.closest('.no-pasta');
            const rotulo = no.dataset.nome;
            botao.querySelector('.rotulo-pasta').textContent = rotulo;
            botao.dataset.rotulo = superior ? `${superior.querySelector('.nome-pasta').dataset.rotulo} / ${rotulo}` : rotulo;
            no.querySelector('.abrir-acoes-pasta').setAttribute('aria-label', `Ações da pasta ${rotulo}`);
            no.querySelector('.expandir-pasta').setAttribute('aria-label', `Subpastas de ${rotulo}`);
            no.querySelector('.filhas-pasta').querySelectorAll(':scope > .no-pasta').forEach(atualizarCaminhos);
        }

        /** Mostra as subpastas e, quando solicitado, os processos diretamente vinculados. */
        function definirRamo(no, aberto, guardar = true) {
            const lista = no.querySelector('.filhas-pasta');
            const botao = no.querySelector('.expandir-pasta');
            lista.hidden = !aberto;
            const vinculados = processosPorPasta.get(no.dataset.ramoPreferencia) ?? [];
            botao.hidden = !lista.childElementCount && !(mostrarProcessos?.checked && vinculados.length);
            botao.setAttribute('aria-expanded', String(aberto));
            if (mostrarProcessos) {
                const itens = no.querySelector(':scope > .processos-da-pasta');
                itens.hidden = !aberto || !mostrarProcessos.checked || !vinculados.length;
                if (!itens.hidden && !itens.childElementCount) preencherProcessosPasta(itens, vinculados);
            }
            if (guardar && no.dataset.ramoPreferencia) preferencias.gravar(`${chaveConsulta(false)}:ramo:${no.dataset.ramoPreferencia}`, aberto);
        }

        /** Reutiliza os processos já autorizados e os mesmos links da tabela. */
        function preencherProcessosPasta(lista, vinculados) {
            vinculados.forEach(registro => {
                const linha = document.createElement('li');
                const endereco = registro.querySelector('a.abrir-consulta');
                const item = document.createElement(endereco ? 'a' : 'span');
                item.className = 'processo-na-pasta';
                if (endereco) item.href = endereco.href;
                const icone = document.createElement('i');
                icone.className = 'fa-regular fa-file-lines';
                icone.setAttribute('aria-hidden', 'true');
                item.append(icone, registro.dataset.assunto);
                item.title = registro.dataset.numero;
                linha.append(item);
                lista.append(linha);
            });
        }

        mostrarProcessos?.addEventListener('change', function alternarProcessosNasPastas() {
            preferencias.gravar(chaveMostrarProcessos, mostrarProcessos.checked);
            arvore.querySelectorAll('.no-pasta').forEach(no => definirRamo(no, !no.querySelector('.filhas-pasta').hidden, false));
        });

        /** Valida o mesmo destino para o modal e para o arraste. */
        function permiteMover(no, lista) {
            const superior = lista.closest('.no-pasta');
            return lista !== no.parentElement && !no.contains(lista)
                && (!superior || no.dataset.local === superior.dataset.local);
        }

        /** Move o nó inteiro e mantém a seleção e os caminhos atualizados. */
        function moverPasta(no, destino, referencia = null) {
            const origem = no.parentElement;
            destino.insertBefore(no, referencia);
            const superiorOrigem = origem.closest('.no-pasta');
            if (superiorOrigem) definirRamo(superiorOrigem, Boolean(origem.childElementCount));
            for (let superior = destino.closest('.no-pasta'); superior; superior = superior.parentElement.closest('.no-pasta')) {
                definirRamo(superior, true);
            }
            ramo.open = true;
            atualizarCaminhos(no);
            selecionarItem(itemSelecionado);
        }

        /** Reinicia o aviso e preenche a barra da esquerda para a direita. */
        function mostrarReversao(mensagem, refazer, tempo, executar) {
            executarReversao = executar;
            avisoMovimento.querySelector('[role="status"]').textContent = mensagem;
            desfazer.textContent = refazer ? 'Refazer' : 'Desfazer';
            avisoMovimento.hidden = false;
            clearTimeout(temporizadorMovimento);
            animacaoMovimento?.cancel();
            animacaoMovimento = barraDesfazer.animate(
                [{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }],
                { duration: tempo, fill: 'forwards' }
            );
            temporizadorMovimento = setTimeout(encerrarReversao, tempo);
        }

        /** Encerra o prazo da ação exibida. */
        function encerrarReversao() {
            clearTimeout(temporizadorMovimento);
            avisoMovimento.hidden = true;
            animacaoMovimento?.cancel();
            executarReversao = null;
        }

        /** Solicita a reversão no servidor e atualiza a árvore com a resposta. */
        function mostrarHistoricoReal(historico, mensagem) {
            if (!historico) return;
            mostrarReversao(mensagem, historico.refazer, historico.tempo, async function reverterNoBanco() {
                const retorno = await gravarPasta(historico.refazer ? 'refazer' : 'desfazer', null, null, historico.token);
                montarArvoreReal(retorno.pastas);
                const restaurada = Array.from(arvore.querySelectorAll('.no-pasta'))
                    .find(no => no.dataset.ramoPreferencia === retorno.pastaId);
                for (let superior = restaurada?.parentElement.closest('.no-pasta'); superior; superior = superior.parentElement.closest('.no-pasta')) {
                    definirRamo(superior, true);
                }
                mostrarHistoricoReal(retorno.historico, historico.refazer ? 'Alteração refeita.' : 'Alteração desfeita.');
            });
        }

        /** Captura a posição e os campos editáveis de uma pasta na demonstração. */
        function capturarPastaDemo(no) {
            return no && {
                lista: no.parentElement, proxima: no.nextElementSibling, dados: { ...no.dataset },
                titulo: no.querySelector('.nome-pasta').title,
            };
        }

        /** Alterna entre os dois estados da última alteração demonstrativa. */
        function mostrarHistoricoDemo(no, antes, depois, mensagem, refazer = false) {
            mostrarReversao(mensagem, refazer, prazoDesfazer, function reverterNaPrevia() {
                const estado = refazer ? depois : antes;
                const origem = no.parentElement?.closest('.no-pasta');
                if (estado) {
                    estado.lista.insertBefore(no, estado.proxima);
                    Object.assign(no.dataset, estado.dados);
                    atualizarCaminhos(no);
                    no.querySelector('.nome-pasta').title = estado.titulo;
                    for (let superior = estado.lista.closest('.no-pasta'); superior; superior = superior.parentElement.closest('.no-pasta')) {
                        definirRamo(superior, true);
                    }
                } else {
                    no.remove();
                }
                if (origem) definirRamo(origem, Boolean(origem.querySelector('.filhas-pasta').childElementCount));
                selecionarItem(itemSelecionado.isConnected ? itemSelecionado : itensExplorador[0]);
                mostrarHistoricoDemo(no, antes, depois, refazer ? 'Alteração refeita.' : 'Alteração desfeita.', !refazer);
            });
        }

        desfazer.addEventListener('click', async function aplicarReversao() {
            if (!executarReversao || gravando) return;
            desfazer.disabled = true;
            try {
                await executarReversao();
            } catch (falha) {
                encerrarReversao();
                aviso.textContent = falha.message;
                raizPastas.focus();
            } finally {
                desfazer.disabled = false;
                if (!avisoMovimento.hidden) desfazer.focus();
            }
        });

        /** Monta a árvore de destinos e sinaliza as opções indisponíveis. */
        function montarDestinos() {
            listasDestino.clear();
            arvoreDestinos.replaceChildren();

            function adicionarDestino(lista, superior, nivel) {
                const opcao = document.createElement('label');
                const campo = document.createElement('input');
                const rotulo = superior?.querySelector('.nome-pasta').dataset.rotulo ?? 'Raiz de Pastas';
                campo.type = 'radio';
                campo.name = 'destino';
                campo.value = String(listasDestino.size);
                campo.required = true;
                campo.disabled = !permiteMover(pasta, lista);
                listasDestino.set(campo.value, lista);
                opcao.style.setProperty('--nivel-destino', Math.min(nivel, 4));
                opcao.append(campo, rotulo);
                arvoreDestinos.append(opcao);
                lista.querySelectorAll(':scope > .no-pasta').forEach(function adicionarSubpasta(no) {
                    adicionarDestino(no.querySelector('.filhas-pasta'), no, nivel + 1);
                });
            }

            adicionarDestino(arvore, null, 0);
            return arvoreDestinos.querySelector('input:not(:disabled)');
        }

        /** Escolhe um nome livre entre as pastas irmãs, ignorando a pasta renomeada. */
        function nomeDisponivelPasta(lista, nome, pastaAtual = null) {
            const irmas = Array.from(lista.children).filter(no => no !== pastaAtual);
            const ocupados = new Set(irmas.map(no => no.dataset.nome.toLowerCase()));
            const caracteres = Array.from(nome);
            let candidato = nome;
            for (let numero = 2; ocupados.has(candidato.toLowerCase()); numero++) {
                const sufixo = ` (${numero})`;
                candidato = caracteres.slice(0, 200 - sufixo.length).join('') + sufixo;
            }
            return candidato;
        }

        /** Cria um nó da árvore e vincula sua seleção à listagem. */
        function inserirPasta(lista, rotulo, escopo, grupo = '', valor = '') {
            const no = modelo.content.firstElementChild.cloneNode(true);
            const identificador = ++sequencia;
            const menu = no.querySelector('.acoes-pasta');
            const botao = no.querySelector('.nome-pasta');
            menu.id = `acoes-pasta-${identificador}`;
            no.querySelector('.abrir-acoes-pasta').setAttribute('popovertarget', menu.id);
            const filhas = no.querySelector('.filhas-pasta');
            filhas.id = `filhas-pasta-${identificador}`;
            const expandir = no.querySelector('.expandir-pasta');
            expandir.setAttribute('aria-controls', filhas.id);
            if (real) {
                const processos = no.querySelector(':scope > .processos-da-pasta');
                processos.id = `processos-da-pasta-${identificador}`;
                expandir.setAttribute('aria-controls', `${filhas.id} ${processos.id}`);
            }
            expandir.addEventListener('click', function alternarRamo() { definirRamo(no, filhas.hidden); });
            no.dataset.nome = rotulo;
            Object.assign(botao.dataset, { escopo, grupo, valor });
            botao.addEventListener('click', function consultarPasta() { selecionarItem(botao); });
            lista.append(no);
            atualizarCaminhos(no);
            return no;
        }

        /** Abre o formulário com os campos relevantes para a ação escolhida. */
        function abrirAcao(evento) {
            const botao = evento.target.closest('[data-acao-pasta]');
            if (!botao) return;
            botao.closest('[popover]')?.hidePopover();
            prepararAcao(botao.dataset.acaoPasta, botao.closest('.no-pasta'));
        }

        /** Preenche o mesmo modal para ações do menu e confirmação de arraste. */
        function prepararAcao(tipo, no) {
            acao = tipo;
            pasta = no;
            const criando = acao === 'criar' || acao === 'subpasta';
            const removendo = acao === 'remover';
            const movendo = acao === 'mover';
            const confirmandoMovimento = acao === 'confirmarMovimento';
            const bloqueada = removendo && (pasta.querySelector('.filhas-pasta').childElementCount > 0 || pasta.dataset.ocupada === 'true' || pasta.querySelector('.nome-pasta').dataset.escopo === 'pasta');
            formulario.reset();
            destinos.hidden = !movendo;
            destinos.disabled = !movendo;
            const destinoDisponivel = movendo ? montarDestinos() : null;
            nome.closest('label').hidden = !criando && acao !== 'renomear';
            nome.required = !nome.closest('label').hidden;
            nome.value = acao === 'renomear' ? pasta.dataset.nome : '';
            visibilidade.closest('label').hidden = !criando && acao !== 'visibilidade';
            visibilidade.value = pasta?.dataset.visibilidade ?? (real ? 'setor' : '');
            if (real && visibilidade.selectedIndex < 0) visibilidade.value = 'setor';
            if (responsavel) {
                responsavel.closest('label').hidden = acao !== 'subpasta';
                responsavel.required = acao === 'subpasta';
            }
            document.getElementById('titulo-pasta').textContent = titulos[acao];
            const caminho = pasta?.querySelector('.nome-pasta').dataset.rotulo;
            const caminhoDestino = destinoArraste?.closest('.no-pasta')?.querySelector('.nome-pasta').dataset.rotulo ?? 'Raiz de Pastas';
            document.getElementById('contexto-pasta').textContent = removendo && !bloqueada
                ? `Remover “${caminho}” do Explorador?`
                : caminho ?? `Setor selecionado: ${setorDemo()}.`;
            if (confirmandoMovimento) {
                document.getElementById('contexto-pasta').textContent = `Mover a pasta “${caminho}” para “${caminhoDestino}”?`;
            }
            erro.textContent = movendo ? 'Nenhum destino disponível no mesmo local.' : 'Para remover esta pasta, reorganize primeiro os processos ou subpastas.';
            erro.hidden = !bloqueada && (!movendo || Boolean(destinoDisponivel));
            confirmar.hidden = !erro.hidden;
            confirmar.textContent = removendo ? 'Remover' : movendo || confirmandoMovimento ? 'Mover pasta' : 'Salvar';
            dialogo.showModal();
            if (confirmandoMovimento) formulario.querySelector('[value="cancelar"]').focus();
            else if (nome.required) nome.focus();
            else if (destinoDisponivel) destinoDisponivel.focus();
            else if (!visibilidade.closest('label').hidden) visibilidade.focus();
        }

        /** Aplica a ação confirmada e mantém a árvore e a lista sincronizadas. */
        async function salvarPasta(evento) {
            if (evento.submitter?.value !== 'salvar' || confirmar.hidden) {
                evento.preventDefault();
                return;
            }
            const rotulo = nome.value.trim();
            if (nome.required && !rotulo) {
                evento.preventDefault();
                erro.textContent = 'Informe o nome da pasta.';
                erro.hidden = false;
                nome.focus();
                return;
            }

            const movendo = acao === 'mover' || acao === 'confirmarMovimento';
            const destino = acao === 'mover' ? listasDestino.get(formulario.elements.namedItem('destino').value) : destinoArraste;
            const antes = !real && !['criar', 'subpasta'].includes(acao) ? capturarPastaDemo(pasta) : null;
            let resultado;
            let historico;
            if (real) {
                evento.preventDefault();
                if (gravando) return;
                const cancelar = formulario.querySelector('[value="cancelar"]');
                confirmar.disabled = true;
                cancelar.disabled = true;
                try {
                    const retorno = await gravarPasta(movendo ? 'mover' : acao, pasta, destino);
                    resultado = retorno.pasta;
                    historico = retorno.historico;
                } catch (falha) {
                    erro.textContent = falha.message;
                    erro.hidden = false;
                    return;
                } finally {
                    confirmar.disabled = false;
                    cancelar.disabled = false;
                }
            }
            let foco;
            if (movendo) {
                moverPasta(pasta, destino);
                foco = pasta.querySelector('.nome-pasta');
            } else if (acao === 'remover' || resultado?.visivel === false) {
                const superior = pasta.parentElement.closest('.no-pasta');
                if (pasta.contains(itemSelecionado)) selecionarItem(itensExplorador[0]);
                pasta.remove();
                if (superior) definirRamo(superior, Boolean(superior.querySelector('.filhas-pasta').childElementCount));
                foco = superior?.querySelector('.nome-pasta') ?? ramo.querySelector('summary');
            } else {
                if (acao === 'criar' || acao === 'subpasta') {
                    const superior = pasta;
                    const lista = superior?.querySelector('.filhas-pasta') ?? arvore;
                    pasta = real ? inserirPastaReal(lista, resultado) : inserirPasta(lista, nomeDisponivelPasta(lista, rotulo), 'pasta-local');
                    const localCriacao = superior?.dataset.local ?? (real ? dialogo.dataset.setor : setorDemo());
                    if (localCriacao !== undefined) pasta.dataset.local = localCriacao;
                    if (superior) definirRamo(superior, true);
                    ramo.open = true;
                }
                if (acao === 'renomear') {
                    pasta.dataset.nome = real ? resultado.nome : nomeDisponivelPasta(pasta.parentElement, rotulo, pasta);
                    atualizarCaminhos(pasta);
                    if (pasta.contains(itemSelecionado)) selecionarItem(itemSelecionado);
                } else {
                    pasta.dataset.visibilidade = visibilidade.value;
                }
                foco = pasta.querySelector('.nome-pasta');
                const localPasta = pasta.dataset.local === '' ? 'Pasta pessoal' : pasta.dataset.local;
                const acessoPasta = pasta.dataset.visibilidade === '' ? 'Privada' : pasta.dataset.visibilidade;
                foco.title = [localPasta, acessoPasta].filter(Boolean).join(' · ');
            }
            aviso.textContent = acao === 'mover' || acao === 'confirmarMovimento'
                ? `Pasta movida para ${foco.dataset.rotulo}.`
                : real ? 'Alteração salva.' : `${titulos[acao]}: alteração aplicada na prévia.`;
            atualizarEstadoVazio();
            dialogo.addEventListener('close', function restaurarFocoPasta() { foco.focus(); }, { once: true });
            if (real) dialogo.close();
            const mensagem = movendo ? 'Pasta movida.' : {
                criar: 'Pasta criada.', subpasta: 'Subpasta criada.', remover: 'Pasta removida.',
                renomear: 'Pasta renomeada.', visibilidade: 'Visibilidade alterada.',
            }[acao];
            if (real) mostrarHistoricoReal(historico, mensagem);
            else mostrarHistoricoDemo(pasta, antes, acao === 'remover' ? null : capturarPastaDemo(pasta), mensagem);
        }

        /** Reutiliza a mesma linha do demo com o identificador real da pasta. */
        function inserirPastaReal(lista, registro) {
            const no = inserirPasta(lista, registro.nome, 'pasta-real', '', String(registro.id));
            no.dataset.local = dialogo.dataset.setor;
            no.dataset.visibilidade = registro.visibilidade;
            no.dataset.ramoPreferencia = String(registro.id);
            no.dataset.ocupada = String(Number(registro.ocupada) === 1);
            const quantidade = processosPorPasta.get(no.dataset.ramoPreferencia)?.length ?? 0;
            no.querySelector('.contagem-pasta').textContent = `(${quantidade})`;
            no.querySelector('.nome-pasta').setAttribute('aria-description', quantidade === 1 ? '1 processo' : `${quantidade} processos`);
            return no;
        }

        /** Reconstrói a árvore real e mantém as preferências de expansão e seleção. */
        function montarArvoreReal(registros) {
            const chave = chaveItem(itemSelecionado);
            arvore.replaceChildren();
            const porPai = new Map();
            registros.forEach(registro => {
                const pai = String(registro.pasta_pai_id ?? '');
                if (!porPai.has(pai)) porPai.set(pai, []);
                porPai.get(pai).push(registro);
            });
            function montarPastasReais(lista, pai) {
                (porPai.get(pai) ?? []).forEach(registro => {
                    const no = inserirPastaReal(lista, registro);
                    montarPastasReais(no.querySelector('.filhas-pasta'), String(registro.id));
                    definirRamo(no, preferencias.ler(`${chaveConsulta(false)}:ramo:${registro.id}`) === true, false);
                });
            }
            montarPastasReais(arvore, '');
            atualizarEstadoVazio();
            if (!itemSelecionado.isConnected) {
                const selecionado = Array.from(arvore.querySelectorAll('.nome-pasta')).find(botao => chaveItem(botao) === chave);
                selecionarItem(selecionado ?? itensExplorador[0]);
            }
        }

        if (real) {
            montarArvoreReal(JSON.parse(arvore.dataset.pastasReais));
            mostrarHistoricoReal(JSON.parse(avisoMovimento.dataset.historico), 'Pasta cadastrada.');
        } else Object.entries(JSON.parse(arvore.dataset.pastas)).forEach(function montarGrupo([grupo, subpastas]) {
            const no = inserirPasta(arvore, grupo, 'grupo-pasta', grupo);
            no.dataset.ramoPreferencia = grupo;
            const lista = no.querySelector('.filhas-pasta');
            Object.keys(subpastas).forEach(function montarSubpasta(rotulo) { inserirPasta(lista, rotulo, 'pasta', grupo, rotulo); });
            definirRamo(no, preferencias.ler(`${chaveConsulta(false)}:ramo:${grupo}`) === true, false);
        });
        /** Retorna o destino associado à linha ou ao cabeçalho da raiz. */
        function obterAlvoArraste(elemento) {
            const linha = elemento.closest('.linha-pasta');
            if (linha) return { destaque: linha, lista: linha.parentElement.querySelector('.filhas-pasta') };
            if (raizPastas.contains(elemento)) return { destaque: raizPastas, lista: arvore };
            return null;
        }

        /** Remove o destaque visual ao sair de um destino ou encerrar o arraste. */
        function destacarAlvo(elemento) {
            alvoArraste?.classList.remove('destino-arraste');
            alvoArraste = elemento;
            alvoArraste?.classList.add('destino-arraste');
        }

        ramo.addEventListener('dragstart', function iniciarArraste(evento) {
            const botao = evento.target.closest('.nome-pasta');
            if (!botao) return;
            pastaArrastada = botao.closest('.no-pasta');
            evento.dataTransfer.effectAllowed = 'move';
            evento.dataTransfer.setData('text/plain', botao.dataset.rotulo);
        });
        ramo.addEventListener('dragover', function indicarDestino(evento) {
            const alvo = obterAlvoArraste(evento.target);
            if (!pastaArrastada || !alvo || !permiteMover(pastaArrastada, alvo.lista)) {
                destacarAlvo(null);
                return;
            }
            evento.preventDefault();
            evento.dataTransfer.dropEffect = 'move';
            destacarAlvo(alvo.destaque);
        });
        ramo.addEventListener('dragleave', function retirarDestaque(evento) {
            if (!alvoArraste?.contains(evento.relatedTarget)) destacarAlvo(null);
        });
        ramo.addEventListener('drop', function soltarPasta(evento) {
            const alvo = obterAlvoArraste(evento.target);
            const no = pastaArrastada;
            pastaArrastada = null;
            destacarAlvo(null);
            if (!no || !alvo || !permiteMover(no, alvo.lista)) return;
            evento.preventDefault();
            destinoArraste = alvo.lista;
            no.querySelector('.nome-pasta').focus();
            prepararAcao('confirmarMovimento', no);
        });
        ramo.addEventListener('dragend', function encerrarArraste() {
            pastaArrastada = null;
            destacarAlvo(null);
        });
        arvore.closest('.secao-pastas').addEventListener('click', abrirAcao);
        formulario.addEventListener('submit', salvarPasta);
        dialogo.addEventListener('cancel', evento => { if (gravando) evento.preventDefault(); });
        dialogo.addEventListener('close', function limparDestinoArraste() { destinoArraste = null; });
        if (real) return;
        document.addEventListener('contexto-trabalho-alterado', encerrarReversao);
        document.addEventListener('contexto-trabalho-alterado', atualizarPastasDoSetor);
        document.addEventListener('contexto-trabalho-alterado', function restaurarGrupos() {
            arvore.querySelectorAll('[data-ramo-preferencia]').forEach(no => {
                definirRamo(no, preferencias.ler(`${chaveConsulta(false)}:ramo:${no.dataset.ramoPreferencia}`) === true, false);
            });
        });
        atualizarPastasDoSetor();
    }

    if (document.getElementById('pastas-explorador')) configurarPastas();
    if (barraLote) {
        const dialogo = document.getElementById('dialogo-lote-processos');
        const formulario = dialogo.querySelector('form');
        const destino = formulario.elements.namedItem('destino');
        const botaoConfirmar = formulario.querySelector('[type="submit"]');
        const acoes = {
            mover: ['Mover para', 'Escolha a pasta de destino dos processos selecionados.', 'Mover'],
            arquivar: ['Arquivar selecionados', 'Confirme o arquivamento dos processos selecionados.', 'Arquivar'],
            excluir: ['Excluir selecionados', 'Os processos selecionados irão para a Lixeira e poderão ser restaurados.', 'Mover para a Lixeira'],
            restaurar: ['Restaurar selecionados', 'Os processos selecionados voltarão à caixa anterior.', 'Restaurar'],
        };
        seletoresLote.forEach(campo => campo.addEventListener('change', atualizarSelecaoLote));
        barraLote.querySelectorAll('[data-acao-lote]').forEach(botao => {
            botao.addEventListener('click', function abrirLote() {
                const acao = botao.dataset.acaoLote;
                const [titulo, descricao, confirmar] = acoes[acao];
                const selecionados = seletoresLote.filter(campo => campo.checked);
                formulario.reset();
                formulario.elements.namedItem('acao').value = acao;
                document.getElementById('titulo-lote-processos').textContent = titulo;
                document.getElementById('descricao-lote-processos').textContent = `${selecionados.length} · ${descricao}`;
                document.getElementById('itens-lote-processos').replaceChildren(...selecionados.map(campo => {
                    const item = document.createElement('input');
                    item.type = 'hidden';
                    item.name = 'processos[]';
                    item.value = campo.value;
                    return item;
                }));
                destino.closest('label').hidden = acao !== 'mover';
                destino.disabled = acao !== 'mover';
                destino.required = acao === 'mover';
                botaoConfirmar.disabled = acao === 'mover' && destino.options.length === 1;
                botaoConfirmar.classList.toggle('btn-danger', acao === 'excluir');
                botaoConfirmar.classList.toggle('btn-primary', acao !== 'excluir');
                botaoConfirmar.textContent = confirmar;
                dialogo.showModal();
                (acao === 'mover' ? destino : formulario.querySelector('[data-cancelar-dialogo]')).focus();
            });
        });
    }
    const dialogoPastaProcesso = document.getElementById('dialogo-pasta-processo');
    /** Preenche o modal com o processo selecionado e os vínculos que ele pode trocar. */
    document.querySelectorAll('[data-mover-processo]').forEach(botao => {
        botao.addEventListener('click', function abrirPastaProcesso() {
            const formulario = dialogoPastaProcesso.querySelector('form');
            formulario.reset();
            formulario.action = botao.dataset.moverProcesso;
            const origem = formulario.elements.namedItem('origem');
            const destino = formulario.elements.namedItem('destino');
            const pastas = new Set(JSON.parse(botao.dataset.pastasVinculadas).map(String));
            const vinculadas = Array.from(destino.options).filter(opcao => pastas.has(opcao.value));
            origem.replaceChildren(...(vinculadas.length ? vinculadas.map(opcao => new Option(opcao.text, opcao.value)) : [new Option('Sem pasta', '')]));
            origem.closest('label').hidden = vinculadas.length < 2;
            destino.value = origem.value;
            origem.onchange = () => { destino.value = origem.value; };
            document.getElementById('processo-da-pasta').textContent = botao.closest('tr').dataset.assunto;
            dialogoPastaProcesso.showModal();
            destino.focus();
        });
    });
    configurarPreferenciasPastas();
    document.getElementById('abrir-pasta-real')?.addEventListener('click', function abrirCadastroPasta() {
        document.getElementById('dialogo-pasta-real').showModal();
    });
    configurarLarguraExplorador();
    document.getElementById('abrir-colunas').addEventListener('click', abrirColunas);
    dialogoColunas.querySelector('form').addEventListener('submit', aplicarColunas);
    dialogoColunas.addEventListener('change', function limparAvisoColunas() { avisoColunas.hidden = true; });
    campoPesquisa.addEventListener('input', atualizarFiltros);
    document.addEventListener('contexto-trabalho-alterado', restaurarConsulta);
    camposFiltros.forEach(function configurarFiltro(campo) { campo.addEventListener('change', atualizarFiltros); });
    seletorOrdenacao.addEventListener('change', atualizarFiltros);
    seletorQuantidade.addEventListener('change', atualizarFiltros);
    itensExplorador.forEach(function configurarItem(item) {
        item.addEventListener('click', function navegar() {
            if (item.dataset.voltarAtivos) {
                const chave = chaveConsulta(false);
                preferencias.gravar(chave, { ...preferencias.ler(chave), item: chaveItem(item), pagina: 1 });
                location.assign(item.dataset.voltarAtivos);
                return;
            }
            selecionarItem(item);
        });
    });
    document.querySelectorAll('[data-limpar-consulta]').forEach(function configurarLimpeza(botao) { botao.addEventListener('click', limparConsulta); });
    document.querySelectorAll('.acompanhar-processo').forEach(function configurarAcompanhamento(botao) { botao.addEventListener('click', function acompanhar() { alternarAcompanhamento(botao); }); });
    document.querySelectorAll('[data-acompanhamento]').forEach(botao => {
        botao.addEventListener('click', function alternarEstrela() { salvarEstrelaProcesso(botao); });
    });
    document.querySelectorAll('.copiar-numero').forEach(function configurarCopia(botao) { botao.addEventListener('click', function copiar() { copiarNumero(botao); }); });
    botaoFiltros.addEventListener('click', function alternarFiltros() {
        painelFiltros.hidden = !painelFiltros.hidden;
        botaoFiltros.setAttribute('aria-expanded', String(!painelFiltros.hidden));
        preferencias.gravar('processos:painel-filtros', !painelFiltros.hidden);
    });
    botaoAnterior.addEventListener('click', function voltarPagina() { if (paginaAtual > 1) { paginaAtual--; atualizarConsulta(); } });
    botaoSeguinte.addEventListener('click', function avancarPagina() { if (paginaAtual < quantidadePaginas) { paginaAtual++; atualizarConsulta(); } });
    botaoExplorador.addEventListener('click', function alternarExplorador() {
        const aberto = arvoreExplorador.hidden;
        definirExplorador(aberto);
        preferencias.gravar(chaveExplorador(), aberto);
    });
    consultaCelular.addEventListener('change', restaurarExplorador);
    restaurarExplorador();
    const colunasSalvas = preferencias.ler('processos:colunas');
    if (Array.isArray(colunasSalvas)) {
        const disponiveis = new Set(opcoesColunas.map(opcao => opcao.value).filter(coluna => colunasSalvas.includes(coluna)));
        if (opcoesColunas.some(opcao => opcao.value === 'pasta') && preferencias.ler('processos:coluna-pasta-configurada') !== true) disponiveis.add('pasta');
        if (disponiveis.size) mostrarColunas(disponiveis);
    }
    painelFiltros.hidden = preferencias.ler('processos:painel-filtros') !== true;
    botaoFiltros.setAttribute('aria-expanded', String(!painelFiltros.hidden));
    restaurarConsulta();
})();
