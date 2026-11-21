<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/vendor/select2/select2.min.css'), 'attr') ?>">
<link rel="stylesheet" href="<?= esc(base_url('assets/vendor/quill/quill.snow.css'), 'attr') ?>">
<link rel="stylesheet" href="<?= esc(base_url('assets/css/novo-processo.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/vendor/jquery/jquery.min.js'), 'attr') ?>" defer></script>
<script src="<?= esc(base_url('assets/vendor/select2/select2.min.js'), 'attr') ?>" defer></script>
<script src="<?= esc(base_url('assets/vendor/select2/pt-BR.js'), 'attr') ?>" defer></script>
<script src="<?= esc(base_url('assets/vendor/quill/quill.js'), 'attr') ?>" defer></script>
<script src="<?= esc(base_url('assets/js/novo-processo.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<?php
// A origem é a seleção vigente no cabeçalho e será conferida novamente ao gravar.
$endereco = site_url('organizacao/' . $identificadorOrganizacao . '/processos');
$editando = $edicao !== null;
$processoEdicao = $edicao['processo'] ?? [];
$acaoFormulario = $editando ? $endereco . '/' . $processoEdicao['hash_publico'] . '/editar' : $endereco . '/novo';
$acessoAnterior = old('acesso', null, false);
$nivelAcesso = $acessoAnterior ?? $edicao['acesso'] ?? 'interno';
$autorizados = array_map('strval', array_column($edicao['concessoes'] ?? [], 'destinatario'));
$setoresMarcados = $acessoAnterior !== null ? (array) old('setores_autorizados', [], false) : (($edicao['acesso'] ?? '') === 'restrito' ? $autorizados : []);
$pessoasMarcadas = $acessoAnterior !== null ? (array) old('pessoas_autorizadas', [], false) : (($edicao['acesso'] ?? '') === 'confidencial' ? $autorizados : []);
$setor = $contextoTrabalho['setor'];
$descricaoAnterior = old('descricao_formatada', $acessoAnterior === null ? ($processoEdicao['descricao_formatada'] ?? '') : '', false);
$descricaoFormatada = is_string($descricaoAnterior) ? \App\Libraries\Organizacao\DescricaoProcesso::limpar($descricaoAnterior) : '';
$pendencias = [];
if (!$setor) $pendencias[] = 'Selecione um setor ativo no cabeçalho.';
if (!$editando && !$tipos) $pendencias[] = 'A organização precisa de um tipo de processo ativo.';
if (!$editando && !$situacaoInicial) $pendencias[] = 'Peça à administração para definir a situação inicial em Configurações › Situações de processo.';
if ($simulacaoSemVinculo && !$responsaveis) $pendencias[] = 'Vincule um usuário interno ativo à organização para registrar o processo em simulação.';
?>
<nav class="caminho-pagina mb-3" aria-label="Localização">
    <a href="<?= esc($endereco, 'attr') ?>">Processos</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page"><?= esc($tituloPagina) ?></span>
</nav>
<header class="titulo-acoes">
    <div>
        <h1><?= esc($tituloPagina) ?></h1>
        <p class="descricao-pagina"><?= $editando ? esc($processoEdicao['numero']) : 'Preencha os dados de abertura. O número oficial será gerado ao salvar.' ?></p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= esc($endereco, 'attr') ?>"><i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Voltar</a>
</header>

<?php if ($erro = session()->getFlashdata('erroAbertura')): ?>
    <p class="alert alert-danger" role="alert"><?= esc($erro) ?></p>
<?php endif ?>
<?php if ($pendencias): ?>
    <div class="alert alert-warning" role="status">
        <strong><?= $editando ? 'Antes de salvar as alterações' : 'Antes de abrir um processo' ?></strong>
        <ul class="mb-0 mt-2"><?php foreach ($pendencias as $pendencia): ?><li><?= esc($pendencia) ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>

