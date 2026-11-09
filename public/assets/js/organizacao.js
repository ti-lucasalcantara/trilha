/** Controla somente as interações locais da demonstração da organização. */
(() => {
    'use strict';

    const campoPesquisa = document.getElementById('pesquisa-processos');
    const seletorTipo = document.getElementById('tipo-processo');
    const linhasProcessos = Array.from(document.querySelectorAll('[data-processo]'));
    const abas = document.querySelectorAll('[data-aba]');
    const contexto = document.getElementById('seletor-trabalho-cabecalho');
    let categoriaAtual = 'todos';

    if (!campoPesquisa || !seletorTipo) return;

    /** Compara os textos sem diferenciar maiúsculas ou acentos. */
    function normalizarTexto(texto) {
        return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase('pt-BR');
    }

    /** Atualiza a listagem e o estado vazio combinando os três filtros. */
    function atualizarResultados() {
        const pesquisa = normalizarTexto(campoPesquisa.value.trim());
        let quantidadeVisivel = 0;
        const totais = { todos: 0, receber: 0, providencia: 0, assinar: 0 };

        linhasProcessos.forEach(function filtrarLinha(linha) {
            const correspondeCategoria = categoriaAtual === 'todos' || linha.dataset.categoria === categoriaAtual;
            const correspondeTipo = seletorTipo.value === '' || linha.dataset.tipo === seletorTipo.value;
            const correspondeSetor = !contexto || (linha.dataset.unidade === contexto.elements.namedItem('unidade').value && linha.dataset.origem === contexto.elements.namedItem('setor').value);
            if (correspondeSetor) {
                totais.todos++;
                totais[linha.dataset.categoria]++;
            }
            const textoLinha = normalizarTexto([linha.dataset.numero, linha.dataset.assunto, linha.dataset.origem].join(' '));
            linha.hidden = !correspondeCategoria || !correspondeTipo || !correspondeSetor || !textoLinha.includes(pesquisa);
            if (!linha.hidden) quantidadeVisivel++;
        });

        document.getElementById('nenhum-processo').hidden = quantidadeVisivel !== 0;
        document.querySelector('.tabela-responsiva').hidden = quantidadeVisivel === 0;
        document.getElementById('quantidade-resultados').textContent = `Exibindo ${quantidadeVisivel} ${quantidadeVisivel === 1 ? 'processo' : 'processos'}`;
        document.querySelectorAll('.resumo-trabalho[data-filtrar]').forEach(botao => { botao.querySelector('strong').textContent = totais[botao.dataset.filtrar]; });
        abas.forEach(aba => { aba.querySelector('span').textContent = totais[aba.dataset.aba]; });
        const contador = document.querySelector('.item-navegacao[data-filtrar="assinar"] .contador-menu');
        if (contador) contador.textContent = totais.assinar;
    }

    /** Seleciona uma categoria e mantém os outros filtros escolhidos pelo usuário. */
    function selecionarCategoria(categoria) {
        categoriaAtual = categoria;
        abas.forEach(function atualizarAba(aba) {
            aba.setAttribute('aria-pressed', String(aba.dataset.aba === categoria));
        });
        atualizarResultados();
    }

    /** Encaminha os atalhos de prazo para a página completa do processo. */
    function abrirProcesso(identificador) {
        const linha = linhasProcessos.find(function localizarLinha(candidata) {
            return candidata.dataset.processo === identificador;
        });
        if (!linha) return;
        window.location.assign(linha.querySelector('.abrir-processo').href);
    }

    campoPesquisa.addEventListener('input', atualizarResultados);
    document.addEventListener('contexto-trabalho-alterado', atualizarResultados);
    seletorTipo.addEventListener('change', atualizarResultados);
    abas.forEach(function configurarAba(aba) {
        aba.addEventListener('click', function escolherAba() { selecionarCategoria(aba.dataset.aba); });
    });
    document.querySelectorAll('[data-filtrar]').forEach(function configurarAtalho(botao) {
        botao.addEventListener('click', function filtrarCaixa() {
            selecionarCategoria(botao.dataset.filtrar);
            document.getElementById('titulo-processos').scrollIntoView({ block: 'nearest' });
        });
    });
    document.getElementById('limpar-filtros').addEventListener('click', function limparFiltros() {
        campoPesquisa.value = '';
        seletorTipo.value = '';
        selecionarCategoria('todos');
        campoPesquisa.focus();
    });
    document.querySelectorAll('[data-abrir-processo]').forEach(function configurarProcesso(botao) {
        botao.addEventListener('click', function visualizarProcesso() { abrirProcesso(botao.dataset.abrirProcesso); });
    });
    atualizarResultados();
})();
