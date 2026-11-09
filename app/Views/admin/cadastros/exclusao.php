<dialog id="confirmar-exclusao-admin" class="dialogo-admin" aria-labelledby="titulo-exclusao-admin">
    <h2 id="titulo-exclusao-admin">Excluir <?= esc($singular) ?></h2>
    <p>Confirma a exclusão de <strong id="nome-exclusao-admin"></strong>?</p>
    <form method="dialog">
        <button class="botao-admin secundario" value="cancelar" autofocus>Cancelar</button>
        <button class="botao-admin perigo" value="excluir">Excluir</button>
    </form>
</dialog>
