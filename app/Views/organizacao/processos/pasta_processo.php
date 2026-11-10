<!-- Reutiliza o formulário para qualquer linha da listagem real. -->
<dialog id="dialogo-pasta-processo" class="dialogo-colunas dialogo-pasta" aria-labelledby="titulo-pasta-processo">
    <h2 id="titulo-pasta-processo">Alterar pasta do processo</h2>
    <p id="processo-da-pasta"></p>
    <form method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="setor_contexto" value="<?= esc($contextoTrabalho['setor']['id'] ?? '', 'attr') ?>">
        <label>Vínculo atual
            <select name="origem"></select>
        </label>
        <label>Destino
            <select name="destino">
                <option value="">Sem pasta</option>
                <?php foreach ($caminhosPastas as $id => $caminho): ?>
                    <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($caminho) ?></option>
                <?php endforeach ?>
            </select>
        </label>
        <?php if ($modoSimulacao): ?>
            <label>Registrar em nome de
                <select name="responsavel" required>
                    <option value="">Selecione um usuário interno</option>
                    <?php foreach ($responsaveisPasta as $usuario): ?>
                        <option value="<?= esc((string) $usuario['vinculo_id'], 'attr') ?>"><?= esc($usuario['nome']) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
        <?php endif ?>
        <footer class="rodape-colunas">
            <button type="button" class="botao-filtros" data-cancelar-dialogo>Cancelar</button>
            <button type="submit" class="btn btn-primary">Salvar pasta</button>
        </footer>
    </form>
</dialog>
