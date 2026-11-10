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
