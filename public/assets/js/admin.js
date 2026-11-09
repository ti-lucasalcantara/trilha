/** Controla o menu móvel e mantém o tema escolhido entre páginas. */
(() => {
    'use strict';

    const botaoMenu = document.querySelector('.alternar-menu-admin');
    const lateral = botaoMenu.closest('.lateral-admin');
    const botaoTema = document.getElementById('alternar-tema-admin');
    const chaveTema = 'trilha-tema-admin';

    function aplicarTema(tema) {
        const claro = tema === 'claro';
        document.body.dataset.temaAdmin = claro ? 'claro' : 'escuro';
        const rotulo = claro ? 'Ativar tema escuro' : 'Ativar tema claro';
        botaoTema.setAttribute('aria-label', rotulo);
        botaoTema.title = rotulo;
        botaoTema.querySelector('i').className = claro ? 'fa-solid fa-moon' : 'fa-solid fa-sun';
    }

    let temaSalvo;
    try { temaSalvo = localStorage.getItem(chaveTema); } catch { /* O tema continua disponível nesta página. */ }
    aplicarTema(temaSalvo);

    botaoTema.addEventListener('click', function alternarTema() {
        const tema = document.body.dataset.temaAdmin === 'claro' ? 'escuro' : 'claro';
        aplicarTema(tema);
        try { localStorage.setItem(chaveTema, tema); } catch { /* A preferência fica apenas na página. */ }
    });

    botaoMenu.addEventListener('click', function alternarMenu() {
        const aberto = lateral.classList.toggle('menu-admin-aberto');
        botaoMenu.setAttribute('aria-expanded', String(aberto));
    });

    /** Mantém a senha no formulário e usa o gerador seguro do navegador. */
    const senha = document.getElementById('campo-admin-senha');
    if (senha) {
        const alternar = document.getElementById('alternar-senha-admin');
        const gerar = document.getElementById('gerar-senha-admin');
        const aviso = document.getElementById('aviso-senha-admin');

        function exibirSenha(visivel) {
            senha.type = visivel ? 'text' : 'password';
            const rotulo = visivel ? 'Ocultar senha' : 'Mostrar senha';
            alternar.setAttribute('aria-label', rotulo);
            alternar.title = rotulo;
            alternar.querySelector('i').className = visivel ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
        }

        alternar.addEventListener('click', () => exibirSenha(senha.type === 'password'));
        gerar.addEventListener('click', function gerarSenha() {
            const bytes = crypto.getRandomValues(new Uint8Array(24));
            senha.value = btoa(String.fromCharCode(...bytes));
            exibirSenha(true);
            aviso.textContent = 'Senha gerada. Copie antes de salvar.';
            senha.focus();
            senha.select();
        });
    }

    /** Filtra os registros já carregados e informa quando a busca fica vazia. */
    const busca = document.getElementById('busca-cadastro-admin');
    if (busca) {
        const registros = Array.from(document.querySelectorAll('[data-registro-admin]'));
        const normalizar = texto => texto.normalize('NFD').replace(/\p{M}/gu, '').toLocaleLowerCase('pt-BR');
        busca.addEventListener('input', function filtrarRegistros() {
            const termo = normalizar(busca.value.trim());
            registros.forEach(registro => { registro.hidden = !normalizar(registro.textContent).includes(termo); });
            document.getElementById('busca-vazia-admin').hidden = registros.some(registro => !registro.hidden);
        });
    }

    /** Confirma a exclusão antes de enviar o formulário protegido. */
    const dialogo = document.getElementById('confirmar-exclusao-admin');
    let formularioExclusao;
    document.querySelectorAll('[data-excluir-admin]').forEach(function configurarExclusao(formulario) {
        formulario.addEventListener('submit', function pedirConfirmacao(evento) {
            evento.preventDefault();
            formularioExclusao = formulario;
            document.getElementById('nome-exclusao-admin').textContent = formulario.dataset.excluirAdmin;
            dialogo.showModal();
        });
    });
    dialogo?.querySelector('form').addEventListener('submit', function confirmarExclusao(evento) {
        if (evento.submitter?.value === 'excluir') formularioExclusao.submit();
    });

    /** Recalcula o caixa em centavos sem gravar os lançamentos fictícios. */
    function configurarFinanceiro(painel) {
        const competencia = document.getElementById('competencia-admin');
        const filtro = document.getElementById('situacao-financeiro-admin');
        const lancamentos = Array.from(painel.querySelectorAll('[data-lancamento-admin]'));
        const moeda = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });
        const rotulos = { pago: 'Pago', aberto: 'Em aberto', vencido: 'Vencido' };

        function atualizarCaixa() {
            const totais = { recebido: 0, despesas: 0, receber: 0, vencido: 0 };
            let visiveis = 0;
            lancamentos.forEach(function atualizarLancamento(linha) {
                const dados = linha.dataset;
                const pago = dados.pago === 'true';
                const situacao = pago ? 'pago' : dados.vencimento < painel.dataset.hoje ? 'vencido' : 'aberto';
                const noMes = dados.vencimento.startsWith(competencia.value);
                linha.hidden = !noMes || Boolean(filtro.value && filtro.value !== situacao);
                if (!linha.hidden) visiveis++;

                const selo = linha.querySelector('[data-situacao-financeiro]');
                selo.textContent = rotulos[situacao];
                selo.dataset.situacao = situacao;
                linha.querySelector('[data-alterar-pagamento]').textContent = pago ? 'Reabrir' : 'Marcar pago';
                if (!noMes) return;

                const centavos = Number(dados.centavos);
                if (dados.tipo === 'entrada') {
                    totais[pago ? 'recebido' : 'receber'] += centavos;
                    if (situacao === 'vencido') totais.vencido += centavos;
                } else if (pago) {
                    totais.despesas += centavos;
                }
            });
            totais.saldo = totais.recebido - totais.despesas;
            Object.entries(totais).forEach(function exibirTotal([chave, centavos]) {
                const valor = moeda.format(centavos / 100);
                document.getElementById(`caixa-${chave}`).textContent = chave === 'vencido' ? `${valor} vencidos` : valor;
            });
            document.getElementById('financeiro-vazio-admin').hidden = visiveis !== 0;
        }

        competencia.addEventListener('change', atualizarCaixa);
        filtro.addEventListener('change', atualizarCaixa);
        painel.addEventListener('click', function alternarPagamento(evento) {
            const botao = evento.target.closest('[data-alterar-pagamento]');
            if (!botao) return;
            const linha = botao.closest('[data-lancamento-admin]');
            const pago = linha.dataset.pago !== 'true';
            linha.dataset.pago = String(pago);
            atualizarCaixa();
            if (linha.hidden) filtro.focus();
            document.getElementById('aviso-financeiro-admin').textContent = pago
                ? 'Pagamento marcado na simulação. Os totais foram atualizados.'
                : 'Lançamento reaberto na simulação. Os totais foram atualizados.';
        });
        atualizarCaixa();
    }

    const financeiro = document.getElementById('financeiro-admin');
    if (financeiro) configurarFinanceiro(financeiro);
})();
