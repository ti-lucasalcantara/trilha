/** Mostra e envia apenas as autorizações do nível escolhido. */
(() => {
    const formulario = document.getElementById('form-abertura-processo');
    if (!formulario) return;
    const revisao = formulario.elements.namedItem('revisao');
    if (revisao) {
        const botaoSalvar = formulario.querySelector('[type="submit"]');
        const retorno = document.getElementById('retorno-edicao-processo');
        const csrf = formulario.elements.namedItem(formulario.dataset.csrf);

        formulario.addEventListener('submit', async evento => {
            evento.preventDefault();
            if (botaoSalvar.disabled) return;
            botaoSalvar.disabled = true;
            retorno.classList.remove('text-danger', 'text-success');
            retorno.textContent = 'Salvando…';
            let podeEditar = true;
            try {
                const resposta = await fetch(formulario.action, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(formulario),
                });
                const dados = await resposta.json();
                if (dados.csrf) csrf.value = dados.csrf;
                if (!resposta.ok) {
                    retorno.classList.add('text-danger');
                    retorno.textContent = dados.erro;
                    return;
                }
                podeEditar = Boolean(dados.revisao);
                if (podeEditar) revisao.value = dados.revisao;
                retorno.classList.add('text-success');
                retorno.textContent = dados.mensagem;
            } catch {
                retorno.classList.add('text-danger');
                retorno.textContent = 'Não foi possível confirmar o salvamento. Confira sua conexão e tente novamente.';
            } finally {
                botaoSalvar.disabled = !podeEditar;
            }
        });
    }
    const acesso = formulario.elements.namedItem('acesso');
    const campos = formulario.querySelectorAll('[data-acesso-processo]');

    function atualizarAutorizacoes() {
        campos.forEach(campo => {
            const ativo = campo.dataset.acessoProcesso === acesso.value;
            campo.hidden = !ativo;
            campo.disabled = !ativo;
        });
    }

    acesso.addEventListener('change', atualizarAutorizacoes);
    atualizarAutorizacoes();

    const responsavel = formulario.elements.namedItem('responsavel');
    if (responsavel) {
        const pessoas = Array.from(formulario.querySelectorAll('[name="pessoas_autorizadas[]"]'));
        let autor;
        let selecaoAnterior;

        function atualizarAutorAutorizado() {
            if (autor) {
                autor.disabled = false;
                autor.checked = selecaoAnterior;
            }
            autor = pessoas.find(pessoa => pessoa.value === responsavel.value);
            if (autor) {
                selecaoAnterior = autor.checked;
                autor.checked = true;
                autor.disabled = true;
            }
        }

        responsavel.addEventListener('change', atualizarAutorAutorizado);
        atualizarAutorAutorizado();
    }

    const tipo = formulario.elements.namedItem('tipo');
    if (tipo && window.jQuery?.fn.select2) {
        const seletor = window.jQuery(tipo).select2({
            language: 'pt-BR',
            placeholder: 'Selecione o tipo',
            width: '100%',
            dropdownParent: window.jQuery(tipo.parentElement),
        });
        const campoSelecao = tipo.parentElement.querySelector('.select2-selection');
        campoSelecao.setAttribute('aria-labelledby', 'rotulo-tipo-processo select2-tipo-processo-container');
        campoSelecao.setAttribute('aria-required', 'true');
        tipo.addEventListener('invalid', evento => {
            evento.preventDefault();
            seletor.select2('open');
        });
    }

    if (!window.Quill) return;
    const descricao = formulario.elements.namedItem('descricao');
    const descricaoFormatada = formulario.elements.namedItem('descricao_formatada');
    const editor = new window.Quill('#conteudo-descricao-processo', {
        theme: 'snow',
        placeholder: descricao.placeholder,
        formats: ['bold', 'italic', 'underline', 'list'],
        modules: { toolbar: '#barra-descricao-processo' },
    });
    editor.root.setAttribute('role', 'textbox');
    editor.root.setAttribute('aria-multiline', 'true');
    editor.root.setAttribute('aria-labelledby', 'rotulo-descricao-processo');
    if (descricaoFormatada.value) {
        editor.setContents(editor.clipboard.convert({ html: descricaoFormatada.value }));
    } else {
        editor.setText(descricao.value);
    }

    function sincronizarDescricao() {
        descricao.value = editor.getText().trim();
        descricaoFormatada.value = descricao.value ? editor.getSemanticHTML() : '';
    }

    editor.on('text-change', sincronizarDescricao);
    formulario.addEventListener('submit', sincronizarDescricao);
    sincronizarDescricao();
    descricao.hidden = true;
    descricaoFormatada.disabled = false;
    document.getElementById('editor-descricao-processo').hidden = false;
    document.getElementById('rotulo-descricao-processo').addEventListener('click', () => editor.focus());
})();
