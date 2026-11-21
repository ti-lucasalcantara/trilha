<?php
use App\Libraries\Organizacao\EnvioDocumento;

$setorEnvio = $contextoTrabalho['setor'];
$envioDisponivel = $setorEnvio && $tiposDocumento && (!$simulacaoSemVinculo || $autoresDocumento);
?>
<dialog class="dialogo-acao dialogo-envio-documento" id="dialogo-adicionar-documento" aria-labelledby="titulo-adicionar-documento">
    <h2 id="titulo-adicionar-documento">Adicionar documento</h2>
    <div class="opcoes-novo-documento">
        <button class="opcao-novo-documento" type="button" id="escolher-envio-documento">
            <i class="fa-solid fa-cloud-arrow-up" aria-hidden="true"></i>
            <strong>Enviar do computador</strong>
            <span>Inclua um arquivo que você já tem.</span>
        </button>
        <?php if ($setorEnvio): ?>
        <a class="opcao-novo-documento text-decoration-none" href="<?= esc($enderecoProcesso . '/editor-documento', 'attr') ?>">
            <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i>
            <strong>Criar no Trilha</strong>
            <span>Escreva um documento e salve no processo.</span>
        </a>
        <?php endif ?>
    </div>
    <footer class="rodape-acao justify-content-end"><button type="button" class="btn btn-outline-secondary" data-cancelar-dialogo>Cancelar</button></footer>
</dialog>
<dialog class="dialogo-acao dialogo-envio-documento" id="dialogo-enviar-documento" aria-labelledby="titulo-enviar-documento">
    <h2 id="titulo-enviar-documento">Enviar documento</h2>
    <?php if (!$envioDisponivel): ?>
        <p class="alert alert-warning" role="status"><?= !$setorEnvio ? 'Selecione um setor ativo no cabeçalho.' : (!$tiposDocumento ? 'Cadastre ou ative um tipo de documento em Configurações para liberar o envio.' : 'Vincule um usuário interno ativo para registrar o envio.') ?></p>
    <?php endif ?>
    <form id="form-enviar-documento" method="post" enctype="multipart/form-data" action="<?= esc($enderecoProcesso . '/documentos', 'attr') ?>">
        <?= csrf_field() ?>
        <input type="hidden" name="setor_contexto" value="<?= esc($setorEnvio['id'] ?? '', 'attr') ?>">
        <label class="form-label" for="tipo-envio-documento">Tipo de documento</label>
        <select class="form-select mb-3" id="tipo-envio-documento" name="tipo_documento" required <?= !$envioDisponivel ? 'disabled' : '' ?>>
            <option value="">Selecione o tipo</option>
            <?php foreach ($tiposDocumento as $tipo): ?>
                <option value="<?= esc((string) $tipo['id'], 'attr') ?>"><?= esc($tipo['nome']) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($simulacaoSemVinculo): ?>
            <label class="form-label" for="autor-envio-documento">Registrar em nome de</label>
            <select class="form-select mb-3" id="autor-envio-documento" name="responsavel" required <?= !$envioDisponivel ? 'disabled' : '' ?>>
                <option value="">Selecione um usuário interno</option>
                <?php foreach ($autoresDocumento as $autor): ?>
                    <option value="<?= esc((string) $autor['vinculo_id'], 'attr') ?>"><?= esc($autor['nome']) ?></option>
                <?php endforeach ?>
            </select>
        <?php endif ?>
        <label class="area-envio-documento" id="area-envio-documento">
            <input class="visualmente-oculto" type="file" name="documento" accept="<?= esc(EnvioDocumento::FORMATOS, 'attr') ?>"
                data-limite="<?= EnvioDocumento::LIMITE_BYTES ?>" required aria-describedby="limite-envio-documento" <?= !$envioDisponivel ? 'disabled' : '' ?>>
            <i class="fa-solid fa-file-arrow-up" aria-hidden="true"></i>
            <strong>Arraste o arquivo ou clique para selecionar</strong>
            <span id="limite-envio-documento">Envie seu arquivo com no máximo 10 MB.</span>
            <span id="arquivo-envio-documento" role="status"></span>
        </label>
        <div class="mt-3">
            <label class="form-label" for="titulo-envio-documento">Nome de exibição <span class="text-secondary fw-normal">(opcional)</span></label>
            <input class="form-control" id="titulo-envio-documento" name="titulo_documento" maxlength="500"
                placeholder="Usar o nome do arquivo" <?= !$envioDisponivel ? 'disabled' : '' ?>>
        </div>
        <footer class="rodape-acao justify-content-end">
            <button type="button" class="btn btn-outline-secondary" id="voltar-adicionar-documento">Voltar</button>
            <button type="submit" class="btn btn-primary" <?= !$envioDisponivel ? 'disabled' : '' ?>>Enviar documento</button>
        </footer>
    </form>
</dialog>
