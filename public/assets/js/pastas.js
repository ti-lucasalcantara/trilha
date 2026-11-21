const listagemPastas = document.querySelector('.pastas-listagem');
const menuPastas = document.querySelector('.pastas-menu-contexto');
const comandoAberto = document.querySelector('[data-acao-aberta]');

if (comandoAberto) {
    comandoAberto.querySelector('input:not([type="hidden"]), select:not(:disabled), button:not(:disabled)')?.focus();
} else {
    listagemPastas.querySelector('tr.selecionada')?.focus({ preventScroll: true });
}

let linhaDoMenu;

function abrirMenuPastas(linha, x, y) {
    const selecao = linha?.dataset.selecao;
    const novaPasta = linha ? linha.dataset.novaPasta : listagemPastas.dataset.novaPasta;

    if (menuPastas.matches(':popover-open')) menuPastas.hidePopover();
    for (const opcao of menuPastas.querySelectorAll('[data-opcao]')) {
        const acao = opcao.dataset.opcao;
        let destino;
        if (acao === 'abrir') {
            destino = linha?.querySelector('a').href;
        } else if (acao === 'criar') {
            destino = novaPasta;
        } else if (linha?.hasAttribute('data-gerencia')) {
            const url = new URL(selecao, location.href);
            url.searchParams.set('acao', acao);
            destino = url.href;
        }
        opcao.hidden = !destino;
        if (destino) opcao.href = destino;
        else opcao.removeAttribute('href');
    }

    linhaDoMenu?.classList.remove('pastas-menu-alvo');
    linhaDoMenu = linha;
    linhaDoMenu?.classList.add('pastas-menu-alvo');
    menuPastas.showPopover();
    const limiteX = Math.max(8, window.innerWidth - menuPastas.offsetWidth - 8);
    const limiteY = Math.max(8, window.innerHeight - menuPastas.offsetHeight - 8);
    menuPastas.style.left = `${Math.max(8, Math.min(x, limiteX))}px`;
    menuPastas.style.top = `${Math.max(8, Math.min(y, limiteY))}px`;
    menuPastas.querySelector('a[href]')?.focus({ preventScroll: true });
}

listagemPastas.addEventListener('click', (evento) => {
    const linha = evento.target.closest('tr[data-selecao]');
    if (!linha || evento.target.closest('a') || linha.classList.contains('selecionada')) return;
    if (window.getSelection()?.type === 'Range') return;
    location.assign(linha.dataset.selecao);
});

listagemPastas.addEventListener('keydown', (evento) => {
    const linha = evento.target.closest('tr[data-selecao]');
    if (!linha) return;
    if (evento.key === 'ContextMenu' || (evento.shiftKey && evento.key === 'F10')) {
        evento.preventDefault();
        const retangulo = evento.target.getBoundingClientRect();
        abrirMenuPastas(linha, retangulo.left + 16, retangulo.bottom);
    } else if (evento.target === linha && ['Enter', ' '].includes(evento.key)) {
        evento.preventDefault();
        if (!linha.classList.contains('selecionada')) location.assign(linha.dataset.selecao);
    }
});

listagemPastas.addEventListener('contextmenu', (evento) => {
    const linha = evento.target.closest('tr');
    if (linha ? !linha.hasAttribute('data-selecao') : !listagemPastas.dataset.novaPasta) return;
    evento.preventDefault();
    abrirMenuPastas(linha, evento.clientX, evento.clientY);
});

menuPastas.addEventListener('toggle', () => {
    if (!menuPastas.matches(':popover-open')) {
        linhaDoMenu?.classList.remove('pastas-menu-alvo');
        linhaDoMenu = null;
    }
});

menuPastas.addEventListener('keydown', (evento) => {
    if (evento.key === 'Escape') {
        menuPastas.hidePopover();
        linhaDoMenu?.focus({ preventScroll: true });
    }
    if (['ArrowDown', 'ArrowUp'].includes(evento.key)) {
        evento.preventDefault();
        const opcoes = [...menuPastas.querySelectorAll('a[href]')];
        const atual = opcoes.indexOf(document.activeElement);
        const passo = evento.key === 'ArrowDown' ? 1 : -1;
        opcoes[(atual + passo + opcoes.length) % opcoes.length]?.focus();
    }
});
