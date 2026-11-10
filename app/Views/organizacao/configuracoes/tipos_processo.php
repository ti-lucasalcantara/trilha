<?php
// Mantém cadastro, edição e confirmação de exclusão dentro da mesma aba.
$baseTipos = site_url('organizacao/' . $identificadorOrganizacao);
$abaTipos = $baseTipos . '/configuracoes#tipos-processo';
$enderecoTipo = static fn (array $consulta) => $baseTipos . '/configuracoes?' . http_build_query($consulta) . '#tipos-processo';
$porId = array_column($tiposProcesso, null, 'id');
$novoTipo = $edicaoTipoProcesso === 'novo';
$tipoEditado = is_string($edicaoTipoProcesso) ? ($porId[$edicaoTipoProcesso] ?? null) : null;
$tipoExcluido = is_string($exclusaoTipoProcesso) ? ($porId[$exclusaoTipoProcesso] ?? null) : null;
$dadosTipo = $tipoEditado ?? [
    'id' => '', 'nome' => '', 'sigla' => '', 'descricao' => '', 'ativo' => '1',
    'mascara' => '{sigla}-{numero}/{ano}', 'sigla_numeracao' => '', 'digitos_minimos' => '4',
];
$dadosAnteriores = session()->getFlashdata('dadosTipoProcesso');
if (is_array($dadosAnteriores) && (string) $dadosTipo['id'] === $dadosAnteriores['id']) {
    $dadosTipo = array_replace($dadosTipo, $dadosAnteriores);
}
?>
<section id="tipos-processo" class="cartao-configuracoes" aria-labelledby="titulo-tipos-processo" hidden>
    <header>
        <div>
            <h2 id="titulo-tipos-processo">Tipos de processo <span class="contagem-configuracoes"><?= count($tiposProcesso) ?></span></h2>
            <p>Gerencie os tipos disponíveis na abertura e o formato dos seus números oficiais.</p>
        </div>
        <?php if (!$novoTipo && !$tipoEditado): ?>
            <a class="btn btn-primary" href="<?= esc($enderecoTipo(['tipo' => 'novo']), 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo tipo</a>
        <?php endif ?>
    </header>
    <?php if ($mensagem = session()->getFlashdata('erroTipoProcesso')): ?>
        <p class="alert alert-danger mx-3" role="alert"><?= esc($mensagem) ?></p>
    <?php elseif ($mensagem = session()->getFlashdata('sucessoTipoProcesso')): ?>
        <p class="alert alert-success mx-3" role="status"><?= esc($mensagem) ?></p>
    <?php endif ?>

    <?php if ($novoTipo || $tipoEditado): ?>
        <form class="border-top p-3 p-md-4" method="post" action="<?= esc($baseTipos . '/tipos-processo', 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc((string) $dadosTipo['id'], 'attr') ?>">
            <h3 class="h4 mb-3"><?= $novoTipo ? 'Novo tipo de processo' : 'Editar tipo de processo' ?></h3>
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label" for="nome-tipo-processo">Nome</label>
                    <input class="form-control" id="nome-tipo-processo" name="nome" maxlength="150" value="<?= esc($dadosTipo['nome'], 'attr') ?>" required>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="sigla-tipo-processo">Sigla do tipo</label>
                    <input class="form-control" id="sigla-tipo-processo" name="sigla" maxlength="40" value="<?= esc($dadosTipo['sigla'], 'attr') ?>" placeholder="COMPRAS" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="descricao-tipo-processo">Descrição <span class="text-secondary fw-normal">(opcional)</span></label>
                    <textarea class="form-control" id="descricao-tipo-processo" name="descricao" rows="3"><?= esc($dadosTipo['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-12 col-md-8">
                    <label class="form-label" for="mascara-tipo-processo">Máscara da numeração</label>
                    <input class="form-control" id="mascara-tipo-processo" name="mascara" maxlength="200" value="<?= esc($dadosTipo['mascara'], 'attr') ?>" aria-describedby="ajuda-mascara-tipo" required>
                    <p class="form-hint" id="ajuda-mascara-tipo">Use <code>{numero}</code> e <code>{ano}</code>. Inclua <code>{sigla}</code> onde preferir ou escreva um prefixo fixo. Exemplo <code>{numero}/{ano}-{sigla}</code>.</p>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="digitos-tipo-processo">Dígitos mínimos</label>
                    <input class="form-control" id="digitos-tipo-processo" name="digitos_minimos" type="number" min="0" max="200" step="1" value="<?= esc((string) $dadosTipo['digitos_minimos'], 'attr') ?>" required>
                    <p class="form-hint">Com 4 dígitos, o número 1 aparece como 0001.</p>
                </div>
                <div class="col-12 col-md-8">
                    <label class="form-label" for="sigla-numeracao-tipo">Sigla na numeração <span class="text-secondary fw-normal">(opcional)</span></label>
                    <input class="form-control" id="sigla-numeracao-tipo" name="sigla_numeracao" maxlength="40" value="<?= esc($dadosTipo['sigla_numeracao'], 'attr') ?>" placeholder="Usar a sigla do tipo">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="ativo-tipo-processo">Disponibilidade</label>
                    <select class="form-select" id="ativo-tipo-processo" name="ativo">
                        <option value="1" <?= $dadosTipo['ativo'] ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= !$dadosTipo['ativo'] ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>
            <p class="text-secondary mt-3">A sequência é anual por tipo. Alterações na máscara valem para novos processos.</p>
            <div class="d-flex justify-content-end flex-wrap gap-2 mt-3">
                <a class="btn btn-outline-secondary" href="<?= esc($abaTipos, 'attr') ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit">Salvar tipo</button>
            </div>
        </form>
    <?php elseif ($tipoExcluido && !$tipoExcluido['em_uso']): ?>
        <form class="alert alert-warning mx-3" method="post" action="<?= esc($baseTipos . '/tipos-processo/' . $tipoExcluido['id'] . '/excluir', 'attr') ?>">
            <?= csrf_field() ?>
            <p>Confirma a exclusão de <strong><?= esc($tipoExcluido['nome']) ?></strong>?</p>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <a class="btn btn-outline-secondary" href="<?= esc($abaTipos, 'attr') ?>">Cancelar</a>
                <button class="btn btn-danger" type="submit">Excluir tipo</button>
            </div>
        </form>
    <?php elseif (!$tiposProcesso): ?>
        <p class="vazio-configuracoes">Nenhum tipo de processo cadastrado.</p>
    <?php endif ?>

    <?php if ($tiposProcesso && !$novoTipo && !$tipoEditado): ?>
        <div class="table-responsive">
            <table class="table table-vcenter card-table mb-0">
                <thead><tr><th scope="col">Tipo</th><th scope="col">Sigla</th><th scope="col">Máscara vigente</th><th scope="col">Disponibilidade</th><th scope="col" class="text-end">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($tiposProcesso as $tipo): ?>
                        <tr>
                            <td><strong><?= esc($tipo['nome']) ?></strong><?php if ($tipo['descricao']): ?><div class="text-secondary text-break"><?= esc($tipo['descricao']) ?></div><?php endif ?></td>
                            <td><?= esc($tipo['sigla']) ?></td>
                            <td><?php if ($tipo['mascara']): ?><code><?= esc($tipo['mascara']) ?></code><?php else: ?><span class="text-secondary">Sem regra vigente</span><?php endif ?></td>
                            <td><span class="badge <?= $tipo['ativo'] ? 'bg-green-lt' : 'bg-secondary-lt' ?>"><?= $tipo['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="controle-configuracoes" href="<?= esc($enderecoTipo(['tipo' => $tipo['id']]), 'attr') ?>">Editar<span class="visualmente-oculto"> <?= esc($tipo['nome']) ?></span></a>
                                    <?php if (!$tipo['em_uso']): ?>
                                        <a class="controle-configuracoes" href="<?= esc($enderecoTipo(['excluir_tipo' => $tipo['id']]), 'attr') ?>">Excluir<span class="visualmente-oculto"> <?= esc($tipo['nome']) ?></span></a>
                                    <?php endif ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
