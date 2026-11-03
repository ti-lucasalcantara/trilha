/** Controla somente a navegação móvel e as abas da apresentação comercial. */
(() => {
    'use strict';

    const botaoMenu = document.querySelector('.abrir-menu-comercial');
    const menu = document.getElementById('menu-comercial');
    const abas = Array.from(document.querySelectorAll('[data-tela]'));

    /** Atualiza a visibilidade do menu e seu nome acessível. */
    function definirMenu(aberto, devolverFoco = false) {
        menu.classList.toggle('menu-aberto', aberto);
        botaoMenu.setAttribute('aria-expanded', String(aberto));
        botaoMenu.setAttribute('aria-label', aberto ? 'Fechar menu' : 'Abrir menu');
        botaoMenu.querySelector('i').className = aberto ? 'fa-solid fa-xmark' : 'fa-solid fa-bars';
        if (devolverFoco) botaoMenu.focus();
    }

    /** Seleciona uma captura do produto sem carregar conteúdo externo. */
    function selecionarTela(abaSelecionada, moverFoco = false) {
        abas.forEach(function atualizarAba(aba) {
            const selecionada = aba === abaSelecionada;
            aba.setAttribute('aria-selected', String(selecionada));
            aba.tabIndex = selecionada ? 0 : -1;
            document.getElementById(aba.getAttribute('aria-controls')).hidden = !selecionada;
        });
        if (moverFoco) abaSelecionada.focus();
    }

    /** Permite percorrer as abas com as setas, Home e End. */
    function navegarAbas(evento) {
        const indiceAtual = abas.indexOf(evento.currentTarget);
        let proximoIndice;
        if (evento.key === 'ArrowRight') proximoIndice = (indiceAtual + 1) % abas.length;
        else if (evento.key === 'ArrowLeft') proximoIndice = (indiceAtual + abas.length - 1) % abas.length;
        else if (evento.key === 'Home') proximoIndice = 0;
        else if (evento.key === 'End') proximoIndice = abas.length - 1;
        else return;
        evento.preventDefault();
        selecionarTela(abas[proximoIndice], true);
    }

    botaoMenu.addEventListener('click', function alternarMenu() {
        definirMenu(botaoMenu.getAttribute('aria-expanded') !== 'true');
    });
    menu.querySelectorAll('a').forEach(function configurarLink(link) {
        link.addEventListener('click', function recolherMenu() { definirMenu(false); });
    });
    abas.forEach(function configurarAba(aba) {
        aba.addEventListener('click', function escolherTela() { selecionarTela(aba); });
        aba.addEventListener('keydown', navegarAbas);
    });
    document.addEventListener('keydown', function fecharPorTeclado(evento) {
        if (evento.key === 'Escape' && botaoMenu.getAttribute('aria-expanded') === 'true') definirMenu(false, true);
    });
    document.addEventListener('click', function fecharPorCliqueExterno(evento) {
        if (!evento.target.closest('.cabecalho-comercial')) definirMenu(false);
    });
})();
