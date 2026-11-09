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
        'pais' => ['País', 'select', null, true],
        'idioma' => ['Idioma', 'select', null, true],
        'fuso_horario' => ['Fuso horário', 'select', null, true],
    ],
    'planos' => [
        'preco' => ['Preço', 'text', 20, true],
        'armazenamento_gb' => ['Armazenamento (GB)', 'number', null, true],
        'usuarios' => ['Limite de usuários', 'number', null, true],
    ],
    'usuarios' => [
        'email' => ['E-mail', 'email', 254, true],
        'idioma' => ['Idioma', 'select', null, false],
        'senha' => [$id === null ? 'Senha inicial' : 'Nova senha (opcional)', 'password', 72, $id === null],
    ],
};
$opcoesLocalizacao = [
    'pais' => ['BR' => 'Brasil', 'PT' => 'Portugal', 'CL' => 'Chile'],
    'idioma' => ['pt-BR' => 'Português (Brasil)', 'pt-PT' => 'Português (Portugal)', 'es-CL' => 'Espanhol (Chile)'],
];
if ($cadastro === 'clientes') {
    $fusos = array_unique(array_merge(['America/Sao_Paulo', 'Europe/Lisbon', 'America/Santiago', 'UTC'], DateTimeZone::listIdentifiers()));
    $opcoesLocalizacao['fuso_horario'] = array_combine($fusos, array_map(static fn ($fuso) => str_replace('_', ' ', $fuso), $fusos));
}
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
            <?php $valorAtual = (string) ($campos[$campo] ?? ''); ?>
            <div class="campo-cadastro-admin">
                <label for="campo-admin-<?= esc($campo, 'attr') ?>"><?= esc($rotulo) ?></label>
                <?php if ($tipo === 'select'): ?>
                    <?php
                    // Mantém valores históricos disponíveis ao editar um cadastro.
                    $opcoes = $opcoesLocalizacao[$campo];
                    if ($valorAtual !== '' && !isset($opcoes[$valorAtual])) $opcoes[$valorAtual] = $valorAtual;
                    ?>
                    <select id="campo-admin-<?= esc($campo, 'attr') ?>" name="<?= esc($campo, 'attr') ?>" <?= $obrigatorio ? 'required' : '' ?>>
                        <option value=""><?= $obrigatorio ? 'Selecione' : 'Usar o padrão' ?></option>
                        <?php foreach ($opcoes as $valor => $nomeOpcao): ?>
                            <option value="<?= esc($valor, 'attr') ?>" <?= $valor === $valorAtual ? 'selected' : '' ?>><?= esc($nomeOpcao) ?></option>
                        <?php endforeach ?>
                    </select>
                <?php else: ?>
                    <?php if ($tipo === 'password'): ?><div class="controle-senha-admin"><?php endif ?>
                <input id="campo-admin-<?= esc($campo, 'attr') ?>" type="<?= esc($tipo, 'attr') ?>" name="<?= esc($campo, 'attr') ?>"
                    value="<?= esc($tipo === 'password' ? '' : $valorAtual, 'attr') ?>"
                    <?= $limite ? 'maxlength="' . $limite . '"' : '' ?>
                    <?= $obrigatorio ? 'required' : '' ?>
                    <?= $tipo === 'number' ? 'min="0" max="2147483647" step="1"' : '' ?>
                    <?= $campo === 'preco' ? 'inputmode="decimal"' : '' ?>
                    <?= $tipo === 'password' ? 'autocomplete="new-password" minlength="8"' : '' ?>>
                    <?php if ($tipo === 'password'): ?>
                        <button type="button" class="botao-admin secundario" id="alternar-senha-admin" aria-controls="campo-admin-senha" aria-label="Mostrar senha" title="Mostrar senha"><i class="fa-regular fa-eye" aria-hidden="true"></i></button>
                        <button type="button" class="botao-admin secundario" id="gerar-senha-admin"><i class="fa-solid fa-key" aria-hidden="true"></i> Gerar senha</button>
                    </div>
                    <small id="aviso-senha-admin" role="status"></small>
                    <?php endif ?>
                <?php endif ?>
            </div>
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
                    <?php
                    $empresaId = (string) $organizacao['id'];
                    $perfilAtual = (string) ($campos['perfis'][$empresaId] ?? '');
                    $protegido = $perfilAtual !== '' && $perfilAtual === (string) $organizacao['perfil_protegido'];
                    ?>
                    <div class="vinculo-perfil-admin">
                        <label><input type="checkbox" name="organizacoes[]" value="<?= esc($empresaId, 'attr') ?>" <?= in_array($empresaId, $campos['organizacoes'] ?? [], true) ? 'checked' : '' ?> <?= $protegido ? 'disabled' : '' ?>><span><?= esc($organizacao['nome']) ?></span></label>
                        <?php if ($protegido): ?>
                            <input type="hidden" name="organizacoes[]" value="<?= esc($empresaId, 'attr') ?>">
                            <input type="hidden" name="perfis[<?= esc($empresaId, 'attr') ?>]" value="<?= esc($perfilAtual, 'attr') ?>">
                            <span>Administrador fundador · Protegido</span>
                        <?php elseif (!$organizacao['perfis']): ?>
                            <span>Sem perfis · O vínculo criará o Administrador fundador.</span>
                        <?php else: ?>
                            <label for="perfil-empresa-<?= esc($empresaId, 'attr') ?>">Perfil</label>
                            <select id="perfil-empresa-<?= esc($empresaId, 'attr') ?>" name="perfis[<?= esc($empresaId, 'attr') ?>]">
                                <option value="">Selecione um perfil</option>
                                <?php foreach ($organizacao['perfis'] as $perfil): ?>
                                    <?php if ($perfil['status'] !== 'ativo' || (string) $perfil['id'] === (string) $organizacao['perfil_protegido']) continue; ?>
                                    <option value="<?= esc((string) $perfil['id'], 'attr') ?>" <?= (string) $perfil['id'] === $perfilAtual ? 'selected' : '' ?>><?= esc($perfil['nome']) ?></option>
                                <?php endforeach ?>
                            </select>
                        <?php endif ?>
                    </div>
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
