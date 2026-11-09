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
    const consultaCelular = window.matchMedia('(max-width: 650px)');
    let itemSelecionado = itensExplorador[0];
    let paginaAtual = 1;
    let quantidadePaginas = 1;
    let temporizadorCopia;

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
        atualizarFiltrosAplicados();
        atualizarPaginacao(resultado.length);
    }

    /** Reinicia a paginação quando a pesquisa ou algum filtro muda. */
    function atualizarFiltros() {
        paginaAtual = 1;
        atualizarConsulta();
    }

    /** Atualiza a seleção e mostra o caminho apenas dentro da árvore. */
    function selecionarItem(item) {
        itemSelecionado.classList.remove('selecionado');
        itemSelecionado.removeAttribute('aria-current');
        itemSelecionado = item;
        item.classList.add('selecionado');
        item.setAttribute('aria-current', 'true');
        const caminho = document.getElementById('caminho-selecao');
        caminho.textContent = item.dataset.rotulo;
        caminho.hidden = ['todos', 'acompanhados'].includes(item.dataset.escopo);
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
        botao.querySelector('span').textContent = acompanhado ? 'Deixar de acompanhar' : 'Acompanhar';
        botao.querySelector('i').className = `${acompanhado ? 'fa-solid' : 'fa-regular'} fa-star`;
        botao.closest('[popover]').hidePopover();
        atualizarConsulta();
        const destinoFoco = registro.hidden ? itemSelecionado : registro.querySelector('.abrir-acoes');
        destinoFoco.focus();
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
            inicio = null;
            divisoria.classList.remove('redimensionando');
        });
        divisoria.addEventListener('keydown', function ajustarComTeclado(evento) {
            const larguras = { ArrowLeft: painel.offsetWidth - 16, ArrowRight: painel.offsetWidth + 16, Home: minimo, End: larguraMaxima() };
            if (!Object.hasOwn(larguras, evento.key)) return;
            evento.preventDefault();
            aplicarLargura(larguras[evento.key]);
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

        tabelaConsulta.querySelectorAll('[data-coluna]').forEach(function atualizarColuna(celula) {
            celula.hidden = !colunasVisiveis.has(celula.dataset.coluna);
        });
        tabelaConsulta.style.setProperty('--quantidade-colunas', colunasVisiveis.size);
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
        const local = formulario.elements.namedItem('local');
        const visibilidade = formulario.elements.namedItem('visibilidade');
        const confirmar = document.getElementById('confirmar-pasta');
        const erro = document.getElementById('erro-pasta');
        const aviso = document.getElementById('aviso-pastas');
        const destinos = document.getElementById('destinos-pasta');
        const arvoreDestinos = document.getElementById('arvore-destinos-pasta');
        const titulos = { criar: 'Nova pasta', subpasta: 'Nova subpasta', renomear: 'Renomear pasta', visibilidade: 'Alterar visibilidade', mover: 'Mover pasta', confirmarMovimento: 'Confirmar movimento', remover: 'Remover pasta' };
        const listasDestino = new Map();
        let sequencia = 0;
        let acao;
        let pasta;
        let pastaArrastada;
        let alvoArraste;
        let destinoArraste;
        const avisoMovimento = document.getElementById('aviso-movimento-pasta');
        const desfazer = document.getElementById('desfazer-movimento-pasta');
        const barraDesfazer = avisoMovimento.querySelector('.progresso-desfazer');
        const prazoDesfazer = 5000;
        let ultimoMovimento;
        let temporizadorMovimento;
        let animacaoMovimento;

        /** Atualiza o caminho mostrado na consulta sem trocar as chaves originais. */
        function atualizarCaminhos(no) {
            const botao = no.querySelector('.nome-pasta');
            const superior = no.parentElement.closest('.no-pasta');
            const rotulo = no.dataset.nome;
            botao.querySelector('span').textContent = rotulo;
            botao.dataset.rotulo = superior ? `${superior.querySelector('.nome-pasta').dataset.rotulo} / ${rotulo}` : rotulo;
            no.querySelector('.abrir-acoes-pasta').setAttribute('aria-label', `Ações da pasta ${rotulo}`);
            no.querySelector('.expandir-pasta').setAttribute('aria-label', `Subpastas de ${rotulo}`);
            no.querySelector('.filhas-pasta').querySelectorAll(':scope > .no-pasta').forEach(atualizarCaminhos);
        }

        /** Mostra ou recolhe as subpastas de um nó. */
        function definirRamo(no, aberto) {
            const lista = no.querySelector('.filhas-pasta');
            const botao = no.querySelector('.expandir-pasta');
            lista.hidden = !aberto;
            botao.hidden = !lista.childElementCount;
            botao.setAttribute('aria-expanded', String(aberto));
        }

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

        /** Reinicia a barra e o prazo para desfazer a última movimentação. */
        function registrarMovimento(no, destino) {
            const origem = no.parentElement;
            ultimoMovimento = { no, origem, proxima: no.nextElementSibling };
            moverPasta(no, destino);
            avisoMovimento.hidden = false;
            clearTimeout(temporizadorMovimento);
            animacaoMovimento?.cancel();
            animacaoMovimento = barraDesfazer.animate(
                [{ transform: 'scaleX(0)' }, { transform: 'scaleX(1)' }],
                { duration: prazoDesfazer, fill: 'forwards' }
            );
            temporizadorMovimento = setTimeout(encerrarMovimento, prazoDesfazer);
        }

        function encerrarMovimento() {
            avisoMovimento.hidden = true;
            animacaoMovimento?.cancel();
            ultimoMovimento = null;
        }

        desfazer.addEventListener('click', function desfazerMovimento() {
            const { no, origem, proxima } = ultimoMovimento;
            if (no.isConnected && origem.isConnected && permiteMover(no, origem)) {
                moverPasta(no, origem, proxima?.parentElement === origem ? proxima : null);
                no.querySelector('.nome-pasta').focus();
                aviso.textContent = 'Movimento desfeito.';
            } else {
                aviso.textContent = 'O local original da pasta não está mais disponível.';
                raizPastas.focus();
            }
            clearTimeout(temporizadorMovimento);
            encerrarMovimento();
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
            const bloqueada = removendo && (pasta.querySelector('.filhas-pasta').childElementCount > 0 || pasta.querySelector('.nome-pasta').dataset.escopo === 'pasta');
            formulario.reset();
            destinos.hidden = !movendo;
            destinos.disabled = !movendo;
            const destinoDisponivel = movendo ? montarDestinos() : null;
            nome.closest('label').hidden = !criando && acao !== 'renomear';
            nome.required = !nome.closest('label').hidden;
            nome.value = acao === 'renomear' ? pasta.dataset.nome : '';
            local.closest('label').hidden = acao !== 'criar';
            visibilidade.closest('label').hidden = !criando && acao !== 'visibilidade';
            visibilidade.value = pasta?.dataset.visibilidade ?? '';
            document.getElementById('titulo-pasta').textContent = titulos[acao];
            const caminho = pasta?.querySelector('.nome-pasta').dataset.rotulo;
            const caminhoDestino = destinoArraste?.closest('.no-pasta')?.querySelector('.nome-pasta').dataset.rotulo ?? 'Raiz de Pastas';
            document.getElementById('contexto-pasta').textContent = removendo && !bloqueada
                ? `Remover “${caminho}” do Explorador?`
                : caminho ?? 'Escolha onde criar a pasta.';
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
        function salvarPasta(evento) {
            if (evento.submitter?.value !== 'salvar' || confirmar.hidden) return;
            const rotulo = nome.value.trim();
            if (nome.required && !rotulo) {
                evento.preventDefault();
                erro.textContent = 'Informe o nome da pasta.';
                erro.hidden = false;
                nome.focus();
                return;
            }

            let foco;
            if (acao === 'mover' || acao === 'confirmarMovimento') {
                const destino = acao === 'mover' ? listasDestino.get(formulario.elements.namedItem('destino').value) : destinoArraste;
                registrarMovimento(pasta, destino);
                foco = pasta.querySelector('.nome-pasta');
            } else if (acao === 'remover') {
                const superior = pasta.parentElement.closest('.no-pasta');
                if (pasta.contains(itemSelecionado)) selecionarItem(itensExplorador[0]);
                pasta.remove();
                if (superior) definirRamo(superior, Boolean(superior.querySelector('.filhas-pasta').childElementCount));
                foco = superior?.querySelector('.nome-pasta') ?? ramo.querySelector('summary');
            } else {
                if (acao === 'criar' || acao === 'subpasta') {
                    const superior = pasta;
                    pasta = inserirPasta(superior?.querySelector('.filhas-pasta') ?? arvore, rotulo, 'pasta-local');
                    const localCriacao = superior ? superior.dataset.local : local.value;
                    if (localCriacao !== undefined) pasta.dataset.local = localCriacao;
                    if (superior) definirRamo(superior, true);
                    ramo.open = true;
                }
                if (acao === 'renomear') {
                    pasta.dataset.nome = rotulo;
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
                : `${titulos[acao]}: alteração aplicada na prévia.`;
            dialogo.addEventListener('close', function restaurarFocoPasta() { foco.focus(); }, { once: true });
        }

        Object.entries(JSON.parse(arvore.dataset.pastas)).forEach(function montarGrupo([grupo, subpastas]) {
            const no = inserirPasta(arvore, grupo, 'grupo-pasta', grupo);
            const lista = no.querySelector('.filhas-pasta');
            Object.keys(subpastas).forEach(function montarSubpasta(rotulo) { inserirPasta(lista, rotulo, 'pasta', grupo, rotulo); });
            definirRamo(no, false);
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
        dialogo.addEventListener('close', function limparDestinoArraste() { destinoArraste = null; });
    }

    configurarPastas();
    configurarLarguraExplorador();
    document.getElementById('abrir-colunas').addEventListener('click', abrirColunas);
    dialogoColunas.querySelector('form').addEventListener('submit', aplicarColunas);
    dialogoColunas.addEventListener('change', function limparAvisoColunas() { avisoColunas.hidden = true; });
    campoPesquisa.addEventListener('input', atualizarFiltros);
    camposFiltros.forEach(function configurarFiltro(campo) { campo.addEventListener('change', atualizarFiltros); });
    seletorOrdenacao.addEventListener('change', atualizarFiltros);
    seletorQuantidade.addEventListener('change', atualizarFiltros);
    itensExplorador.forEach(function configurarItem(item) { item.addEventListener('click', function navegar() { selecionarItem(item); }); });
    document.querySelectorAll('[data-limpar-consulta]').forEach(function configurarLimpeza(botao) { botao.addEventListener('click', limparConsulta); });
    document.querySelectorAll('.acompanhar-processo').forEach(function configurarAcompanhamento(botao) { botao.addEventListener('click', function acompanhar() { alternarAcompanhamento(botao); }); });
    document.querySelectorAll('.copiar-numero').forEach(function configurarCopia(botao) { botao.addEventListener('click', function copiar() { copiarNumero(botao); }); });
    botaoFiltros.addEventListener('click', function alternarFiltros() {
        painelFiltros.hidden = !painelFiltros.hidden;
        botaoFiltros.setAttribute('aria-expanded', String(!painelFiltros.hidden));
    });
    botaoAnterior.addEventListener('click', function voltarPagina() { if (paginaAtual > 1) { paginaAtual--; atualizarConsulta(); } });
    botaoSeguinte.addEventListener('click', function avancarPagina() { if (paginaAtual < quantidadePaginas) { paginaAtual++; atualizarConsulta(); } });
    botaoExplorador.addEventListener('click', function alternarExplorador() { definirExplorador(arvoreExplorador.hidden); });
    consultaCelular.addEventListener('change', function adaptarExplorador() { definirExplorador(!consultaCelular.matches); });
    definirExplorador(!consultaCelular.matches);
    atualizarConsulta();
})();
