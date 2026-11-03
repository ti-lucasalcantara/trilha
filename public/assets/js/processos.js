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
    const botaoExplorador = document.getElementById('alternar-explorador');
    const arvoreExplorador = document.getElementById('arvore-explorador');
    const consultaCelular = window.matchMedia('(max-width: 650px)');
    let itemSelecionado = itensExplorador[0];
    let paginaAtual = 1;
    let quantidadePaginas = 1;

    /** Permite pesquisar textos sem diferenciar acentos ou letras maiúsculas. */
    function normalizarTexto(texto) {
        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    }

    /** Formata a data demonstrativa sem conversões de fuso horário. */
    function formatarData(data) {
        return data.split('-').reverse().join('/');
    }

    /** Verifica o vínculo do exemplo com a unidade, setor ou pasta selecionados. */
    function correspondeExplorador(registro) {
        const selecao = itemSelecionado.dataset;
        const dados = registro.dataset;
        if (selecao.escopo === 'acompanhados') return dados.acompanhado === 'true';
        if (selecao.escopo === 'unidade') return dados.unidade === selecao.valor;
        if (selecao.escopo === 'setor') return dados.unidade === selecao.unidade && dados.setor === selecao.valor;
        if (selecao.escopo === 'pasta') return dados.grupo === selecao.grupo && dados.pasta === selecao.valor;
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
            const texto = normalizarTexto([dados.numero, dados.assunto, dados.interessado].join(' '));
            if (!texto.includes(pesquisa)) return false;
            return camposFiltros.every(function verificarCampo(campo) {
                if (!campo.value) return true;
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
    function atualizarPaginacao(total, quantidadePorPagina) {
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
        const inicio = total === 0 ? 0 : (paginaAtual - 1) * quantidadePorPagina + 1;
        const fim = Math.min(paginaAtual * quantidadePorPagina, total);
        document.getElementById('resumo-resultados').textContent = `${inicio}–${fim} de ${total} ${total === 1 ? 'processo' : 'processos'}`;
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
        document.getElementById('total-acompanhados').textContent = String(registros.filter(function contarAcompanhado(registro) { return registro.dataset.acompanhado === 'true'; }).length);
        atualizarFiltrosAplicados();
        atualizarPaginacao(resultado.length, quantidadePorPagina);
    }

    /** Reinicia a paginação quando a pesquisa ou algum filtro muda. */
    function atualizarFiltros() {
        paginaAtual = 1;
        atualizarConsulta();
    }

    /** Atualiza o item selecionado, o título e o caminho do explorador. */
    function selecionarItem(item) {
        itemSelecionado = item;
        itensExplorador.forEach(function atualizarSelecao(candidato) {
            candidato.classList.toggle('selecionado', candidato === item);
            if (candidato === item) candidato.setAttribute('aria-current', 'true');
            else candidato.removeAttribute('aria-current');
        });
        document.getElementById('titulo-consulta').textContent = item.dataset.rotulo.split(' / ').at(-1);
        document.getElementById('caminho-selecao').textContent = item.dataset.rotulo;
        atualizarFiltros();
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
        botao.classList.toggle('acompanhado', acompanhado);
        botao.setAttribute('aria-pressed', String(acompanhado));
        botao.setAttribute('aria-label', `${acompanhado ? 'Deixar de acompanhar' : 'Acompanhar'} ${registro.dataset.numero}`);
        botao.querySelector('i').className = `${acompanhado ? 'fa-solid' : 'fa-regular'} fa-star`;
        atualizarConsulta();
    }

    /** Recolhe a árvore em telas pequenas e mantém uma faixa compacta no desktop. */
    function definirExplorador(aberto) {
        arvoreExplorador.hidden = !aberto;
        document.querySelector('.estrutura-explorador').classList.toggle('explorador-recolhido', !aberto);
        botaoExplorador.setAttribute('aria-expanded', String(aberto));
        botaoExplorador.setAttribute('aria-label', aberto ? 'Recolher explorador' : 'Expandir explorador');
        botaoExplorador.querySelector('i').className = aberto ? 'fa-solid fa-chevron-up' : 'fa-solid fa-chevron-down';
    }

    campoPesquisa.addEventListener('input', atualizarFiltros);
    camposFiltros.forEach(function configurarFiltro(campo) { campo.addEventListener('change', atualizarFiltros); });
    seletorOrdenacao.addEventListener('change', atualizarFiltros);
    seletorQuantidade.addEventListener('change', atualizarFiltros);
    itensExplorador.forEach(function configurarItem(item) { item.addEventListener('click', function navegar() { selecionarItem(item); }); });
    document.querySelectorAll('[data-limpar-consulta]').forEach(function configurarLimpeza(botao) { botao.addEventListener('click', limparConsulta); });
    document.querySelectorAll('.acompanhar-processo').forEach(function configurarAcompanhamento(botao) { botao.addEventListener('click', function acompanhar() { alternarAcompanhamento(botao); }); });
    botaoAnterior.addEventListener('click', function voltarPagina() { if (paginaAtual > 1) { paginaAtual--; atualizarConsulta(); } });
    botaoSeguinte.addEventListener('click', function avancarPagina() { if (paginaAtual < quantidadePaginas) { paginaAtual++; atualizarConsulta(); } });
    botaoExplorador.addEventListener('click', function alternarExplorador() { definirExplorador(arvoreExplorador.hidden); });
    consultaCelular.addEventListener('change', function adaptarExplorador() { definirExplorador(!consultaCelular.matches); });
    definirExplorador(!consultaCelular.matches);
    atualizarConsulta();
})();
