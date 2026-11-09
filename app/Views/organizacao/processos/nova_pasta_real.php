<!-- A criação real usa o contexto da organização e o token do formulário. -->
<dialog id="dialogo-pasta-real" class="dialogo-colunas dialogo-pasta" aria-labelledby="titulo-pasta-real">
    <h2 id="titulo-pasta-real">Nova pasta</h2>
    <?php $setorAtivo = $contextoTrabalho['setor']; ?>
    <?php if (!$setorAtivo || ($modoSimulacao && !$responsaveisPasta)): ?>
        <p id="contexto-pasta"><?= !$setorAtivo ? 'Cadastre e selecione um setor ativo para criar a pasta.' : 'Para criar uma pasta na simulação, vincule primeiro um usuário interno ativo à empresa.' ?></p>
        <form method="dialog" class="rodape-colunas"><button class="botao-filtros">Fechar</button></form>
    <?php else: ?>
        <form method="post" action="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/pastas'), 'attr') ?>">
            <?= csrf_field() ?>
            <label for="nome-pasta-real">Nome da pasta<input id="nome-pasta-real" name="nome" maxlength="200" required autofocus></label>
            <input type="hidden" name="setor_contexto" value="<?= esc($setorAtivo['id'], 'attr') ?>">
            <p id="contexto-pasta">A pasta será criada em <strong><?= esc($setorAtivo['nome']) ?></strong>, o setor selecionado no cabeçalho.</p>
            <?php if ($modoSimulacao): ?>
                <label for="responsavel-pasta-real">Registrar em nome de
                    <select id="responsavel-pasta-real" name="responsavel" required>
                        <option value="">Selecione um usuário interno</option>
                        <?php foreach ($responsaveisPasta as $usuario): ?>
                            <option value="<?= esc((string) $usuario['vinculo_id'], 'attr') ?>"><?= esc($usuario['nome'] . ' · ' . $usuario['email']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
            <?php endif ?>
            <footer class="rodape-colunas">
                <button class="botao-filtros" type="button" data-cancelar-dialogo>Cancelar</button>
                <button class="btn btn-primary" type="submit">Criar pasta</button>
            </footer>
        </form>
    <?php endif ?>
</dialog>
