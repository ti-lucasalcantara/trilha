<?= $this->extend('layout/principal') ?>

<?= $this->section('conteudo') ?>
<?php
// A origem é a seleção vigente no cabeçalho e será conferida novamente ao gravar.
$endereco = site_url('organizacao/' . $identificadorOrganizacao . '/processos');
$setor = $contextoTrabalho['setor'];
$unidade = $setor ? array_column($contextoTrabalho['unidades'], 'nome', 'id')[$setor['unidade']] : '';
$pendencias = [];
if (!$setor) $pendencias[] = 'Selecione um setor ativo no cabeçalho.';
if (!$tipos) $pendencias[] = 'A organização precisa de um tipo de processo ativo.';
if (!$situacoes) $pendencias[] = 'A organização precisa de uma situação de processo ativa.';
if ($modoSimulacao && !$responsaveis) $pendencias[] = 'Vincule um usuário interno ativo à organização para registrar a abertura em simulação.';
?>
<nav class="caminho-pagina mb-3" aria-label="Localização">
    <a href="<?= esc($endereco, 'attr') ?>">Processos</a>
    <span aria-hidden="true">/</span>
    <span aria-current="page">Novo processo</span>
</nav>
<header class="titulo-acoes">
    <div>
        <h1>Novo processo</h1>
        <p class="descricao-pagina">Preencha os dados de abertura. O número oficial será gerado ao salvar.</p>
    </div>
    <a class="btn btn-outline-secondary" href="<?= esc($endereco, 'attr') ?>"><i class="fa-solid fa-arrow-left me-2" aria-hidden="true"></i>Voltar</a>
</header>

<?php if ($erro = session()->getFlashdata('erroAbertura')): ?>
    <p class="alert alert-danger" role="alert"><?= esc($erro) ?></p>
<?php endif ?>
<?php if ($pendencias): ?>
    <div class="alert alert-warning" role="status">
        <strong>Antes de abrir um processo</strong>
        <ul class="mb-0 mt-2"><?php foreach ($pendencias as $pendencia): ?><li><?= esc($pendencia) ?></li><?php endforeach ?></ul>
    </div>
<?php endif ?>

<form class="card" method="post" action="<?= esc($endereco . '/novo', 'attr') ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="setor_contexto" value="<?= esc($setor['id'] ?? '', 'attr') ?>">
    <div class="card-body">
        <h2 class="card-title">Dados de abertura</h2>
        <p class="text-secondary mb-3">Tipo, situação inicial e assunto são obrigatórios.</p>
        <div class="row g-3">
            <div class="col-12 col-md-6">
                <label class="form-label" for="tipo-processo">Tipo de processo</label>
                <select class="form-select" id="tipo-processo" name="tipo" required>
                    <option value="">Selecione o tipo</option>
                    <?php foreach ($tipos as $tipo): ?>
                        <option value="<?= esc((string) $tipo['id'], 'attr') ?>" <?= old('tipo', '', false) === (string) $tipo['id'] ? 'selected' : '' ?>><?= esc($tipo['nome']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label" for="situacao-processo">Situação inicial</label>
                <select class="form-select" id="situacao-processo" name="situacao" required>
                    <option value="">Selecione a situação</option>
                    <?php foreach ($situacoes as $situacao): ?>
                        <option value="<?= esc((string) $situacao['id'], 'attr') ?>" <?= old('situacao', '', false) === (string) $situacao['id'] ? 'selected' : '' ?>><?= esc($situacao['nome']) ?></option>
                    <?php endforeach ?>
                </select>
            </div>
            <div class="col-12">
                <label class="form-label" for="assunto-processo">Assunto</label>
                <input class="form-control" id="assunto-processo" name="assunto" maxlength="500" placeholder="Ex.: Aquisição de equipamentos para a equipe" value="<?= old('assunto', '', 'attr') ?>" required>
            </div>
            <div class="col-12">
                <label class="form-label" for="descricao-processo">Descrição <span class="text-secondary fw-normal">(opcional)</span></label>
                <textarea class="form-control" id="descricao-processo" name="descricao" rows="4" placeholder="Inclua informações que ajudem a entender a abertura."><?= old('descricao') ?></textarea>
            </div>
        </div>
    </div>
    <div class="card-body border-top">
        <h2 class="card-title">Origem e acesso</h2>
        <dl class="row gy-2 mb-0">
            <div class="col-12 col-md-4"><dt>Unidade</dt><dd><?= esc($unidade ?: 'Aguardando seleção') ?></dd></div>
            <div class="col-12 col-md-4"><dt>Setor</dt><dd><?= esc($setor ? $setor['nome'] . ($setor['sigla'] ? ' - ' . $setor['sigla'] : '') : 'Aguardando seleção') ?></dd></div>
            <div class="col-12 col-md-4"><dt>Nível de acesso</dt><dd>Interno</dd></div>
        </dl>
        <?php if ($modoSimulacao): ?>
            <label class="form-label mt-3" for="responsavel-processo">Registrar em nome de</label>
            <select class="form-select" id="responsavel-processo" name="responsavel" required>
                <option value="">Selecione um usuário interno</option>
                <?php foreach ($responsaveis as $usuario): ?>
                    <option value="<?= esc((string) $usuario['vinculo_id'], 'attr') ?>" <?= old('responsavel', '', false) === (string) $usuario['vinculo_id'] ? 'selected' : '' ?>><?= esc($usuario['nome'] . ' · ' . $usuario['email']) ?></option>
                <?php endforeach ?>
            </select>
        <?php endif ?>
    </div>
    <div class="card-footer d-flex justify-content-end flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="<?= esc($endereco, 'attr') ?>">Cancelar</a>
        <button type="submit" class="btn btn-primary" <?= $pendencias ? 'disabled' : '' ?>><i class="fa-solid fa-plus" aria-hidden="true"></i>Abrir processo</button>
    </div>
</form>
<?= $this->endSection() ?>
