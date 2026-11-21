<?= $this->extend('layout/principal') ?>

<?= $this->section('scripts') ?>
<?php if (!$modoSimulacao): ?>
    <script src="<?= esc(base_url('assets/js/perfil.js'), 'attr') ?>" defer></script>
<?php endif ?>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<header class="titulo-acoes">
    <h1>Meu Perfil</h1>
</header>
<section class="card" aria-label="Dados do perfil">
    <dl class="card-body row g-3 mb-0">
        <div class="col-12 col-md-4">
            <dt>Nome</dt>
            <dd class="mb-0"><?= esc($nomeUsuario) ?></dd>
        </div>
        <?php if ($emailUsuario !== null): ?>
            <div class="col-12 col-md-4">
                <dt>E-mail</dt>
                <dd class="mb-0 text-break"><?= esc($emailUsuario) ?></dd>
            </div>
        <?php endif ?>
        <div class="col-12 col-md-4">
            <dt>Organização</dt>
            <dd class="mb-0"><?= esc($nomeOrganizacao) ?></dd>
        </div>
    </dl>
    <div class="card-body border-top" id="foto-perfil">
        <div class="d-flex flex-wrap align-items-start gap-3">
            <span class="avatar-usuario avatar-perfil" aria-hidden="true">
                <?php if ($usuarioTemAvatar): ?>
                    <img src="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/meu-perfil/foto'), 'attr') ?>" alt="">
                <?php else: ?>
                    <?= esc(mb_strtoupper(mb_substr($nomeUsuario, 0, 1))) ?>
                <?php endif ?>
            </span>
            <div class="envio-foto-perfil">
                <h2 class="card-title mb-2">Foto de perfil</h2>
                <?php if (!$modoSimulacao): ?>
                    <?php if ($erroFoto = session()->getFlashdata('erroFoto')): ?>
                        <div class="alert alert-danger" role="alert"><?= esc($erroFoto) ?></div>
                    <?php elseif ($sucessoFoto = session()->getFlashdata('sucessoFoto')): ?>
                        <div class="alert alert-success" role="status"><?= esc($sucessoFoto) ?></div>
                    <?php endif ?>
                    <form id="form-foto-perfil" method="post" enctype="multipart/form-data" action="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/meu-perfil/foto'), 'attr') ?>">
                        <?= csrf_field() ?>
                        <label class="form-label" for="arquivo-foto-perfil">Escolher foto</label>
                        <input class="form-control" id="arquivo-foto-perfil" name="foto" type="file"
                            accept="image/jpeg,image/png,image/webp" aria-describedby="ajuda-foto-perfil" required>
                        <div class="form-hint" id="ajuda-foto-perfil">JPG, PNG ou WebP de até 2 MB.</div>
                        <p class="text-danger small mt-2" id="erro-recorte-perfil" role="alert" hidden></p>
                        <fieldset id="recorte-foto-perfil" class="recorte-foto-perfil mt-3" hidden>
                            <legend class="form-label">Prévia do recorte</legend>
                            <p class="text-secondary small mb-2">Arraste a foto ou use as setas. Ajuste o zoom para enquadrar.</p>
                            <canvas class="previa-foto-perfil" width="256" height="256" aria-label="Prévia circular da foto de perfil"></canvas>
                            <div class="controles-recorte-perfil mt-2" role="group" aria-label="Ajustar o recorte">
                                <?php foreach ([
                                    'esquerda' => ['arrow-left', 'Mover para a esquerda'],
                                    'direita' => ['arrow-right', 'Mover para a direita'],
                                    'cima' => ['arrow-up', 'Mover para cima'],
                                    'baixo' => ['arrow-down', 'Mover para baixo'],
                                    'diminuir' => ['minus', 'Diminuir zoom'],
                                    'aumentar' => ['plus', 'Aumentar zoom'],
                                ] as $acao => [$icone, $rotulo]): ?>
                                    <button type="button" class="btn btn-outline-secondary btn-icon" data-ajuste-foto="<?= esc($acao, 'attr') ?>"
                                        aria-label="<?= esc($rotulo, 'attr') ?>" title="<?= esc($rotulo, 'attr') ?>">
                                        <i class="fa-solid fa-<?= esc($icone, 'attr') ?>" aria-hidden="true"></i>
                                    </button>
                                <?php endforeach ?>
                            </div>
                        </fieldset>
                        <button class="btn btn-primary mt-2" type="submit">Salvar foto</button>
                    </form>
                <?php endif ?>
            </div>
        </div>
    </div>
</section>