<form id="form-abertura-processo" class="card" method="post" action="<?= esc($acaoFormulario, 'attr') ?>" data-csrf="<?= esc(csrf_token(), 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="setor_contexto" value="<?= esc($setor['id'] ?? '', 'attr') ?>">
    <?php if ($editando): ?>
        <input type="hidden" name="revisao" value="<?= esc($edicao['revisao'], 'attr') ?>">
    <?php endif ?>
    <div class="card-body">
        <h2 class="card-title"><?= $editando ? 'Dados do processo' : 'Dados de abertura' ?></h2>
        <div class="row g-3">
            <div class="col-12 col-md-8">
                <label class="form-label" id="rotulo-tipo-processo" for="tipo-processo">Tipo de processo</label>
                <?php if ($editando): ?>
                    <input class="form-control" id="tipo-processo" value="<?= esc($processoEdicao['tipo'], 'attr') ?>" readonly>
                    <p class="form-hint mb-0">O tipo está vinculado à numeração oficial.</p>
                <?php else: ?>
                    <select class="form-select" id="tipo-processo" name="tipo" required>
                        <option value="">Selecione o tipo</option>
                        <?php foreach ($tipos as $tipo): ?>
                            <option value="<?= esc((string) $tipo['id'], 'attr') ?>" <?= old('tipo', '', false) === (string) $tipo['id'] ? 'selected' : '' ?>><?= esc($tipo['nome']) ?></option>
                        <?php endforeach ?>
                    </select>
                <?php endif ?>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label" for="acesso-processo">Nível de acesso</label>
                <div class="controle-acesso-processo">
                    <select class="form-select" id="acesso-processo" name="acesso" required>
                        <?php foreach ($niveisAcesso as $codigo => $rotulo): ?>
                            <option value="<?= esc($codigo, 'attr') ?>" <?= $nivelAcesso === $codigo ? 'selected' : '' ?>><?= esc($rotulo) ?></option>
                        <?php endforeach ?>
                    </select>
                    <details class="ajuda-acesso-processo">
                        <summary class="btn btn-outline-secondary btn-icon" aria-label="Explicação dos níveis de acesso">
                            <i class="fa-solid fa-question" aria-hidden="true"></i>
                        </summary>
                        <ul class="explicacoes-acesso-processo list-unstyled small mb-0">
                            <li><strong>Interno</strong> — toda a organização</li>
                            <li><strong>Restrito</strong> — setores específicos</li>
                            <li><strong>Confidencial</strong> — pessoas específicas</li>
                        </ul>
                    </details>
                </div>
            </div>
            <?php if ($simulacaoSemVinculo): ?>
                <div class="col-12">
                    <label class="form-label" for="responsavel-processo"><?= $editando ? 'Registrar edição em nome de' : 'Registrar em nome de' ?></label>
                    <select class="form-select" id="responsavel-processo" name="responsavel" required>
                        <option value="">Selecione um usuário interno</option>
                        <?php foreach ($responsaveis as $usuario): ?>
                            <option value="<?= esc((string) $usuario['vinculo_id'], 'attr') ?>" <?= old('responsavel', (string) ($processoEdicao['aberto_por'] ?? ''), false) === (string) $usuario['vinculo_id'] ? 'selected' : '' ?>><?= esc($usuario['nome'] . ' · ' . $usuario['email']) ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            <?php endif ?>
            <fieldset class="col-12" data-acesso-processo="restrito" hidden disabled>
                <legend class="form-label">Setores autorizados</legend>
                <div class="row g-2">
                    <?php foreach ($contextoTrabalho['unidades'] as $grupo): ?>
                        <div class="col-12 col-md-6">
                            <strong><?= esc($grupo['nome']) ?></strong>
                            <?php foreach ($grupo['setores'] as $opcao):
                                $setorAtivo = $opcao['id'] === ($setor['id'] ?? null);
                                $marcado = $setorAtivo || in_array($opcao['id'], $setoresMarcados, true);
                            ?>
                                <label class="form-check">
                                    <input class="form-check-input" type="checkbox" name="setores_autorizados[]" value="<?= esc($opcao['id'], 'attr') ?>" <?= $marcado ? 'checked' : '' ?> <?= $setorAtivo ? 'disabled' : '' ?>>
                                    <span class="form-check-label"><?= esc($opcao['nome']) ?><?= $setorAtivo ? ' (setor ativo)' : '' ?></span>
                                </label>
                            <?php endforeach ?>
                        </div>
                    <?php endforeach ?>
                </div>
            </fieldset>
            <fieldset class="col-12" data-acesso-processo="confidencial" hidden disabled>
                <legend class="form-label">Pessoas autorizadas</legend>
                <p class="form-hint mb-2"><?= $simulacaoSemVinculo ? 'A pessoa escolhida para registrar a operação é autorizada automaticamente.' : 'Seu acesso é incluído automaticamente.' ?></p>
                <?php foreach ($pessoasAcesso as $pessoa):
                    $vinculoPessoa = (string) $pessoa['vinculo_id'];
                    $usuarioLogado = $vinculoPessoa === $vinculoLogado;
                    $marcado = $usuarioLogado || in_array($vinculoPessoa, $pessoasMarcadas, true);
                ?>
                    <label class="form-check">
                        <input class="form-check-input" type="checkbox" name="pessoas_autorizadas[]" value="<?= esc($vinculoPessoa, 'attr') ?>" <?= $marcado ? 'checked' : '' ?> <?= $usuarioLogado ? 'disabled' : '' ?>>
                        <span class="form-check-label"><?= esc($pessoa['nome'] . ' · ' . $pessoa['email']) ?><?= $usuarioLogado ? ' (você)' : '' ?></span>
                    </label>
                <?php endforeach ?>
            </fieldset>
            <div class="col-12">
                <label class="form-label" for="assunto-processo">Assunto</label>
                <input class="form-control" id="assunto-processo" name="assunto" maxlength="500" placeholder="Informe o assunto" value="<?= old('assunto', $processoEdicao['assunto'] ?? '', 'attr') ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" id="rotulo-descricao-processo" for="descricao-processo">Descrição <span class="text-secondary fw-normal">(opcional)</span></label>
                <textarea class="form-control" id="descricao-processo" name="descricao" rows="4" placeholder="Inclua informações que ajudem a entender o processo."><?= old('descricao', $processoEdicao['descricao'] ?? '') ?></textarea>
                <input type="hidden" id="descricao-formatada-processo" name="descricao_formatada" value="<?= esc($descricaoFormatada, 'attr') ?>" disabled>
                <div id="editor-descricao-processo" class="editor-descricao-processo" hidden>
                    <div id="barra-descricao-processo" role="group" aria-label="Formatação da descrição">
                        <span class="ql-formats">
                            <button type="button" class="ql-bold" aria-label="Negrito" title="Negrito"></button>
                            <button type="button" class="ql-italic" aria-label="Itálico" title="Itálico"></button>
                            <button type="button" class="ql-underline" aria-label="Sublinhado" title="Sublinhado"></button>
                        </span>
                        <span class="ql-formats">
                            <button type="button" class="ql-list" value="bullet" aria-label="Lista com marcadores" title="Lista com marcadores"></button>
                            <button type="button" class="ql-list" value="ordered" aria-label="Lista numerada" title="Lista numerada"></button>
                        </span>
                        <button type="button" class="ql-clean" aria-label="Limpar formatação" title="Limpar formatação"></button>
                    </div>
                    <div id="conteudo-descricao-processo"></div>
                </div>
            </div>
        </div>
    </div>
    <div class="card-footer d-flex justify-content-end align-items-center flex-wrap gap-2">
        <?php if ($editando): ?>
            <p id="retorno-edicao-processo" class="me-auto mb-0 text-success" role="status"><?= esc($confirmacao) ?></p>
        <?php endif ?>
        <a class="btn btn-outline-secondary" href="<?= esc($endereco, 'attr') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary" <?= $pendencias ? 'disabled' : '' ?>><?= $editando ? 'Salvar alterações' : 'Abrir processo' ?></button>
    </div>
</form>
<?= $this->endSection() ?>
