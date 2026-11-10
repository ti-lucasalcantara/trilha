<!-- Confirma a ação e envia somente os processos marcados na página visível. -->
<dialog id="dialogo-lote-processos" class="dialogo-colunas dialogo-pasta" aria-labelledby="titulo-lote-processos">
    <h2 id="titulo-lote-processos">Ação em lote</h2>
    <p id="descricao-lote-processos"></p>
    <form method="post" action="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/lote'), 'attr') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="setor_contexto" value="<?= esc($contextoTrabalho['setor']['id'] ?? '', 'attr') ?>">
        <input type="hidden" name="caixa" value="<?= esc($caixaProcessos, 'attr') ?>">
        <input type="hidden" name="acao">
        <div id="itens-lote-processos"></div>
        <label id="destino-lote-processos">Pasta de destino
            <select name="destino">
                <option value="">Selecione uma pasta</option>
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
            <button type="submit" class="btn btn-primary">Confirmar</button>
        </footer>
    </form>
</dialog>
