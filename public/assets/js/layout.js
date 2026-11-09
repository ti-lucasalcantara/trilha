/** Mantém as interações do cabeçalho e do menu comuns às telas internas. */
(() => {
    'use strict';

    const avisoInterface = document.getElementById('aviso-interface');
    const navegacao = document.getElementById('navegacao-principal');
    const botaoMenu = document.querySelector('.alternar-menu');
    let temporizadorAviso;
    const chavePreferencias = `trilha:${document.body.dataset.preferenciasContexto}:`;

    /** Isola as preferências por conta e organização e tolera armazenamento indisponível. */
    const preferencias = {
        ler(chave) {
            try { return JSON.parse(localStorage.getItem(chavePreferencias + chave)); } catch { return null; }
        },
        gravar(chave, valor) {
            try { localStorage.setItem(chavePreferencias + chave, JSON.stringify(valor)); } catch { /* A interface continua funcionando. */ }
        },
    };
    window.preferenciasTrilha = preferencias;

    /** Fecha pelo botão Cancelar sem torná-lo o envio padrão do formulário. */
    document.addEventListener('click', function cancelarDialogo(evento) {
        const botao = evento.target.closest('[data-cancelar-dialogo]');
        botao?.closest('dialog')?.close('cancelar');
    });

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
    /** Atualiza a lista dependente e aplica o contexto real ou demonstrativo. */
    function configurarContextoTrabalho() {
        const formulario = document.getElementById('seletor-trabalho-cabecalho');
        if (!formulario) return;
        const unidade = formulario.elements.namedItem('unidade');
        const setor = formulario.elements.namedItem('setor');
        if (unidade.disabled) return;
        const exemplo = formulario.dataset.demonstrativo === 'true';
        const opcoes = Array.from(setor.options);
        const chave = 'trilha-contexto-demo';
        const dialogo = document.getElementById('confirmar-contexto-trabalho');
        const campos = [unidade, setor].map(seletor => ({
            seletor, rotulo: seletor === unidade ? 'unidade' : 'setor',
            botao: document.getElementById(`editar-${seletor.id}`),
        }));
        let campoAtivo;
        let contextoAtual;

        function atualizarSetores(escolhido) {
            const disponiveis = opcoes.filter(opcao => opcao.dataset.unidade === unidade.value);
            setor.replaceChildren(...disponiveis);
            if (disponiveis.some(opcao => opcao.value === escolhido)) setor.value = escolhido;
        }

        function aplicarSelecao() {
            if (!setor.value) return;
            if (!exemplo) {
                formulario.requestSubmit();
                return;
            }
            preferencias.gravar(chave, [unidade.value, setor.value]);
            document.dispatchEvent(new Event('contexto-trabalho-alterado'));
        }

        let escolhido = setor.value;
        if (exemplo) {
            try {
                const [unidadeSalva, setorSalvo] = preferencias.ler(chave) ?? [];
                if (Array.from(unidade.options).some(opcao => opcao.value === unidadeSalva)) {
                    unidade.value = unidadeSalva;
                    escolhido = setorSalvo;
                }
            } catch { /* Usa o primeiro setor disponível. */ }
            formulario.addEventListener('submit', evento => evento.preventDefault());
        }
        atualizarSetores(escolhido);
        contextoAtual = [unidade.value, setor.value];

        /** Mostra os nomes como texto clicável até que o usuário queira editar. */
        function exibirCampos(edicao = null) {
            campos.forEach(function exibirCampo(campo) {
                const editando = campo === edicao;
                campo.seletor.closest('label').hidden = !editando;
                campo.botao.hidden = editando;
                campo.botao.setAttribute('aria-expanded', String(editando));
                const nome = campo.seletor.selectedOptions[0]?.textContent.trim() ?? '';
                campo.botao.textContent = nome;
                campo.botao.setAttribute('aria-label', `Trocar ${campo.rotulo}: ${nome}`);
                campo.botao.title = `Trocar ${campo.rotulo}`;
            });
        }

        /** Volta aos valores confirmados quando a edição é cancelada. */
        function restaurarContexto() {
            unidade.value = contextoAtual[0];
            atualizarSetores(contextoAtual[1]);
            exibirCampos();
        }

        function fecharEdicao() {
            const anterior = campoAtivo;
            campoAtivo = null;
            restaurarContexto();
            anterior?.botao.focus();
        }

        campos.forEach(function configurarCampo(campo) {
            campo.botao.addEventListener('click', function abrirSeletor() {
                restaurarContexto();
                campoAtivo = campo;
                exibirCampos(campo);
                campo.seletor.focus();
            });
            campo.seletor.addEventListener('keydown', function cancelarSeletor(evento) {
                if (evento.key === 'Escape' && !dialogo.open) fecharEdicao();
            });
            campo.seletor.addEventListener('change', function pedirConfirmacao() {
                campoAtivo = campo;
                if (campo.seletor === unidade) atualizarSetores('');
                if (unidade.value === contextoAtual[0] && setor.value === contextoAtual[1]) {
                    fecharEdicao();
                    return;
                }
                const nomeUnidade = unidade.selectedOptions[0].textContent.trim();
                const nomeSetor = setor.selectedOptions[0].textContent.trim();
                document.getElementById('titulo-confirmar-contexto').textContent = `Confirmar troca de ${campo.rotulo}`;
                document.getElementById('descricao-confirmar-contexto').textContent = campo.seletor === unidade
                    ? `Trocar para a unidade “${nomeUnidade}” e começar pelo setor “${nomeSetor}”?`
                    : `Trocar para o setor “${nomeSetor}”?`;
                dialogo.returnValue = '';
                dialogo.showModal();
            });
        });

        dialogo.addEventListener('close', function concluirTroca() {
            if (dialogo.returnValue !== 'confirmar') {
                fecharEdicao();
                return;
            }
            contextoAtual = [unidade.value, setor.value];
            const anterior = campoAtivo;
            campoAtivo = null;
            exibirCampos();
            anterior?.botao.focus();
            aplicarSelecao();
        });

        document.addEventListener('click', function fecharSeletorExterno(evento) {
            if (campoAtivo && !dialogo.open && !formulario.contains(evento.target) && !dialogo.contains(evento.target)) {
                campoAtivo = null;
                restaurarContexto();
            }
        });
        exibirCampos();
    }

    configurarContextoTrabalho();
    if (botaoMenu && preferencias.ler('menu-superior-aberto') === true) {
        navegacao.classList.add('menu-aberto');
        botaoMenu.setAttribute('aria-expanded', 'true');
    }
    botaoMenu?.addEventListener('click', function alternarNavegacao() {
        const menuAberto = navegacao.classList.toggle('menu-aberto');
        botaoMenu.setAttribute('aria-expanded', String(menuAberto));
        preferencias.gravar('menu-superior-aberto', menuAberto);
    });
    document.addEventListener('click', fecharMenusExternos);
    document.addEventListener('toggle', organizarMenus, true);
    document.addEventListener('keydown', tratarAtalho);
})();
