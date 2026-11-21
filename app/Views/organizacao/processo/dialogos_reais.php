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
    <div class="painel-acao" data-painel-acao="historico" hidden>
        <ol class="historico-processo">
            <?php
            $rotulosHistorico = [
                'processo.encaminhar' => 'Processo encaminhado', 'processo.mover' => 'Vínculo com pasta alterado',
                'processo.arquivar' => 'Processo arquivado', 'processo.excluir' => 'Processo enviado à lixeira',
                'processo.restaurar' => 'Processo restaurado',
            ];
            ?>
            <?php foreach ($historicoProcesso as $evento): ?>
                <li>
                    <strong><?= esc($rotulosHistorico[$evento['tipo']] ?? 'Atualização do processo') ?></strong>
                    <span><?= esc(date('d/m/Y H:i', strtotime($evento['ocorrido_em']))) ?>
                        <?php if ($evento['autor']): ?> · <?= esc($evento['autor']) ?><?php endif ?>
                    </span>
                </li>
            <?php endforeach ?>
            <li><strong>Processo aberto</strong><span><?= esc(date('d/m/Y', strtotime($processo['abertura']))) ?> · <?= esc($processo['responsavel']) ?></span></li>
        </ol>
    </div>
    <?php $nomesUnidades = array_column($contextoTrabalho['unidades'], 'nome', 'id'); ?>
    <?php foreach (['tramitar' => $destinosTramitacao, 'pastas' => $pastasDisponiveis] as $acao => $opcoes): ?>
        <?php if (!$opcoes || ($simulacaoSemVinculo && !$autoresOperacao)) continue; ?>
        <?php $tramitar = $acao === 'tramitar'; ?>
        <form class="painel-acao" data-painel-acao="<?= $acao ?>" method="post" hidden
              action="<?= esc($tramitar ? site_url('organizacao/' . $identificadorOrganizacao . '/processos/lote') : $enderecoProcesso . '/pasta', 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="retorno" value="processo">
            <input type="hidden" name="setor_contexto" value="<?= esc($contextoTrabalho['setor']['id'], 'attr') ?>">
            <?php if ($tramitar): ?>
                <input type="hidden" name="caixa" value="ativos">
                <input type="hidden" name="acao" value="encaminhar">
                <input type="hidden" name="processos[]" value="<?= esc($processo['hash_publico'], 'attr') ?>">
                <label>Setor de destino
                    <select name="setor_destino[]" required>
                        <option value="">Selecione o setor</option>
                        <?php foreach ($opcoes as $id => $destino): ?>
                            <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($nomesUnidades[$destino['unidade']] . ' / ' . $destino['nome']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <div class="form-check mt-3">
                    <input class="form-check-input w-auto" id="manter-processo-origem" type="checkbox" name="manter[]" value="<?= esc($processo['hash_publico'], 'attr') ?>" checked>
                    <label class="form-check-label" for="manter-processo-origem">Manter o processo no setor atual</label>
                </div>
            <?php else: ?>
                <label>Pasta atual
                    <select name="origem">
                        <?php if (!$pastasAtuais): ?><option value="">Sem pasta neste setor</option><?php endif ?>
                        <?php foreach ($pastasAtuais as $id => $caminho): ?>
                            <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($caminho) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
                <label>Destino
                    <select name="destino" <?= !$pastasAtuais ? 'required' : '' ?>>
                        <option value=""><?= $pastasAtuais ? 'Remover vínculo com a pasta atual' : 'Selecione uma pasta' ?></option>
                        <?php foreach ($opcoes as $id => $caminho): ?>
                            <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($caminho) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
            <?php endif ?>
            <?php if ($simulacaoSemVinculo): ?>
                <label>Registrar a ação em nome de
                    <select name="responsavel" required>
                        <option value="">Selecione um usuário</option>
                        <?php foreach ($autoresOperacao as $autor): ?>
                            <option value="<?= esc((string) $autor['vinculo_id'], 'attr') ?>"><?= esc($autor['nome']) ?></option>
                        <?php endforeach ?>
                    </select>
                </label>
            <?php endif ?>
            <div class="mt-3"><button type="submit" class="btn btn-primary"><?= $tramitar ? 'Tramitar' : 'Salvar pasta' ?></button></div>
        </form>
    <?php endforeach ?>
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
