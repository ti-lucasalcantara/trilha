/** Gerencia os cadastros e as permissões da organização. */
(() => {
    'use strict';

    const configuracao = JSON.parse(document.querySelector('.area-configuracoes').dataset.configuracoes);
    const { dados, catalogo } = configuracao;
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
    let perfilAtual = dados.perfis?.[0]?.id;
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
            if (['unidades', 'setores'].includes(tipo)) acoes.append(botaoAcao('Editar', 'editar', tipo, registro));
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

    if (modeloSetores) {
        document.getElementById('contagem-unidades').textContent = dados.unidades.length;
        mostrarLista('unidades', dados.unidades, registro => [registro.nome, [registro.sigla, buscar('unidades', registro.superior)?.nome].filter(Boolean).join(' · ')]);
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
        document.getElementById('acoes-perfil').replaceChildren(...((!configuracao.perfis || perfil.protegido) ? [] : [renomear, excluir]));
        if (perfil.protegido) document.getElementById('acoes-perfil').append(elemento('span', 'Administrador fundador · Protegido', 'contagem-configuracoes'));
        const marcacoes = document.getElementById('marcacoes-perfil');
        marcacoes.replaceChildren();
        for (const modulo of new Set(catalogo.map(permissao => permissao.modulo))) {
            adicionarMarcacoes(modulo, 'permissoes', catalogo.filter(permissao => permissao.modulo === modulo), rascunhosPermissoes.get(perfil.id) ?? perfil.permissoes, marcacoes);
        }
        const somenteLeitura = Boolean((!configuracao.perfis || perfil.protegido));
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

    /** Identifica vínculos que impedem a remoção do perfil. */
    function temDependencias(tipo, id) {
        if (tipo === 'unidades') return dados.unidades.some(item => item.superior === id) || dados.setores.some(item => item.unidade === id) || dados.atribuicoes.some(item => item.escopo === `unidade:${id}`);
        if (tipo === 'setores') return dados.setores.some(item => item.superior === id) || dados.pessoas.some(item => item.setores.includes(id)) || dados.atribuicoes.some(item => item.escopo === `setor:${id}`);
        if (tipo === 'perfis') return dados.atribuicoes.some(item => item.perfil === id);
        if (tipo === 'pessoas') return dados.atribuicoes.some(item => item.pessoa === id);
        return false;
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
            campos.append(elemento('p', dependencias ? 'Há vínculos neste cadastro. Revise os vínculos antes de remover.' : 'Confirma a exclusão deste perfil?'));
        } else if (tipo === 'atribuicoes') {
            adicionarSelecao('Pessoa', 'pessoa', dados.pessoas, registro.pessoa);
            adicionarSelecao('Âmbito', 'escopo', opcoesEscopo(), registro.escopo);
        } else {
            adicionarTexto('Nome', 'nome', registro.nome, tipo === 'perfis' ? 150 : 200, 'text', true);
            if (tipo === 'unidades' || tipo === 'setores') {
                adicionarTexto('Sigla', 'sigla', registro.sigla, 40);
                adicionarSelecao('Situação', 'status', [{ id: 'ativo', nome: 'Ativo' }, { id: 'inativo', nome: 'Inativo' }], registro.status);
            }
            if (tipo === 'pessoas') {
                adicionarTexto('E-mail', 'email', registro.email, 254, 'email', true);
                adicionarTexto('Senha inicial', 'senha', '', 72, 'password', true);
                const senha = formulario.elements.namedItem('senha');
                senha.minLength = 8;
                senha.autocomplete = 'new-password';
                adicionarSelecao('Perfil', 'perfil', dados.perfis.filter(perfil => !perfil.protegido && perfil.status === 'ativo'), '', 'Selecione');
                formulario.elements.namedItem('perfil').required = true;
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

    /** Envia os dados da operação ao servidor. */
    function salvarRegistro(evento) {
        if (evento.submitter?.value !== 'confirmar' || confirmar.hidden) {
            evento.preventDefault();
            return;
        }
        const { tipo, registro, acao } = edicao;
        evento.preventDefault();
        const valores = new FormData(formulario);
        if (tipo === 'unidades' || tipo === 'setores') {
            valores.set('tabela', tipo);
            valores.set('id', registro.id ?? '');
            valores.set('unidade', registro.unidade ?? '');
            enviarCadastro(configuracao.estrutura, valores);
        } else if (tipo === 'pessoas' && configuracao.equipe) {
            enviarCadastro(configuracao.equipe, valores);
        } else if (tipo === 'perfis' && configuracao.perfis) {
            if (acao === 'remover') {
                enviarCadastro(`${configuracao.perfis}/${registro.id}/excluir`, valores);
            } else {
                valores.set('id', registro.id ?? '');
                (registro.permissoes ?? []).forEach(id => valores.append('permissoes[]', id));
                enviarCadastro(configuracao.perfis, valores);
            }
        }
    }

    /** Envia o modal ao fluxo POST protegido e volta à aba após a gravação. */
    function enviarCadastro(destino, valores) {
        const envio = elemento('form');
        envio.method = 'post';
        envio.action = destino;
        envio.hidden = true;
        valores.set(configuracao.csrfNome, configuracao.csrfValor);
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

    abasPerfisCadastrados?.addEventListener('keydown', function navegarPerfis(evento) {
        const indice = dados.perfis.findIndex(perfil => perfil.id === perfilAtual);
        const total = dados.perfis.length;
        const indices = { ArrowLeft: (indice + total - 1) % total, ArrowRight: (indice + 1) % total, Home: 0, End: total - 1 };
        if (!Object.hasOwn(indices, evento.key)) return;
        evento.preventDefault();
        abrirPerfil(dados.perfis[indices[evento.key]].id);
    });
    formularioPerfil?.addEventListener('change', function guardarMarcacoes() {
        rascunhosPermissoes.set(perfilAtual, new FormData(formularioPerfil).getAll('permissoes'));
    });
    formularioPerfil?.addEventListener('submit', function salvarPermissoes(evento) {
        evento.preventDefault();
        const perfil = buscar('perfis', perfilAtual);
        if (!configuracao.perfis || perfil.protegido) return;
        const valores = new FormData();
        valores.set('id', perfil.id);
        valores.set('nome', perfil.nome);
        valores.set('descricao', perfil.descricao);
        new FormData(formularioPerfil).getAll('permissoes').forEach(id => valores.append('permissoes[]', id));
        enviarCadastro(configuracao.perfis, valores);
    });
    document.querySelectorAll('[data-novo]').forEach(botao => {
        const tipo = botao.dataset.novo;
        if ((tipo === 'atribuicoes' || (tipo === 'pessoas' && !configuracao.equipe) || (tipo === 'perfis' && !configuracao.perfis))) {
            botao.hidden = true;
            return;
        }
        botao.addEventListener('click', function criarRegistro() {
        const tipo = botao.dataset.novo;
        abrirEditor(tipo, tipo === 'atribuicoes' ? { perfil: perfilAtual } : {});
        });
    });
    formulario.addEventListener('submit', salvarRegistro);
    if (busca) {
        busca.addEventListener('input', atualizarEquipe);
        atualizarEquipe();
    }
    if (formularioPerfil) atualizarPerfil();
})();
