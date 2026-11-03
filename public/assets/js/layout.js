/** Mantém as interações do cabeçalho e do menu comuns às telas internas. */
(() => {
    'use strict';

    const avisoInterface = document.getElementById('aviso-interface');
    const navegacao = document.getElementById('navegacao-principal');
    const botaoMenu = document.querySelector('.alternar-menu');
    let temporizadorAviso;

    /** Apresenta uma mensagem da prévia sem executar operações no servidor. */
    function exibirAviso(mensagem) {
        if (!avisoInterface) return;
        clearTimeout(temporizadorAviso);
        avisoInterface.textContent = mensagem;
        avisoInterface.hidden = false;
        temporizadorAviso = setTimeout(function ocultarAviso() { avisoInterface.hidden = true; }, 6500);
    }

    /** Foca o campo de pesquisa da página quando ele estiver disponível. */
    function focarPesquisa() {
        const campoPesquisa = document.getElementById('pesquisa-processos') || document.getElementById('pesquisa-documentos');
        campoPesquisa?.focus();
    }

    /** Fecha os menus suspensos quando o clique acontece fora deles. */
    function fecharMenusExternos(evento) {
        document.querySelectorAll('.menu-suspenso[open]').forEach(function fecharMenu(menu) {
            if (!menu.contains(evento.target)) menu.open = false;
        });
    }

    /** Evita que os menus suspensos do cabeçalho se sobreponham. */
    function organizarMenus(evento) {
        const menuAtual = evento.target;
        if (!menuAtual.matches('.menu-suspenso') || !menuAtual.open) return;
        document.querySelectorAll('.menu-suspenso[open]').forEach(function fecharOutroMenu(menu) {
            if (menu !== menuAtual) menu.open = false;
        });
    }

    /** Trata os atalhos sem interromper a digitação ou um diálogo aberto. */
    function tratarAtalho(evento) {
        if (evento.key === 'Escape') {
            document.querySelectorAll('.menu-suspenso[open]').forEach(function fecharMenu(menu) { menu.open = false; });
        }
        if (evento.key !== '/' || evento.ctrlKey || evento.metaKey || evento.altKey || document.querySelector('dialog[open]')) return;
        if (evento.target.closest('input, textarea, select, [contenteditable="true"]')) return;
        evento.preventDefault();
        focarPesquisa();
    }

    document.querySelectorAll('[data-aviso]').forEach(function configurarAviso(botao) {
        botao.addEventListener('click', function mostrarAviso() { exibirAviso(botao.dataset.aviso); });
    });
    document.querySelector('[data-focar-pesquisa]')?.addEventListener('click', focarPesquisa);
    document.getElementById('contexto-trabalho')?.addEventListener('change', function informarContexto(evento) {
        exibirAviso(`Contexto demonstrativo: ${evento.target.value}. Nenhum vínculo ou permissão foi alterado.`);
    });
    botaoMenu?.addEventListener('click', function alternarNavegacao() {
        const menuAberto = navegacao.classList.toggle('menu-aberto');
        botaoMenu.setAttribute('aria-expanded', String(menuAberto));
    });
    document.addEventListener('click', fecharMenusExternos);
    document.addEventListener('toggle', organizarMenus, true);
    document.addEventListener('keydown', tratarAtalho);
})();