<div class="row g-3 mt-1">
    <div class="col-12 col-lg-6">
        <section class="card h-100" aria-labelledby="titulo-setores-perfil">
            <div class="card-header"><h2 class="card-title" id="titulo-setores-perfil">Setores com acesso</h2></div>
            <?php if ($contextoTrabalho['unidades']): ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($contextoTrabalho['unidades'] as $unidade): ?>
                        <div class="list-group-item">
                            <h3 class="h4 mb-2"><?= esc($unidade['nome']) ?></h3>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($unidade['setores'] as $setor): ?>
                                    <li><?= esc($setor['nome']) ?><?= $setor['sigla'] ? ' · ' . esc($setor['sigla']) : '' ?></li>
                                <?php endforeach ?>
                            </ul>
                        </div>
                    <?php endforeach ?>
                </div>
            <?php else: ?>
                <div class="card-body text-secondary">Você ainda não tem acesso a setores ativos nesta organização.</div>
            <?php endif ?>
        </section>
    </div>
    <?php if (!$modoSimulacao): ?>
        <div class="col-12 col-lg-6">
            <section class="card" id="alterar-senha" aria-labelledby="titulo-alterar-senha">
                <div class="card-header"><h2 class="card-title" id="titulo-alterar-senha">Alterar senha</h2></div>
                <form class="card-body" id="form-senha-perfil" method="post" action="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/meu-perfil/senha'), 'attr') ?>">
                    <?= csrf_field() ?>
                    <?php if ($erro = session()->getFlashdata('erroSenha')): ?>
                        <div class="alert alert-danger" role="alert"><?= esc($erro) ?></div>
                    <?php elseif ($sucesso = session()->getFlashdata('sucessoSenha')): ?>
                        <div class="alert alert-success" role="status"><?= esc($sucesso) ?></div>
                    <?php endif ?>
                    <?php
                    $camposSenha = [
                        'senha-atual' => ['senha_atual', 'Senha atual', 'senha atual', 'current-password'],
                        'nova-senha' => ['nova_senha', 'Nova senha', 'nova senha', 'new-password'],
                        'confirmar-senha' => ['confirmar_senha', 'Confirmar nova senha', 'confirmação da nova senha', 'new-password'],
                    ];
                    ?>
                    <?php foreach ($camposSenha as $idCampo => [$nomeCampo, $rotuloCampo, $alvoBotao, $autocompletar]): ?>
                        <div class="mb-3">
                            <label class="form-label" for="<?= esc($idCampo, 'attr') ?>"><?= esc($rotuloCampo) ?></label>
                            <div class="input-group">
                                <input class="form-control" id="<?= esc($idCampo, 'attr') ?>" name="<?= esc($nomeCampo, 'attr') ?>"
                                    type="password" autocomplete="<?= esc($autocompletar, 'attr') ?>"
                                    <?= $idCampo === 'nova-senha' ? 'minlength="8" aria-describedby="requisitos-senha-perfil"' : '' ?> required>
                                <button class="btn btn-outline-secondary" type="button" aria-pressed="false"
                                    aria-controls="<?= esc($idCampo, 'attr') ?>" data-visibilidade-senha="<?= esc($alvoBotao, 'attr') ?>"
                                    aria-label="<?= esc('Mostrar ' . $alvoBotao, 'attr') ?>">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            <?php if ($idCampo === 'nova-senha'): ?>
                                <ul class="list-unstyled small text-secondary mt-2 mb-0" id="requisitos-senha-perfil" aria-label="Requisitos da nova senha">
                                    <?php foreach ([
                                        'tamanho' => 'Pelo menos 8 caracteres',
                                        'maiuscula' => 'Uma letra maiúscula',
                                        'numero' => 'Um número',
                                        'especial' => 'Um caractere especial, como !, @ ou #',
                                        'confirmacao' => 'Nova senha e confirmação iguais',
                                    ] as $regra => $requisito): ?>
                                        <li class="d-flex align-items-center gap-2" data-requisito-senha="<?= esc($regra, 'attr') ?>">
                                            <i class="fa-regular fa-circle" aria-hidden="true"></i>
                                            <span><?= esc($requisito) ?><span class="visually-hidden" data-estado-requisito> (pendente)</span></span>
                                        </li>
                                    <?php endforeach ?>
                                </ul>
                                <div class="form-hint text-danger" id="limite-senha-perfil" hidden>A nova senha excede o limite de 72 bytes.</div>
                            <?php endif ?>
                        </div>
                    <?php endforeach ?>
                    <button class="btn btn-primary" type="submit">Alterar senha</button>
                </form>
            </section>
        </div>
    <?php endif ?>
</div>
<?= $this->endSection() ?>
