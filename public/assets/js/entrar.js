/** Controla a visibilidade da senha e os avisos da tela de entrada. */
(() => {
    'use strict';

    const formulario = document.getElementById('formulario-entrada');
    const campoSenha = document.getElementById('senha');
    const botaoAlternarSenha = document.getElementById('alternar-senha');
    const botaoRecuperarSenha = document.getElementById('recuperar-senha');
    const avisoEntrada = document.getElementById('aviso-entrada');

    // Evita executar os eventos quando os elementos da tela não estão presentes.
    if (!formulario || !campoSenha || !botaoAlternarSenha || !botaoRecuperarSenha || !avisoEntrada) return;

    /** Mostra uma mensagem informativa sem inserir conteúdo HTML. */
    function exibirAviso(mensagem) {
        avisoEntrada.textContent = mensagem;
        avisoEntrada.hidden = false;
    }

    /** Alterna a exibição da senha e atualiza o nome acessível do botão. */
    function alternarVisibilidadeSenha() {
        const senhaVisivel = campoSenha.type === 'password';
        campoSenha.type = senhaVisivel ? 'text' : 'password';
        botaoAlternarSenha.setAttribute('aria-pressed', String(senhaVisivel));
        botaoAlternarSenha.setAttribute('aria-label', senhaVisivel ? 'Ocultar senha' : 'Mostrar senha');
        botaoAlternarSenha.querySelector('i').className = senhaVisivel ? 'fa-regular fa-eye-slash' : 'fa-regular fa-eye';
    }

    /** Informa que a recuperação de senha será implementada posteriormente. */
    function informarRecuperacaoIndisponivel() {
        exibirAviso('A recuperação de senha ainda não está disponível. Entre em contato com o administrador da sua organização.');
    }

    botaoAlternarSenha.addEventListener('click', alternarVisibilidadeSenha);
    botaoRecuperarSenha.addEventListener('click', informarRecuperacaoIndisponivel);
})();
