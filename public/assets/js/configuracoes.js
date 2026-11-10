/** Simula os cadastros e a configuração de acesso na página aberta. */
(() => {
    'use strict';

    const fonte = document.querySelector('.area-configuracoes').dataset.configuracoesReais;
    const real = fonte ? JSON.parse(fonte) : null;
    const dados = real?.dados ?? {
        unidades: [{ id: '1', nome: 'Matriz', sigla: 'MTZ' }, { id: '2', nome: 'Filial Centro', sigla: 'CTR' }],
        setores: [
            { id: '3', nome: 'Financeiro', sigla: 'FIN', unidade: '1' },
            { id: '4', nome: 'Recursos Humanos', sigla: 'RH', unidade: '1' },
            { id: '5', nome: 'Operações', sigla: 'OPE', unidade: '2' },
        ],
        pessoas: [
            { id: '6', nome: 'Mariana Costa', email: 'mariana@example.com', setores: ['3', '5'] },
            { id: '7', nome: 'Ana Souza', email: 'ana@example.com', setores: ['4'] },
        ],
        perfis: [
            { id: '10', nome: 'Gerente', descricao: 'Exemplo de perfil para acompanhar e tramitar processos.', permissoes: ['processos.consultar', 'processos.tramitar'] },
            { id: '8', nome: 'Consulta de processos', descricao: 'Consulta aos processos da área.', permissoes: ['processos.consultar'] },
        ],
        atribuicoes: [{ id: '9', pessoa: '6', perfil: '8', escopo: 'setor:3' }, { id: '11', pessoa: '7', perfil: '10', escopo: 'unidade:1' }],
    };
    const catalogo = real?.catalogo ?? [
        { id: 'processos.consultar', nome: 'Consultar processos', modulo: 'Processos' },
        { id: 'processos.criar', nome: 'Criar processos', modulo: 'Processos' },
        { id: 'processos.tramitar', nome: 'Tramitar processos', modulo: 'Processos' },
        { id: 'documentos.adicionar', nome: 'Adicionar documentos', modulo: 'Documentos' },
        { id: 'documentos.assinar', nome: 'Assinar documentos', modulo: 'Documentos' },
    ];
    const nomes = { unidades: 'unidade', setores: 'setor', pessoas: 'pessoa', perfis: 'perfil', atribuicoes: 'atribuição' };
    const dialogo = document.getElementById('dialogo-configuracoes');
    const formulario = dialogo.querySelector('form');
    const campos = document.getElementById('campos-configuracoes');
    const aviso = document.getElementById('aviso-configuracoes');
    const erro = document.getElementById('erro-configuracoes');
    const confirmar = document.getElementById('confirmar-configuracoes');
    const busca = document.getElementById('buscar-equipe');
    const modeloSetores = document.getElementById('modelo-setores-da-unidade');
    const abasPerfisCadastrados = document.getElementById('abas-perfis-cadastrados');
    const formularioPerfil = document.getElementById('formulario-permissoes-perfil');
    const abasPerfil = Array.from(document.querySelectorAll('.abas-perfil [role="tab"]'));
    const unidadesAbertas = new Set(dados.unidades.slice(0, 1).map(unidade => unidade.id));
    const pessoasComPerfisAbertos = new Set();
    const rascunhosPermissoes = new Map();
    let perfilAtual = '10';
    let sequencia = 11;
    let edicao;

    /** Cria elementos preenchidos como texto, inclusive para dados digitados. */
    function elemento(tipo, texto = '', classe = '') {
        const item = document.createElement(tipo);
        item.textContent = texto;
        if (classe) item.className = classe;
        return item;
    }

    /** Resolve um identificador local sem depender do nome do cadastro. */
    function buscar(tipo, id) {
        return dados[tipo].find(registro => registro.id === id);
    }

    function nomeSetor(setor) {
        return `${buscar('unidades', setor.unidade).nome} / ${setor.nome}`;
    }

    /** Apresenta os âmbitos representados nas atribuições do banco. */
    function opcoesEscopo() {
        return [
            { id: 'organizacao', nome: 'Organização inteira' },
            ...dados.unidades.map(unidade => ({ id: `unidade:${unidade.id}`, nome: `Unidade · ${unidade.nome}` })),
            ...dados.setores.map(setor => ({ id: `setor:${setor.id}`, nome: `Setor · ${nomeSetor(setor)}` })),
        ];
    }

    function botaoAcao(rotulo, acao, tipo, registro) {
        const botao = elemento('button', '', `controle-configuracoes controle-registro ${acao}`);
        botao.type = 'button';
        botao.title = rotulo;
        botao.setAttribute('aria-label', `${rotulo} ${registro.nome ?? buscar('perfis', registro.perfil).nome}`);
        const icone = elemento('i', '', `fa-regular ${acao === 'editar' ? 'fa-pen-to-square' : 'fa-trash-can'}`);
        icone.setAttribute('aria-hidden', 'true');
        botao.append(icone);
        botao.addEventListener('click', () => abrirEditor(tipo, registro, acao));
        return botao;
    }

    /** Monta o painel de setores e vincula o botão de cadastro à unidade correta. */
    function montarSetores(unidade) {
        const painel = modeloSetores.content.firstElementChild.cloneNode(true);
        const setores = dados.setores.filter(setor => setor.unidade === unidade.id);
        painel.id = `setores-unidade-${unidade.id}`;
        painel.setAttribute('aria-label', `Setores de ${unidade.nome}`);
        painel.querySelector('.contagem-configuracoes').textContent = setores.length;
        painel.querySelector('.nome-unidade').textContent = unidade.nome;
        painel.querySelector('[data-novo="setores"]').addEventListener('click', () => abrirEditor('setores', { unidade: unidade.id }));
        mostrarLista('setores', setores, setor => [setor.nome, [setor.sigla, buscar('setores', setor.superior)?.nome].filter(Boolean).join(' · ')], painel.querySelector('.lista-configuracoes'));
        return painel;
    }

    /** Desenha os registros e mantém as ações ao lado de cada item. */
    function mostrarLista(tipo, registros, descrever, lista = document.getElementById(`lista-${tipo}`)) {
        lista.replaceChildren();
        if (!registros.length) {
            const mensagem = tipo === 'pessoas' && busca.value.trim() ? 'Nenhuma pessoa encontrada para essa busca.' : 'Nenhum registro nesta lista.';
            lista.append(elemento('p', mensagem, 'vazio-configuracoes'));
            return;
        }
        const itens = elemento('ul');
        registros.forEach(function adicionarLinha(registro) {
            const [titulo, detalhe, marcadores = []] = descrever(registro);
            const linha = elemento('li', '', 'linha-configuracoes');
            const icones = { unidades: 'fa-building', setores: 'fa-folder', perfis: 'fa-id-badge', atribuicoes: 'fa-address-card' };
            const icone = elemento('span', '', 'icone-registro');
            icone.setAttribute('aria-hidden', 'true');
            if (tipo === 'pessoas') {
                const iniciais = titulo.split(/\s+/).slice(0, 2).map(parte => Array.from(parte)[0]).join('').toLocaleUpperCase('pt-BR');
                icone.classList.add('avatar-equipe');
                icone.textContent = iniciais;
            } else {
                icone.append(elemento('i', '', `fa-regular ${icones[tipo]}`));
            }
            const identificacao = elemento('div', '', 'identificacao-configuracoes');
            let painel;
            if (tipo === 'unidades') {
                const selecionar = elemento('button', titulo, 'selecionar-unidade');
                painel = montarSetores(registro);
                selecionar.type = 'button';
                selecionar.setAttribute('aria-controls', painel.id);
                const seta = elemento('i', '', 'fa-solid');
                seta.setAttribute('aria-hidden', 'true');
                function atualizarExpansao() {
                    const aberta = unidadesAbertas.has(registro.id);
                    selecionar.setAttribute('aria-expanded', String(aberta));
                    painel.hidden = !aberta;
                    seta.className = `fa-solid ${aberta ? 'fa-chevron-down' : 'fa-chevron-right'}`;
                    linha.classList.toggle('unidade-selecionada', aberta);
                }
                selecionar.addEventListener('click', function selecionarUnidade() {
                    if (!unidadesAbertas.delete(registro.id)) unidadesAbertas.add(registro.id);
                    atualizarExpansao();
                });
                atualizarExpansao();
                selecionar.append(seta);
                identificacao.append(selecionar);
            } else {
                identificacao.append(elemento('strong', titulo));
            }
            if (detalhe) identificacao.append(elemento('small', detalhe));
            if (tipo === 'pessoas') identificacao.append(montarAbasPessoa(registro, marcadores));
            else marcadores.forEach(function mostrarMarcadores({ itens }) {
                const grupo = elemento('div', '', 'marcadores-configuracoes');
                itens.forEach(marcador => grupo.append(elemento('span', marcador)));
                identificacao.append(grupo);
            });
            const acoes = elemento('div', '', 'acoes-configuracoes');
            if (!real || ['unidades', 'setores'].includes(tipo)) acoes.append(botaoAcao('Editar', 'editar', tipo, registro));
            if (!real) acoes.append(botaoAcao('Remover', 'remover', tipo, registro));
            const resumo = elemento('div', '', 'resumo-registro');
            resumo.append(icone, identificacao, acoes);
            linha.append(resumo);
            if (painel) linha.append(painel);
            itens.append(linha);
        });
        lista.append(itens);
    }

    /** Exibe os setores e perfis em abas independentes para cada pessoa. */
    function montarAbasPessoa(pessoa, grupos) {
        const conteudo = elemento('div', '', 'vinculos-pessoa');
        const abas = elemento('div', '', 'abas-pessoa');
        abas.setAttribute('role', 'tablist');
        abas.setAttribute('aria-label', `Vínculos de ${pessoa.nome}`);
        conteudo.append(abas);
        const controles = grupos.map(function montarAba({ rotulo, itens }, indice) {
            const aba = elemento('button', rotulo);
            aba.type = 'button';
            aba.id = `aba-vinculos-${pessoa.id}-${indice}`;
            aba.setAttribute('role', 'tab');
            const painel = elemento('div', '', 'marcadores-configuracoes painel-vinculos-pessoa');
            painel.id = `painel-vinculos-${pessoa.id}-${indice}`;
            painel.setAttribute('role', 'tabpanel');
            painel.setAttribute('aria-labelledby', aba.id);
            painel.tabIndex = 0;
            aba.setAttribute('aria-controls', painel.id);
            itens.forEach(item => painel.append(elemento('span', item)));
            abas.append(aba);
            conteudo.append(painel);
            aba.addEventListener('click', () => selecionar(indice));
            aba.addEventListener('keydown', function navegar(evento) {
                const indices = { ArrowLeft: 1 - indice, ArrowRight: 1 - indice, Home: 0, End: 1 };
                if (!Object.hasOwn(indices, evento.key)) return;
                evento.preventDefault();
                const destino = indices[evento.key];
                selecionar(destino);
                controles[destino].aba.focus();
            });
            return { aba, painel };
        });

        function selecionar(indice) {
            if (indice === 1) pessoasComPerfisAbertos.add(pessoa.id);
            else pessoasComPerfisAbertos.delete(pessoa.id);
            controles.forEach(function atualizar({ aba, painel }, posicao) {
                const ativa = posicao === indice;
                aba.setAttribute('aria-selected', String(ativa));
                aba.tabIndex = ativa ? 0 : -1;
                painel.hidden = !ativa;
            });
        }

        selecionar(pessoasComPerfisAbertos.has(pessoa.id) ? 1 : 0);
        return conteudo;
    }

    /** Atualiza as relações exibidas depois de salvar ou remover. */
    function atualizarListas() {
        document.getElementById('contagem-unidades').textContent = dados.unidades.length;
        mostrarLista('unidades', dados.unidades, registro => [registro.nome, [registro.sigla, buscar('unidades', registro.superior)?.nome].filter(Boolean).join(' · ')]);
        atualizarEquipe();
        atualizarPerfil();
    }

    /** Mostra setores e perfis em linhas separadas para cada pessoa. */
    function atualizarEquipe() {
        const termo = busca.value.trim().toLocaleLowerCase('pt-BR');
        const pessoasVisiveis = dados.pessoas.filter(pessoa => `${pessoa.nome} ${pessoa.email}`.toLocaleLowerCase('pt-BR').includes(termo));
        document.getElementById('contagem-pessoas').textContent = pessoasVisiveis.length;
        const escopos = opcoesEscopo();
        mostrarLista('pessoas', pessoasVisiveis, function descreverPessoa(pessoa) {
            const setores = pessoa.setores.map(id => nomeSetor(buscar('setores', id)));
            const perfis = dados.atribuicoes.filter(registro => registro.pessoa === pessoa.id).map(registro =>
                `${buscar('perfis', registro.perfil).nome} · ${escopos.find(escopo => escopo.id === registro.escopo).nome}`
            );
            return [pessoa.nome, pessoa.email, [
                { rotulo: 'Setores', itens: setores.length ? setores : ['Sem setor vinculado'] },
                { rotulo: 'Perfis', itens: perfis.length ? perfis : ['Sem perfil atribuído'] },
            ]];
        });
    }

    /** Carrega as permissões e os usuários apenas do perfil selecionado. */
    function atualizarPerfil() {
        const perfil = buscar('perfis', perfilAtual) ?? dados.perfis[0];
        const detalhes = document.getElementById('detalhes-perfil');
        document.getElementById('contagem-perfis').textContent = dados.perfis.length;
        document.getElementById('perfis-vazios').hidden = Boolean(perfil);
        detalhes.hidden = !perfil;
        abasPerfisCadastrados.replaceChildren(...dados.perfis.map(function montarAba(item) {
            const aba = elemento('button', item.nome);
            aba.type = 'button';
            aba.id = `aba-perfil-${item.id}`;
            aba.setAttribute('role', 'tab');
            aba.setAttribute('aria-controls', detalhes.id);
            aba.setAttribute('aria-selected', String(item === perfil));
            aba.tabIndex = item === perfil ? 0 : -1;
            aba.addEventListener('click', () => abrirPerfil(item.id));
            return aba;
        }));
        if (!perfil) return;

        perfilAtual = perfil.id;
        detalhes.setAttribute('aria-labelledby', `aba-perfil-${perfil.id}`);
        document.getElementById('nome-perfil').textContent = perfil.nome;
        document.getElementById('descricao-perfil').textContent = perfil.descricao;
        const renomear = botaoAcao('Renomear', 'editar', 'perfis', perfil);
        const excluir = botaoAcao('Excluir', 'remover', 'perfis', perfil);
        [renomear, excluir].forEach(botao => {
            botao.classList.remove('controle-registro');
            botao.append(` ${botao.title}`);
        });
        document.getElementById('acoes-perfil').replaceChildren(...(real && (!real.perfis || perfil.protegido) ? [] : [renomear, excluir]));
        if (perfil.protegido) document.getElementById('acoes-perfil').append(elemento('span', 'Administrador fundador · Protegido', 'contagem-configuracoes'));
        const marcacoes = document.getElementById('marcacoes-perfil');
        marcacoes.replaceChildren();
        for (const modulo of new Set(catalogo.map(permissao => permissao.modulo))) {
            adicionarMarcacoes(modulo, 'permissoes', catalogo.filter(permissao => permissao.modulo === modulo), rascunhosPermissoes.get(perfil.id) ?? perfil.permissoes, marcacoes);
        }
        const somenteLeitura = Boolean(real && (!real.perfis || perfil.protegido));
        marcacoes.querySelectorAll('input').forEach(campo => { campo.disabled = somenteLeitura; });
        formularioPerfil.querySelector('button[type="submit"]').hidden = somenteLeitura;
        const atribuicoes = dados.atribuicoes.filter(registro => registro.perfil === perfil.id);
        const escopos = opcoesEscopo();
        document.getElementById('contagem-atribuicoes').textContent = new Set(atribuicoes.map(registro => registro.pessoa)).size;
        document.querySelector('[data-novo="atribuicoes"]').disabled = !dados.pessoas.length;
        mostrarLista('atribuicoes', atribuicoes, registro => [
            buscar('pessoas', registro.pessoa).nome,
            buscar('pessoas', registro.pessoa).email,
            [{ itens: [escopos.find(escopo => escopo.id === registro.escopo).nome] }],
        ]);
    }

    /** Acrescenta um campo rotulado ao formulário em edição. */
    function adicionarCampo(rotulo, controle, nome) {
        controle.name = nome;
        controle.id = `campo-configuracoes-${nome}`;
        const etiqueta = elemento('label', rotulo);
        etiqueta.htmlFor = controle.id;
        const campo = elemento('div', '', 'campo-configuracoes');
        campo.append(etiqueta, controle);
        campos.append(campo);
    }

    function adicionarTexto(rotulo, nome, valor, limite, tipo = 'text', obrigatorio = false) {
        const controle = elemento('input');
        controle.type = tipo;
        controle.value = valor ?? '';
        controle.maxLength = limite;
        controle.required = obrigatorio;
        adicionarCampo(rotulo, controle, nome);
    }

    function adicionarSelecao(rotulo, nome, opcoes, valor, vazio = '') {
        const controle = elemento('select');
        if (vazio) controle.append(new Option(vazio, ''));
        opcoes.forEach(opcao => controle.append(new Option(opcao.nome, opcao.id)));
        if (valor) controle.value = valor;
        adicionarCampo(rotulo, controle, nome);
    }

    /** Lista vínculos e permissões como escolhas múltiplas. */
    function adicionarMarcacoes(rotulo, nome, opcoes, selecionadas = [], destino = campos) {
        const grupo = elemento('fieldset');
        grupo.append(elemento('legend', rotulo));
        if (!opcoes.length) grupo.append(elemento('p', 'Nenhuma opção disponível.'));
        opcoes.forEach(function adicionarOpcao(opcao) {
            const etiqueta = elemento('label', '', 'marcacao-configuracoes');
            const campo = elemento('input');
            campo.type = 'checkbox';
            campo.name = nome;
            campo.value = opcao.id;
            campo.checked = selecionadas.includes(opcao.id);
            etiqueta.append(campo, opcao.nome);
            grupo.append(etiqueta);
        });
        destino.append(grupo);
    }

    /** Evita que a prévia fique com referências a um cadastro removido. */
    function temDependencias(tipo, id) {
        if (tipo === 'unidades') return dados.unidades.some(item => item.superior === id) || dados.setores.some(item => item.unidade === id) || dados.atribuicoes.some(item => item.escopo === `unidade:${id}`);
        if (tipo === 'setores') return dados.setores.some(item => item.superior === id) || dados.pessoas.some(item => item.setores.includes(id)) || dados.atribuicoes.some(item => item.escopo === `setor:${id}`);
        if (tipo === 'perfis') return dados.atribuicoes.some(item => item.perfil === id);
        if (tipo === 'pessoas') return dados.atribuicoes.some(item => item.pessoa === id);
        return false;
    }

    /** Exclui o próprio registro e seus descendentes da escolha de superior. */
    function opcoesSuperiores(tipo, registro) {
        return dados[tipo].filter(function podeSerSuperior(item) {
            if (tipo === 'setores' && item.unidade !== registro.unidade) return false;
            for (let candidato = item; candidato; candidato = buscar(tipo, candidato.superior)) {
                if (candidato.id === registro.id) return false;
            }
            return true;
        });
    }

    /** Prepara somente os campos pertinentes ao cadastro escolhido. */
    function abrirEditor(tipo, registro = {}, acao = 'criar') {
        edicao = { tipo, registro, acao };
        campos.replaceChildren();
        erro.hidden = true;
        confirmar.hidden = false;
        const verbos = tipo === 'perfis' ? { criar: 'Adicionar', editar: 'Renomear', remover: 'Excluir' } : { criar: 'Adicionar', editar: 'Editar', remover: 'Remover' };
        confirmar.textContent = acao === 'remover' ? verbos.remover : 'Salvar';
        document.getElementById('titulo-edicao-configuracoes').textContent = `${verbos[acao]} ${nomes[tipo]}`;

        if (acao === 'remover') {
            const dependencias = temDependencias(tipo, registro.id);
            confirmar.hidden = dependencias;
            campos.append(elemento('p', dependencias ? 'Há vínculos neste cadastro. Revise os vínculos antes de remover.' : (real ? 'Confirma a exclusão deste perfil?' : 'Confirma a remoção deste cadastro da prévia?')));
        } else if (tipo === 'atribuicoes') {
            adicionarSelecao('Pessoa', 'pessoa', dados.pessoas, registro.pessoa);
            adicionarSelecao('Âmbito', 'escopo', opcoesEscopo(), registro.escopo);
        } else {
            adicionarTexto('Nome', 'nome', registro.nome, tipo === 'perfis' ? 150 : 200, 'text', true);
            if (tipo === 'unidades' || tipo === 'setores') {
                adicionarTexto('Sigla', 'sigla', registro.sigla, 40);
                if (real) {
                    adicionarSelecao('Situação', 'status', [{ id: 'ativo', nome: 'Ativo' }, { id: 'inativo', nome: 'Inativo' }], registro.status);
                } else {
                    adicionarSelecao(`${tipo === 'unidades' ? 'Unidade' : 'Setor'} superior`, 'superior', opcoesSuperiores(tipo, registro), registro.superior, 'Sem vínculo superior');
                }
            }
            if (tipo === 'pessoas') {
                adicionarTexto('E-mail', 'email', registro.email, 254, 'email', true);
                if (real) {
                    adicionarTexto('Senha inicial', 'senha', '', 72, 'password', true);
                    const senha = formulario.elements.namedItem('senha');
                    senha.minLength = 8;
                    senha.autocomplete = 'new-password';
                    adicionarSelecao('Perfil', 'perfil', dados.perfis.filter(perfil => !perfil.protegido && perfil.status === 'ativo'), '', 'Selecione');
                    formulario.elements.namedItem('perfil').required = true;
                } else {
                    adicionarMarcacoes('Setores vinculados', 'setores', dados.setores.map(setor => ({ id: setor.id, nome: nomeSetor(setor) })), registro.setores);
                }
            }
            if (tipo === 'perfis') {
                const descricao = elemento('textarea');
                descricao.value = registro.descricao ?? '';
                adicionarCampo('Descrição', descricao, 'descricao');
            }
        }
        dialogo.showModal();
        (acao === 'remover' ? formulario.querySelector('[value="cancelar"]') : campos.querySelector('input, select'))?.focus();
    }

    /** Aplica o formulário em memória e mantém o foco na seção atual. */
    function salvarRegistro(evento) {
        if (evento.submitter?.value !== 'confirmar' || confirmar.hidden) {
            evento.preventDefault();
            return;
        }
        const { tipo, registro, acao } = edicao;
        if (real) {
            evento.preventDefault();
            const valores = new FormData(formulario);
            if (tipo === 'unidades' || tipo === 'setores') {
                valores.set('tabela', tipo);
                valores.set('id', registro.id ?? '');
                valores.set('unidade', registro.unidade ?? '');
                enviarCadastro(real.estrutura, valores);
            } else if (tipo === 'pessoas' && real.equipe) {
                enviarCadastro(real.equipe, valores);
            } else if (tipo === 'perfis' && real.perfis) {
                if (acao === 'remover') {
                    enviarCadastro(`${real.perfis}/${registro.id}/excluir`, valores);
                } else {
                    valores.set('id', registro.id ?? '');
                    (registro.permissoes ?? []).forEach(id => valores.append('permissoes[]', id));
                    enviarCadastro(real.perfis, valores);
                }
            }
            return;
        }
        if (acao === 'remover') {
            dados[tipo] = dados[tipo].filter(item => item.id !== registro.id);
            if (tipo === 'unidades') unidadesAbertas.delete(registro.id);
            if (tipo === 'perfis') rascunhosPermissoes.delete(registro.id);
            if (tipo === 'pessoas') pessoasComPerfisAbertos.delete(registro.id);
        } else {
            const valores = new FormData(formulario);
            const alteracoes = Object.fromEntries([...valores].map(([chave, valor]) => [chave, valor.trim()]));
            if (tipo !== 'atribuicoes' && !alteracoes.nome) {
                evento.preventDefault();
                erro.textContent = 'Informe o nome.';
                erro.hidden = false;
                formulario.elements.namedItem('nome').focus();
                return;
            }
            if (tipo === 'pessoas') alteracoes.setores = valores.getAll('setores');
            if (tipo === 'atribuicoes') alteracoes.perfil = registro.perfil;
            if (tipo === 'setores') alteracoes.unidade = registro.unidade;
            if (acao === 'criar') {
                alteracoes.id = String(++sequencia);
                dados[tipo].push(alteracoes);
                if (tipo === 'unidades') unidadesAbertas.add(alteracoes.id);
                if (tipo === 'perfis') {
                    alteracoes.permissoes = [];
                    perfilAtual = alteracoes.id;
                    selecionarAbaPerfil(abasPerfil[0]);
                }
            } else {
                Object.assign(registro, alteracoes);
            }
        }
        atualizarListas();
        aviso.textContent = acao === 'remover' ? 'Cadastro removido da prévia.' : 'Cadastro salvo na prévia.';
        dialogo.addEventListener('close', function restaurarFoco() {
            const seletor = tipo === 'setores' ? `#setores-unidade-${registro.unidade} [data-novo="setores"]` : `[data-novo="${tipo}"]`;
            const botao = (tipo === 'perfis' && document.getElementById(`aba-perfil-${perfilAtual}`)) || document.querySelector(seletor);
            (botao?.disabled ? document.querySelector('.navegacao-configuracoes [aria-current="page"]') : botao)?.focus();
        }, { once: true });
    }

    /** Envia o modal ao fluxo POST protegido e volta à aba após a gravação. */
    function enviarCadastro(destino, valores) {
        const envio = elemento('form');
        envio.method = 'post';
        envio.action = destino;
        envio.hidden = true;
        valores.set(real.csrfNome, real.csrfValor);
        for (const [nome, valor] of valores) {
            const campo = elemento('input');
            campo.type = 'hidden';
            campo.name = nome;
            campo.value = valor;
            envio.append(campo);
        }
        document.body.append(envio);
        envio.submit();
    }

    /** Navega entre as áreas e aceita links diretos pelo menu. */
    function mostrarPagina() {
        const links = Array.from(document.querySelectorAll('[data-pagina-configuracoes]'));
        const atual = links.find(link => link.hash === location.hash) ?? links[0];
        links.forEach(function mostrarSecao(link) {
            document.getElementById(link.dataset.paginaConfiguracoes).hidden = link !== atual;
            if (link === atual) link.setAttribute('aria-current', 'page');
            else link.removeAttribute('aria-current');
        });
    }

    /** Alterna as abas sem recarregar o formulário de permissões. */
    function selecionarAbaPerfil(atual) {
        abasPerfil.forEach(function atualizarAba(aba) {
            const ativa = aba === atual;
            aba.setAttribute('aria-selected', String(ativa));
            aba.tabIndex = ativa ? 0 : -1;
            document.getElementById(aba.getAttribute('aria-controls')).hidden = !ativa;
        });
    }

    abasPerfil.forEach(function configurarAba(aba, indice) {
        aba.addEventListener('click', () => selecionarAbaPerfil(aba));
        aba.addEventListener('keydown', function navegarAbas(evento) {
            const indices = { ArrowLeft: 1 - indice, ArrowRight: 1 - indice, Home: 0, End: 1 };
            if (!Object.hasOwn(indices, evento.key)) return;
            evento.preventDefault();
            const destino = abasPerfil[indices[evento.key]];
            selecionarAbaPerfil(destino);
            destino.focus();
        });
    });
    /** Ativa um perfil e mantém o foco na aba após redesenhar seus dados. */
    function abrirPerfil(id) {
        perfilAtual = id;
        atualizarPerfil();
        document.getElementById(`aba-perfil-${id}`).focus();
    }

    abasPerfisCadastrados.addEventListener('keydown', function navegarPerfis(evento) {
        const indice = dados.perfis.findIndex(perfil => perfil.id === perfilAtual);
        const total = dados.perfis.length;
        const indices = { ArrowLeft: (indice + total - 1) % total, ArrowRight: (indice + 1) % total, Home: 0, End: total - 1 };
        if (!Object.hasOwn(indices, evento.key)) return;
        evento.preventDefault();
        abrirPerfil(dados.perfis[indices[evento.key]].id);
    });
    formularioPerfil.addEventListener('change', function guardarMarcacoes() {
        rascunhosPermissoes.set(perfilAtual, new FormData(formularioPerfil).getAll('permissoes'));
    });
    formularioPerfil.addEventListener('submit', function salvarPermissoes(evento) {
        evento.preventDefault();
        if (real) {
            const perfil = buscar('perfis', perfilAtual);
            if (!real.perfis || perfil.protegido) return;
            const valores = new FormData();
            valores.set('id', perfil.id);
            valores.set('nome', perfil.nome);
            valores.set('descricao', perfil.descricao);
            new FormData(formularioPerfil).getAll('permissoes').forEach(id => valores.append('permissoes[]', id));
            enviarCadastro(real.perfis, valores);
            return;
        }
        buscar('perfis', perfilAtual).permissoes = new FormData(formularioPerfil).getAll('permissoes');
        rascunhosPermissoes.delete(perfilAtual);
        aviso.textContent = 'Permissões salvas na prévia.';
    });
    document.querySelectorAll('[data-novo]').forEach(botao => {
        const tipo = botao.dataset.novo;
        if (real && (tipo === 'atribuicoes' || (tipo === 'pessoas' && !real.equipe) || (tipo === 'perfis' && !real.perfis))) {
            botao.hidden = true;
            return;
        }
        botao.addEventListener('click', function criarRegistro() {
        const tipo = botao.dataset.novo;
        abrirEditor(tipo, tipo === 'atribuicoes' ? { perfil: perfilAtual } : {});
        });
    });
    formulario.addEventListener('submit', salvarRegistro);
    busca.addEventListener('input', atualizarEquipe);
    window.addEventListener('hashchange', mostrarPagina);
    mostrarPagina();
    atualizarListas();
})();
