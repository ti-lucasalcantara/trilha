<?php
// Mantém uma escolha de setor por unidade; no demo a seleção fica no navegador.
$demonstrativo = !isset($contextoTrabalho);
if ($demonstrativo) {
    $estruturaExemplo = $unidades ?? [
        'Matriz' => array_fill_keys(['Administrativo', 'Financeiro', 'Jurídico', 'Recursos Humanos', 'Tecnologia'], true),
        'Filial Centro' => array_fill_keys(['Operações', 'Financeiro', 'Administrativo'], true),
    ];
    $opcoesUnidades = [];
    foreach ($estruturaExemplo as $unidade => $setores) {
        $opcoesUnidades[] = ['id' => (string) $unidade, 'nome' => $unidade,
            'setores' => array_map(static fn ($setor) => ['id' => (string) $setor, 'nome' => $setor], array_keys($setores))];
    }
    $setorAtual = null;
} else {
    $opcoesUnidades = $contextoTrabalho['unidades'];
    $setorAtual = $contextoTrabalho['setor'];
}
?>
<?php if (!$demonstrativo && ($erroContexto = session()->getFlashdata('erroContextoTrabalho'))): ?>
    <p class="aviso-contexto-trabalho" role="alert"><?= esc($erroContexto) ?></p>
<?php endif ?>
<form class="seletor-trabalho-cabecalho" id="seletor-trabalho-cabecalho" data-demonstrativo="<?= $demonstrativo ? 'true' : 'false' ?>"
    <?php if (!$demonstrativo): ?> method="post" action="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/contexto-trabalho'), 'attr') ?>"<?php endif ?>>
    <?php if (!$demonstrativo): ?>
        <?= csrf_field() ?>
        <input type="hidden" name="pagina" value="<?= esc($paginaAtiva, 'attr') ?>">
    <?php endif ?>
    <button type="button" class="nome-contexto-trabalho" id="editar-unidade-trabalho" aria-controls="unidade-trabalho" aria-expanded="false" hidden></button>
    <label for="unidade-trabalho" class="campo-contexto-trabalho"><span class="visualmente-oculto">Unidade</span>
        <select id="unidade-trabalho" name="unidade" <?= !$opcoesUnidades ? 'disabled' : '' ?>>
            <?php if (!$opcoesUnidades): ?><option value="">Sem unidade</option><?php endif ?>
            <?php foreach ($opcoesUnidades as $unidade): ?>
                <option value="<?= esc($unidade['id'], 'attr') ?>" <?= ($setorAtual['unidade'] ?? '') === $unidade['id'] ? 'selected' : '' ?>><?= esc($unidade['nome']) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <button type="button" class="nome-contexto-trabalho" id="editar-setor-trabalho" aria-controls="setor-trabalho" aria-expanded="false" hidden></button>
    <label for="setor-trabalho" class="campo-contexto-trabalho"><span class="visualmente-oculto">Setor</span>
        <select id="setor-trabalho" name="setor" <?= !$opcoesUnidades ? 'disabled' : '' ?>>
            <?php if (!$opcoesUnidades): ?><option value="">Sem setor</option><?php endif ?>
            <?php foreach ($opcoesUnidades as $unidade): ?>
                <?php foreach ($unidade['setores'] as $setor): ?>
                    <option data-unidade="<?= esc($unidade['id'], 'attr') ?>" value="<?= esc($setor['id'], 'attr') ?>" <?= ($setorAtual['id'] ?? '') === $setor['id'] ? 'selected' : '' ?>><?= esc($setor['nome'] . (!empty($setor['sigla']) ? ' - ' . $setor['sigla'] : '')) ?></option>
                <?php endforeach ?>
            <?php endforeach ?>
        </select>
    </label>
    <?php if (!$demonstrativo && $opcoesUnidades): ?><noscript><button type="submit" class="btn">Aplicar</button></noscript><?php endif ?>
</form>
<dialog id="confirmar-contexto-trabalho" class="dialogo-contexto-trabalho" aria-labelledby="titulo-confirmar-contexto" aria-describedby="descricao-confirmar-contexto">
    <h2 id="titulo-confirmar-contexto"></h2>
    <p id="descricao-confirmar-contexto"></p>
    <form method="dialog">
        <button type="submit" value="cancelar" class="btn" autofocus>Cancelar</button>
        <button type="submit" value="confirmar" class="btn btn-primary">Confirmar troca</button>
    </form>
</dialog>
