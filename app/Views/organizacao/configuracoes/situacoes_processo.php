<?php
// A consulta define qual formulário aparece dentro da aba.
$baseSituacoes = site_url('organizacao/' . $identificadorOrganizacao);
$abaSituacoes = $baseSituacoes . '/configuracoes#situacoes-processo';
$enderecoSituacao = static fn (array $consulta) => $baseSituacoes . '/configuracoes?' . http_build_query($consulta) . '#situacoes-processo';
$porId = array_column($situacoesProcesso, null, 'id');
$novaSituacao = $edicaoSituacaoProcesso === 'nova';
$situacaoEditada = is_string($edicaoSituacaoProcesso) ? ($porId[$edicaoSituacaoProcesso] ?? null) : null;
$situacaoExcluida = is_string($exclusaoSituacaoProcesso) ? ($porId[$exclusaoSituacaoProcesso] ?? null) : null;
$dadosSituacao = $situacaoEditada ?? ['id' => '', 'nome' => '', 'descricao' => '', 'cor' => '', 'ordem' => 0, 'ativo' => 1];
$dadosAnteriores = session()->getFlashdata('dadosSituacaoProcesso');
if (is_array($dadosAnteriores) && (string) $dadosSituacao['id'] === $dadosAnteriores['id']) {
    $dadosSituacao = array_replace($dadosSituacao, $dadosAnteriores);
}
// O seletor nativo precisa de uma cor hexadecimal e usa o roxo do Trilha como padrão.
$corSituacao = preg_match('/^#[0-9a-f]{6}$/iD', $dadosSituacao['cor'] ?? '') ? $dadosSituacao['cor'] : '#7c3aed';
?>
<section id="situacoes-processo" class="cartao-configuracoes" aria-labelledby="titulo-situacoes-processo" hidden>
    <header>
        <div>
            <h2 id="titulo-situacoes-processo">Situações de processo <span class="contagem-configuracoes"><?= count($situacoesProcesso) ?></span></h2>
            <p>Defina as situações e a ordem em que aparecem na abertura de processos.</p>
        </div>
        <?php if (!$novaSituacao && !$situacaoEditada): ?>
            <a class="btn btn-primary" href="<?= esc($enderecoSituacao(['situacao' => 'nova']), 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Nova situação</a>
        <?php endif ?>
    </header>

    <?php if ($mensagem = session()->getFlashdata('erroSituacaoProcesso')): ?>
        <p class="alert alert-danger mx-3" role="alert"><?= esc($mensagem) ?></p>
    <?php elseif ($mensagem = session()->getFlashdata('sucessoSituacaoProcesso')): ?>
        <p class="alert alert-success mx-3" role="status"><?= esc($mensagem) ?></p>
    <?php endif ?>

    <?php if ($novaSituacao || $situacaoEditada): ?>
        <form class="border-top p-3 p-md-4" method="post" action="<?= esc($baseSituacoes . '/situacoes-processo', 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc((string) $dadosSituacao['id'], 'attr') ?>">
            <h3 class="h4 mb-3"><?= $novaSituacao ? 'Nova situação de processo' : 'Editar situação de processo' ?></h3>
            <div class="row g-3">
                <div class="col-12">
                    <label class="form-label" for="nome-situacao-processo">Nome</label>
                    <input class="form-control" id="nome-situacao-processo" name="nome" maxlength="100" value="<?= esc($dadosSituacao['nome'], 'attr') ?>" placeholder="Ex.: Em análise" required>
                </div>
                <div class="col-12">
                    <label class="form-label" for="descricao-situacao-processo">Descrição <span class="text-secondary fw-normal">(opcional)</span></label>
                    <textarea class="form-control" id="descricao-situacao-processo" name="descricao" rows="3"><?= esc($dadosSituacao['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="cor-situacao-processo">Cor</label>
                    <input class="form-control form-control-color" id="cor-situacao-processo" name="cor" type="color" value="<?= esc($corSituacao, 'attr') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="ordem-situacao-processo">Ordem</label>
                    <input class="form-control" id="ordem-situacao-processo" name="ordem" type="number" step="1" value="<?= esc((string) $dadosSituacao['ordem'], 'attr') ?>" aria-describedby="ajuda-ordem-situacao" required>
                    <p class="form-hint" id="ajuda-ordem-situacao">Números menores aparecem primeiro.</p>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="ativo-situacao-processo">Disponibilidade</label>
                    <select class="form-select" id="ativo-situacao-processo" name="ativo">
                        <option value="1" <?= $dadosSituacao['ativo'] ? 'selected' : '' ?>>Ativa</option>
                        <option value="0" <?= !$dadosSituacao['ativo'] ? 'selected' : '' ?>>Inativa</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end flex-wrap gap-2 mt-3">
                <a class="btn btn-outline-secondary" href="<?= esc($abaSituacoes, 'attr') ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit">Salvar situação</button>
            </div>
        </form>
    <?php elseif ($situacaoExcluida && !$situacaoExcluida['em_uso']): ?>
        <form class="alert alert-warning mx-3" method="post" action="<?= esc($baseSituacoes . '/situacoes-processo/' . $situacaoExcluida['id'] . '/excluir', 'attr') ?>">
            <?= csrf_field() ?>
            <p>Confirma a exclusão de <strong><?= esc($situacaoExcluida['nome']) ?></strong>?</p>
            <div class="d-flex flex-wrap gap-2 mt-2">
                <a class="btn btn-outline-secondary" href="<?= esc($abaSituacoes, 'attr') ?>">Cancelar</a>
                <button class="btn btn-danger" type="submit">Excluir situação</button>
            </div>
        </form>
    <?php elseif (!$situacoesProcesso): ?>
        <p class="vazio-configuracoes">Nenhuma situação de processo cadastrada.</p>
    <?php endif ?>

    <?php if ($situacoesProcesso && !$novaSituacao && !$situacaoEditada): ?>
        <div class="table-responsive">
            <table class="table table-vcenter card-table mb-0">
                <thead><tr><th scope="col">Situação</th><th scope="col">Ordem</th><th scope="col">Disponibilidade</th><th scope="col" class="text-end">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($situacoesProcesso as $situacao): ?>
                        <tr>
                            <td>
                                <?php if (preg_match('/^#[0-9a-f]{6}$/iD', $situacao['cor'] ?? '')): ?>
                                    <span class="badge badge-dot me-1" style="background-color:<?= esc($situacao['cor'], 'attr') ?>" aria-hidden="true"></span>
                                <?php endif ?>
                                <strong><?= esc($situacao['nome']) ?></strong>
                                <?php if ($situacao['descricao']): ?><div class="text-secondary text-break"><?= esc($situacao['descricao']) ?></div><?php endif ?>
                            </td>
                            <td><?= esc((string) $situacao['ordem']) ?></td>
                            <td><span class="badge <?= $situacao['ativo'] ? 'bg-green-lt' : 'bg-secondary-lt' ?>"><?= $situacao['ativo'] ? 'Ativa' : 'Inativa' ?></span></td>
                            <td>
                                <div class="d-flex justify-content-end gap-2">
                                    <a class="controle-configuracoes" href="<?= esc($enderecoSituacao(['situacao' => $situacao['id']]), 'attr') ?>">Editar<span class="visualmente-oculto"> <?= esc($situacao['nome']) ?></span></a>
                                    <?php if (!$situacao['em_uso']): ?>
                                        <a class="controle-configuracoes" href="<?= esc($enderecoSituacao(['excluir_situacao' => $situacao['id']]), 'attr') ?>">Excluir<span class="visualmente-oculto"> <?= esc($situacao['nome']) ?></span></a>
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
