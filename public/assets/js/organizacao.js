/** Controla somente as interações locais da demonstração da organização. */
(() => {
    'use strict';

    const campoPesquisa = document.getElementById('pesquisa-processos');
    const seletorTipo = document.getElementById('tipo-processo');
    const linhasProcessos = Array.from(document.querySelectorAll('[data-processo]'));
    const abas = document.querySelectorAll('[data-aba]');
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

        linhasProcessos.forEach(function filtrarLinha(linha) {
            const correspondeCategoria = categoriaAtual === 'todos' || linha.dataset.categoria === categoriaAtual;
            const correspondeTipo = seletorTipo.value === '' || linha.dataset.tipo === seletorTipo.value;
            const textoLinha = normalizarTexto([linha.dataset.numero, linha.dataset.assunto, linha.dataset.origem].join(' '));
            linha.hidden = !correspondeCategoria || !correspondeTipo || !textoLinha.includes(pesquisa);
            if (!linha.hidden) quantidadeVisivel++;
        });

        document.getElementById('nenhum-processo').hidden = quantidadeVisivel !== 0;
        document.querySelector('.tabela-responsiva').hidden = quantidadeVisivel === 0;
        document.getElementById('quantidade-resultados').textContent = `Exibindo ${quantidadeVisivel} ${quantidadeVisivel === 1 ? 'processo' : 'processos'}`;
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
})();
