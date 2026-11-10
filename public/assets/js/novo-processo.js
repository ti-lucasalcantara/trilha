/** Mostra e envia apenas as autorizações do nível escolhido. */
(() => {
    const acesso = document.getElementById('acesso-processo');
    const campos = document.querySelectorAll('[data-acesso-processo]');

    function atualizarAutorizacoes() {
        campos.forEach(campo => {
            const ativo = campo.dataset.acessoProcesso === acesso.value;
            campo.hidden = !ativo;
            campo.disabled = !ativo;
        });
    }

    acesso.addEventListener('change', atualizarAutorizacoes);
    atualizarAutorizacoes();
})();
