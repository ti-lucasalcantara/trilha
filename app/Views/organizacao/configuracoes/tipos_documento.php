<?= $this->extend('organizacao/configuracoes') ?>

<?= $this->section('painelConfiguracoes') ?>
<?php $endereco = site_url('organizacao/' . $identificadorOrganizacao . '/configuracoes/tipos-documento'); ?>
<section class="cartao-configuracoes" aria-labelledby="titulo-tipos-documento">
    <header>
        <div>
            <h2 id="titulo-tipos-documento">Tipos de Documentos <span class="contagem-configuracoes"><?= count($tiposDocumento) ?></span></h2>
            <p>Gerencie os tipos que podem ser escolhidos ao enviar um documento para um processo.</p>
        </div>
        <?php if ($tipoDocumentoEditado === null): ?>
            <a class="btn btn-primary text-white ms-auto" href="<?= esc($endereco . '?tipo=novo', 'attr') ?>"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo tipo</a>
        <?php endif ?>
    </header>
    <?php if ($mensagem = session()->getFlashdata('erroTipoDocumento')): ?>
        <p class="alert alert-danger mx-3" role="alert"><?= esc($mensagem) ?></p>
    <?php elseif ($mensagem = session()->getFlashdata('sucessoTipoDocumento')): ?>
        <p class="alert alert-success mx-3" role="status"><?= esc($mensagem) ?></p>
    <?php endif ?>

    <?php if ($tipoDocumentoEditado !== null): ?>
        <form class="border-top p-3 p-md-4" method="post" action="<?= esc($endereco, 'attr') ?>">
            <?= csrf_field() ?>
            <input type="hidden" name="id" value="<?= esc((string) $tipoDocumentoEditado['id'], 'attr') ?>">
            <div class="row g-3">
                <div class="col-12 col-md-8">
                    <label class="form-label" for="nome-tipo-documento">Nome</label>
                    <input class="form-control" id="nome-tipo-documento" name="nome" maxlength="150" required value="<?= old('nome', $tipoDocumentoEditado['nome'], 'attr') ?>">
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="sigla-tipo-documento">Sigla</label>
                    <input class="form-control" id="sigla-tipo-documento" name="sigla" maxlength="40" required value="<?= old('sigla', $tipoDocumentoEditado['sigla'], 'attr') ?>">
                </div>
                <div class="col-12">
                    <label class="form-label" for="descricao-tipo-documento">Descrição <span class="text-secondary fw-normal">(opcional)</span></label>
                    <textarea class="form-control" id="descricao-tipo-documento" name="descricao" rows="3"><?= old('descricao', $tipoDocumentoEditado['descricao'] ?? '') ?></textarea>
                </div>
                <div class="col-12 col-md-4">
                    <label class="form-label" for="ativo-tipo-documento">Disponibilidade</label>
                    <?php $ativo = old('ativo', (string) $tipoDocumentoEditado['ativo'], false); ?>
                    <select class="form-select" id="ativo-tipo-documento" name="ativo">
                        <option value="1" <?= $ativo === '1' ? 'selected' : '' ?>>Ativo</option>
                        <option value="0" <?= $ativo === '0' ? 'selected' : '' ?>>Inativo</option>
                    </select>
                </div>
            </div>
            <div class="d-flex justify-content-end gap-2 mt-3">
                <a class="btn btn-outline-secondary" href="<?= esc($endereco, 'attr') ?>">Cancelar</a>
                <button class="btn btn-primary" type="submit">Salvar tipo</button>
            </div>
        </form>
    <?php elseif (!$tiposDocumento): ?>
        <p class="vazio-configuracoes">Nenhum tipo de documento cadastrado.</p>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-vcenter card-table mb-0">
                <thead><tr><th scope="col">Tipo</th><th scope="col">Sigla</th><th scope="col">Disponibilidade</th><th scope="col" class="text-end">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($tiposDocumento as $tipo): ?>
                        <tr>
                            <td><strong><?= esc($tipo['nome']) ?></strong>
                                <?php if ($tipo['descricao']): ?><div class="text-secondary text-break"><?= esc($tipo['descricao']) ?></div><?php endif ?>
                            </td>
                            <td><?= esc($tipo['sigla']) ?></td>
                            <td><span class="badge <?= $tipo['ativo'] ? 'bg-green-lt' : 'bg-secondary-lt' ?>"><?= $tipo['ativo'] ? 'Ativo' : 'Inativo' ?></span></td>
                            <td class="text-end"><a class="controle-configuracoes" href="<?= esc($endereco . '?tipo=' . $tipo['id'], 'attr') ?>">Editar<span class="visualmente-oculto"> <?= esc($tipo['nome']) ?></span></a></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
<?= $this->endSection() ?>
