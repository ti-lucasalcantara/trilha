(() => {
    'use strict';

    const form = document.getElementById('editor-documento-form');
    if (!form) return;

    const campos = document.getElementById('editor-campos');
    const texto = document.getElementById('editor-conteudo');
    const estado = document.getElementById('editor-estado');
    const barra = document.getElementById('editor-barra');
    const bloco = document.getElementById('editor-bloco');
    const alinhamento = document.getElementById('editor-alinhamento');
    const iconeAlinhamento = document.getElementById('editor-icone-alinhamento');
    const acoesTabela = form.querySelector('.editor-tabela-acoes');
    const inserirTabela = document.getElementById('editor-inserir-tabela');
    const previa = document.getElementById('editor-previa');
    const modoPrevia = document.getElementById('editor-modo-previa');
    const imprimir = document.getElementById('editor-imprimir');
    const tituloPrevia = document.getElementById('editor-titulo-previa');
    const salvar = document.getElementById('editor-salvar');
    const finalizar = document.getElementById('editor-finalizar');
    const erro = document.getElementById('editor-erro-local');
    const estrutura = document.getElementById('editor-estrutura-lista');
    const estruturaVazia = document.getElementById('editor-estrutura-vazia');
    const contagem = document.getElementById('editor-contagem');
    const dialogoTabela = document.getElementById('editor-dialogo-tabela');
    const dialogoFinalizar = document.getElementById('editor-dialogo-finalizar');
    const alinhamentos = {left: 'justifyLeft', center: 'justifyCenter', right: 'justifyRight', justify: 'justifyFull'};
    const iconesAlinhamento = {left: 'left', center: 'center', right: 'right', justify: 'justify'};
    const botoesComando = barra.querySelectorAll('[data-comando]');
    const tabelaAcao = Object.fromEntries(Array.from(acoesTabela.querySelectorAll('[data-tabela]'), botao => [botao.dataset.tabela, botao]));
    const obterDados = () => [form.elements.titulo.value, form.elements.tipo.value, form.elements.autor?.value, texto.innerHTML];
    let selecao;
    let emPrevia = false;
    let envioConfirmado = false;
    let enviando = false;
    let alterado = estado.dataset.estado === 'alterado';

    texto.querySelectorAll('[class^="editor-align-"]').forEach(elemento => {
        elemento.style.textAlign = elemento.className.replace('editor-align-', '');
        elemento.removeAttribute('class');
    });

    const estadoInicial = estado.dataset.estado;
    const rotuloInicial = estado.textContent;
    const dadosIniciais = obterDados();

    function elementoSelecionado() {
        if (!selecao || !texto.contains(selecao.commonAncestorContainer)) return null;
        const inicio = selecao.startContainer;
        return inicio instanceof Element ? inicio : inicio.parentElement;
    }

    function celulaSelecionada() {
        const celula = elementoSelecionado()?.closest('td, th');
        return celula && texto.contains(celula) ? celula : null;
    }

    function atualizarBarra() {
        const elemento = elementoSelecionado();
        for (const botao of botoesComando) {
            const comando = botao.dataset.comando;
            if (botao.hasAttribute('aria-pressed')) {
                botao.setAttribute('aria-pressed', String(Boolean(elemento && document.queryCommandState(comando))));
            } else {
                botao.disabled = !document.queryCommandEnabled(comando);
            }
        }
        const estilo = elemento?.closest('p, h2, h3, blockquote');
        bloco.value = estilo?.tagName.toLowerCase() ?? 'p';
        const alinhamentoAtual = elemento ? getComputedStyle(elemento).textAlign : 'left';
        alinhamento.value = Object.hasOwn(alinhamentos, alinhamentoAtual) ? alinhamentoAtual : 'left';
        iconeAlinhamento.className = 'fa-solid fa-align-' + iconesAlinhamento[alinhamento.value];

        const celula = celulaSelecionada();
        inserirTabela.disabled = Boolean(celula);
        acoesTabela.hidden = !celula || emPrevia;
        if (!celula) return;
        const tabela = celula.closest('table');
        tabelaAcao['adicionar-coluna'].disabled = celula.parentElement.cells.length >= 8;
        tabelaAcao['remover-coluna'].disabled = celula.parentElement.cells.length <= 1;
        tabelaAcao['remover-linha'].disabled = tabela.rows.length <= 1;
    }

    function atualizarEstrutura() {
        const itens = [];
        for (const titulo of texto.querySelectorAll('h2, h3')) {
            const rotulo = titulo.textContent.trim();
            if (!rotulo) continue;
            const item = document.createElement('li');
            const botao = document.createElement('button');
            item.dataset.nivel = titulo.tagName.toLowerCase();
            botao.type = 'button';
            botao.textContent = rotulo;
            botao.addEventListener('click', () => {
                if (!emPrevia) {
                    texto.focus({preventScroll: true});
                    window.getSelection().collapse(titulo, 0);
                }
                titulo.scrollIntoView({block: 'start'});
            });
            item.append(botao);
            itens.push(item);
        }
        estrutura.replaceChildren(...itens);
        estrutura.hidden = itens.length === 0;
        estruturaVazia.hidden = itens.length !== 0;
    }

    function atualizarDocumento() {
        const valor = texto.innerText.trim();
        const palavras = valor.match(/[\p{L}\p{N}]+(?:[’'-][\p{L}\p{N}]+)*/gu)?.length ?? 0;
        texto.dataset.vazio = String(!valor && !texto.querySelector('table'));
        contagem.textContent = palavras.toLocaleString('pt-BR') + (palavras === 1 ? ' palavra' : ' palavras');
        atualizarEstrutura();
    }

    function atualizarEstado() {
        alterado = estadoInicial === 'alterado' || obterDados().some((valor, indice) => valor !== dadosIniciais[indice]);
        const proximo = alterado ? 'alterado' : estadoInicial;
        if (estado.dataset.estado !== proximo) {
            estado.dataset.estado = proximo;
            estado.textContent = alterado ? 'Alterações não salvas' : rotuloInicial;
        }
        erro.hidden = true;
    }

    function guardarSelecao() {
        const atual = window.getSelection();
        if (atual.rangeCount && texto.contains(atual.anchorNode) && texto.contains(atual.focusNode)) {
            selecao = atual.getRangeAt(0).cloneRange();
            atualizarBarra();
        }
    }

    function restaurarSelecao() {
        texto.focus();
        const atual = window.getSelection();
        if (selecao && texto.contains(selecao.commonAncestorContainer)) {
            atual.removeAllRanges();
            atual.addRange(selecao);
        } else {
            atual.collapse(texto, texto.childNodes.length);
        }
        return atual;
    }

    function editar(comando, valor) {
        restaurarSelecao();
        document.execCommand(comando, false, valor);
        guardarSelecao();
    }

    function editarTabela(acao) {
        const celula = celulaSelecionada();
        if (!celula) return;
        const original = celula.closest('table');
        const indiceTabela = Array.from(texto.querySelectorAll('table')).indexOf(original);
        const indiceLinha = celula.parentElement.rowIndex;
        const indiceColuna = celula.cellIndex;
        const tabela = original.cloneNode(true);
        const linha = tabela.rows[indiceLinha];
        let linhaDestino = indiceLinha;
        let colunaDestino = indiceColuna;
        const novaCelula = linha => {
            const item = document.createElement(linha.cells[0]?.tagName === 'TH' ? 'th' : 'td');
            item.append(document.createElement('br'));
            return item;
        };

        switch (acao) {
            case 'adicionar-linha': {
                const novaLinha = linha.parentElement.tagName === 'THEAD'
                    ? (tabela.tBodies[0] ?? tabela.createTBody()).insertRow(0)
                    : linha.parentElement.insertRow(linha.sectionRowIndex + 1);
                for (let coluna = 0; coluna < linha.cells.length; coluna++) {
                    novaLinha.insertCell().append(document.createElement('br'));
                }
                linhaDestino = novaLinha.rowIndex;
                break;
            }
            case 'adicionar-coluna':
                for (const linhaAtual of tabela.rows) {
                    linhaAtual.cells[indiceColuna]?.after(novaCelula(linhaAtual));
                }
                colunaDestino++;
                break;
            case 'remover-linha':
                tabela.deleteRow(indiceLinha);
                break;
            case 'remover-coluna':
                for (const linhaAtual of tabela.rows) {
                    if (linhaAtual.cells.length > indiceColuna) linhaAtual.deleteCell(indiceColuna);
                }
                break;
        }

        const atual = restaurarSelecao();
        const intervalo = document.createRange();
        intervalo.selectNode(original);
        atual.removeAllRanges();
        atual.addRange(intervalo);
        if (acao === 'remover-tabela') {
            document.execCommand('delete');
        } else {
            document.execCommand('insertHTML', false, tabela.outerHTML);
            const inserida = texto.querySelectorAll('table')[indiceTabela];
            const destino = inserida?.rows[Math.min(linhaDestino, inserida.rows.length - 1)];
            if (destino?.cells.length) atual.collapse(destino.cells[Math.min(colunaDestino, destino.cells.length - 1)], 0);
        }
        guardarSelecao();
    }

    function mostrarErro(mensagem) {
        erro.textContent = mensagem;
        erro.hidden = false;
        erro.scrollIntoView({block: 'nearest'});
    }

    function alternarPrevia() {
        emPrevia = !emPrevia;
        previa.setAttribute('aria-pressed', String(emPrevia));
        previa.querySelector('span').textContent = emPrevia ? 'Voltar à edição' : 'Pré-visualizar';
        texto.contentEditable = String(!emPrevia);
        texto.setAttribute('aria-readonly', String(emPrevia));
        barra.hidden = emPrevia;
        acoesTabela.hidden = emPrevia || !celulaSelecionada();
        modoPrevia.hidden = !emPrevia;
        imprimir.hidden = !emPrevia;
        tituloPrevia.hidden = !emPrevia;
    }

    form.addEventListener('submit', evento => {
        if (campos.disabled || enviando) {
            evento.preventDefault();
            return;
        }
        const conteudo = texto.innerHTML;
        const finalizando = evento.submitter === finalizar;
        const excedeLimite = new TextEncoder().encode(conteudo).length > 65535;
        if (excedeLimite || (finalizando && !texto.textContent.trim())) {
            evento.preventDefault();
            mostrarErro(excedeLimite
                ? 'O conteúdo ultrapassou o limite do editor.'
                : 'Escreva o conteúdo antes de finalizar.');
            return;
        }
        if (finalizando && !envioConfirmado) {
            evento.preventDefault();
            dialogoFinalizar.showModal();
            return;
        }
        form.elements.conteudo.value = conteudo;
        enviando = true;
    });

    tituloPrevia.textContent = form.elements.titulo.value.trim();
    atualizarDocumento();
    if (campos.disabled) return;

    texto.contentEditable = 'true';
    texto.setAttribute('aria-readonly', 'false');
    for (const botao of [salvar, finalizar, previa]) botao.disabled = false;
    atualizarDocumento();

    texto.addEventListener('input', atualizarDocumento);
    texto.addEventListener('paste', evento => {
        evento.preventDefault();
        editar('insertText', evento.clipboardData.getData('text/plain'));
    });
    texto.addEventListener('drop', evento => evento.preventDefault());
    document.addEventListener('selectionchange', guardarSelecao);
    form.addEventListener('input', evento => {
        if (evento.target === form.elements.titulo) tituloPrevia.textContent = form.elements.titulo.value.trim();
        if (evento.target === texto || evento.target.matches('[name="titulo"], [name="tipo"], [name="autor"]')) atualizarEstado();
    });
    window.addEventListener('pageshow', () => { enviando = false; });
    window.addEventListener('beforeunload', evento => {
        if (!alterado || enviando) return;
        evento.preventDefault();
        evento.returnValue = '';
    });

    barra.addEventListener('mousedown', evento => {
        if (evento.target.closest('button')) evento.preventDefault();
    });
    barra.addEventListener('click', evento => {
        const botao = evento.target.closest('[data-comando]');
        if (botao) editar(botao.dataset.comando);
    });
    bloco.addEventListener('change', () => editar('formatBlock', bloco.value));
    alinhamento.addEventListener('change', () => editar(alinhamentos[alinhamento.value]));
    acoesTabela.addEventListener('mousedown', evento => {
        if (evento.target.closest('button')) evento.preventDefault();
    });
    acoesTabela.addEventListener('click', evento => {
        const botao = evento.target.closest('[data-tabela]');
        if (botao) editarTabela(botao.dataset.tabela);
    });

    inserirTabela.addEventListener('click', () => dialogoTabela.showModal());
    dialogoTabela.querySelector('form').addEventListener('submit', evento => {
        if (evento.submitter?.value !== 'inserir') return;
        evento.preventDefault();
        const {colunas, linhas} = evento.currentTarget.elements;
        const cabecalho = Array.from({length: colunas.valueAsNumber}, (_, indice) => '<th>Coluna ' + (indice + 1) + '</th>').join('');
        const linha = '<tr>' + '<td><br></td>'.repeat(colunas.valueAsNumber) + '</tr>';
        dialogoTabela.close();
        editar('insertHTML', '<table><thead><tr>' + cabecalho + '</tr></thead><tbody>' + linha.repeat(linhas.valueAsNumber) + '</tbody></table><p><br></p>');
    });
    dialogoFinalizar.querySelector('form').addEventListener('submit', evento => {
        if (evento.submitter?.value !== 'finalizar') return;
        evento.preventDefault();
        dialogoFinalizar.close();
        envioConfirmado = true;
        form.requestSubmit(finalizar);
        envioConfirmado = false;
    });
    previa.addEventListener('click', alternarPrevia);
    imprimir.addEventListener('click', () => window.print());
    document.addEventListener('keydown', evento => {
        if ((evento.ctrlKey || evento.metaKey) && !evento.altKey && !evento.shiftKey
            && evento.key.toLowerCase() === 's' && !document.querySelector('.editor-dialogo[open]')) {
            evento.preventDefault();
            form.requestSubmit(salvar);
        }
    });
})();
