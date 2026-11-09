<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<?php
// Define os campos de cada cadastro e conserva valores válidos após erros.
$enderecoLista = site_url('admin/' . $cadastro);
$enderecoSalvar = $id === null ? $enderecoLista : site_url('admin/' . $cadastro . '/' . $id);
$definicoes = [
    'nome' => ['Nome', 'text', $cadastro === 'planos' ? 150 : 200, true],
] + match ($cadastro) {
    'clientes' => [
        'nome_fantasia' => ['Nome fantasia', 'text', 200, false],
        'pais' => ['País (código de duas letras)', 'text', 2, true],
        'idioma' => ['Idioma', 'text', 20, true],
        'fuso_horario' => ['Fuso horário', 'text', 80, true],
    ],
    'planos' => [
        'preco' => ['Preço', 'text', 20, true],
        'armazenamento_gb' => ['Armazenamento (GB)', 'number', null, true],
        'usuarios' => ['Limite de usuários', 'number', null, true],
    ],
    'usuarios' => [
        'email' => ['E-mail', 'email', 254, true],
        'idioma' => ['Idioma', 'text', 20, false],
        'senha' => [$id === null ? 'Senha inicial' : 'Nova senha (opcional)', 'password', 72, $id === null],
    ],
};
?>
<a class="botao-admin secundario voltar-cadastro-admin" href="<?= esc($enderecoLista, 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Voltar à lista</a>

<?php if ($erros): ?>
    <div class="mensagem-admin erro" role="alert">
        <strong>Confira os dados antes de salvar.</strong>
        <ul><?php foreach ($erros as $erro): ?><li><?= esc($erro) ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>

<form class="cartao-admin formulario-cadastro-admin" method="post" action="<?= esc($enderecoSalvar, 'attr') ?>">
    <?= csrf_field() ?>
    <header><h2>Dados do cadastro</h2>
        <?php if ($cadastro === 'planos'): ?><p>Novos planos são mensais em BRL. Alterações de preço ou limite geram uma nova versão.</p><?php endif ?>
    </header>
    <div class="campos-cadastro-admin">
        <?php foreach ($definicoes as $campo => [$rotulo, $tipo, $limite, $obrigatorio]): ?>
            <label for="campo-admin-<?= esc($campo, 'attr') ?>">
                <span><?= esc($rotulo) ?></span>
                <input id="campo-admin-<?= esc($campo, 'attr') ?>" type="<?= esc($tipo, 'attr') ?>" name="<?= esc($campo, 'attr') ?>"
                    value="<?= esc((string) ($campos[$campo] ?? ''), 'attr') ?>"
                    <?= $limite ? 'maxlength="' . $limite . '"' : '' ?>
                    <?= $obrigatorio ? 'required' : '' ?>
                    <?= $tipo === 'number' ? 'min="0" max="2147483647" step="1"' : '' ?>
                    <?= $campo === 'preco' ? 'inputmode="decimal"' : '' ?>
                    <?= $tipo === 'password' ? 'autocomplete="new-password" minlength="8"' : '' ?>>
            </label>
        <?php endforeach ?>
        <label for="situacao-cadastro-admin"><span>Situação</span>
            <select id="situacao-cadastro-admin" name="status" required>
                <?php foreach (['ativo' => 'Ativo', 'inativo' => 'Inativo'] as $valor => $rotulo): ?>
                    <option value="<?= esc($valor, 'attr') ?>" <?= ($campos['status'] ?? '') === $valor ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                <?php endforeach ?>
            </select>
        </label>

        <?php if ($cadastro === 'clientes'): ?>
            <label for="plano-cadastro-admin"><span>Plano contratado</span>
                <select id="plano-cadastro-admin" name="plano_versao_id">
                    <option value="">Sem plano</option>
                    <?php $opcoesPlanos = array_filter($planos, static fn ($plano) => $plano['status'] === 'ativo' && $plano['plano_versao_id'] !== null); ?>
                    <?php if ($planoVinculado && !in_array((string) $planoVinculado, array_map('strval', array_column($opcoesPlanos, 'plano_versao_id')), true)): ?>
                        <option value="<?= esc((string) $planoVinculado, 'attr') ?>" <?= (string) ($campos['plano_versao_id'] ?? '') === (string) $planoVinculado ? 'selected' : '' ?>>Manter versão contratada</option>
                    <?php endif ?>
                    <?php foreach ($opcoesPlanos as $plano): ?>
                        <option value="<?= esc((string) $plano['plano_versao_id'], 'attr') ?>" <?= (string) ($campos['plano_versao_id'] ?? '') === (string) $plano['plano_versao_id'] ? 'selected' : '' ?>><?= esc($plano['nome']) ?></option>
                    <?php endforeach ?>
                </select>
            </label>
        <?php elseif ($cadastro === 'usuarios'): ?>
            <fieldset class="vinculos-cadastro-admin">
                <legend>Organizações vinculadas</legend>
                <?php if (!$organizacoes): ?><p>Cadastre um cliente para associar o usuário a uma organização.</p><?php endif ?>
                <?php foreach ($organizacoes as $organizacao): ?>
                    <label><input type="checkbox" name="organizacoes[]" value="<?= esc((string) $organizacao['id'], 'attr') ?>" <?= in_array((string) $organizacao['id'], $campos['organizacoes'] ?? [], true) ? 'checked' : '' ?>><span><?= esc($organizacao['nome']) ?></span></label>
                <?php endforeach ?>
            </fieldset>
        <?php endif ?>
    </div>
    <footer class="rodape-cadastro-admin">
        <a class="botao-admin secundario" href="<?= esc($enderecoLista, 'attr') ?>">Cancelar</a>
        <button type="submit" class="botao-admin principal"><i class="fa-regular fa-floppy-disk" aria-hidden="true"></i> Salvar <?= esc($singular) ?></button>
    </footer>
</form>
<?= $this->endSection() ?>
