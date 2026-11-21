<!-- O diálogo distingue informações já disponíveis de operações ainda em preparação. -->
<dialog id="dialogo-acao" class="dialogo-acao" aria-labelledby="titulo-acao" aria-describedby="descricao-acao">
    <h2 id="titulo-acao"></h2>
    <p id="descricao-acao"></p>
    <p class="referencia-acao" id="referencia-acao"></p>
    <dl class="informacoes-processo" id="informacoes-processo-real" hidden>
        <div><dt>Tipo</dt><dd><?= esc($processo['tipo']) ?></dd></div>
        <div><dt>Origem</dt><dd><?= esc($processo['unidade'] . ' / ' . $processo['setor']) ?></dd></div>
        <div><dt>Responsável</dt><dd><?= esc($processo['responsavel']) ?></dd></div>
        <div><dt>Abertura</dt><dd><?= esc(date('d/m/Y', strtotime($processo['abertura']))) ?></dd></div>
    </dl>
    <footer class="rodape-acao"><button type="button" class="btn btn-primary ms-auto" data-fechar-acao>Fechar</button></footer>
</dialog>
<?php if ($documentos): ?>
    <dialog id="dialogo-exclusao-documento" class="dialogo-acao" aria-labelledby="titulo-exclusao-documento" aria-describedby="mensagem-exclusao-documento">
        <h2 id="titulo-exclusao-documento"></h2>
        <p id="mensagem-exclusao-documento"></p>
        <p class="referencia-acao" id="referencia-exclusao-documento"></p>
        <form method="post" class="rodape-acao" data-endereco="<?= esc($enderecoProcesso . '/documentos', 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="acao">
            <button type="button" class="btn btn-outline-secondary" id="cancelar-exclusao-documento" autofocus>Cancelar</button>
            <button type="submit" class="btn" id="confirmar-exclusao-documento"></button>
        </form>
    </dialog>
<?php endif ?>
