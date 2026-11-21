<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/pastas.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/pastas.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<?php
$base = site_url('organizacao/' . $identificadorOrganizacao . '/pastas');
$setor = $contextoTrabalho['setor'];
$trilha = $trilhas[$pastaAtual] ?? [];
$pai = $pastas[$pastaAtual]['pasta_pai_id'] ?? '';
$visibilidades = ['setor' => 'Setor', 'privada' => 'Privada'];
$podeRegistrar = $setor && (!$simulacaoSemVinculo || $autores);
$endereco = static function ($id = '', array $extras = []) use ($base): string {
    $parametros = ((string) $id === '' ? [] : ['pasta' => $id]) + $extras;
    return esc($base . ($parametros ? '?' . http_build_query($parametros) : ''), 'attr');
};
?>
<section class="janela-pastas" aria-labelledby="titulo-pastas">
    <header class="pastas-titulo">
        <h1 id="titulo-pastas"><i class="fa-solid fa-folder" aria-hidden="true"></i> Pastas</h1>
        <span><?= esc($setor['nome'] ?? 'Sem setor ativo') ?></span>
    </header>

    <?php foreach (['erroPastas' => 'danger', 'sucessoPastas' => 'success'] as $chave => $estilo): ?>
        <?php if ($aviso = session()->getFlashdata($chave)): ?>
            <p class="alert alert-<?= $estilo ?> pastas-aviso" role="<?= $estilo === 'danger' ? 'alert' : 'status' ?>"><?= esc($aviso) ?></p>
        <?php endif ?>
    <?php endforeach ?>

    <div class="pastas-endereco">
        <?php if ($pastaAtual !== ''): ?>
            <a class="pastas-subir" href="<?= $endereco($pai) ?>" aria-label="Subir um nível" title="Subir um nível"><i class="fa-solid fa-arrow-up" aria-hidden="true"></i></a>
        <?php endif ?>
        <nav class="pastas-caminho" aria-label="Caminho da pasta">
            <a href="<?= $endereco() ?>" <?= $pastaAtual === '' ? 'aria-current="page"' : '' ?>><i class="fa-solid fa-folder-tree" aria-hidden="true"></i> Pastas do setor</a>
            <?php foreach ($trilha as $id): ?>
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                <a href="<?= $endereco($id) ?>" <?= $id === $pastaAtual ? 'aria-current="page"' : '' ?>><?= esc($pastas[$id]['nome']) ?></a>
            <?php endforeach ?>
        </nav>
        <?php if ($setor): ?>
            <form class="pastas-busca" action="<?= $endereco() ?>" method="get" role="search">
                <?php if ($pastaAtual !== ''): ?><input type="hidden" name="pasta" value="<?= esc($pastaAtual, 'attr') ?>"><?php endif ?>
                <input class="form-control" type="search" name="q" aria-label="Pesquisar nesta pasta" placeholder="Pesquisar nesta pasta" value="<?= esc($busca, 'attr') ?>" maxlength="200">
                <button type="submit" aria-label="Pesquisar"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i></button>
            </form>
        <?php endif ?>
    </div>

    <div class="pastas-comandos" aria-label="Ações de pasta">
        <?php
        $acoes = [];
        if ($podeRegistrar && $podeCriar) $acoes['criar'] = ['Nova pasta', 'fa-plus'];
        if ($podeAlterar) {
            $acoes += [
                'renomear' => ['Renomear', 'fa-pen'],
                'visibilidade' => ['Visibilidade', 'fa-lock'],
                'mover' => ['Mover', 'fa-folder-open'],
                'remover' => ['Excluir', 'fa-trash'],
            ];
        }
        ?>
        <?php foreach ($acoes as $acao => [$rotulo, $icone]): ?>
            <?php $abrirAcao = $acaoAberta === $acao; ?>
            <details class="pastas-comando pastas-comando-<?= $acao ?>" name="comandos-pastas"<?= $abrirAcao ? ' open data-acao-aberta' : '' ?>>
                <summary><i class="fa-solid <?= $icone ?>" aria-hidden="true"></i> <?= $rotulo ?></summary>
                <form class="pastas-formulario" method="post" action="<?= $endereco() ?>">
                    <?= csrf_field() ?>
                    <input type="hidden" name="acao" value="<?= $acao ?>">
                    <input type="hidden" name="setor_contexto" value="<?= esc((string) $setor['id'], 'attr') ?>">
                    <input type="hidden" name="pasta" value="<?= esc($acao === 'criar' ? $pastaAtual : (string) $selecionada['id'], 'attr') ?>">
                    <?php if ($acao === 'criar' || $acao === 'renomear'): ?>
                        <label>Nome da pasta
                            <input class="form-control" name="nome" maxlength="200" required value="<?= $acao === 'renomear' ? esc($selecionada['nome'], 'attr') : '' ?>">
                        </label>
                    <?php endif ?>
                    <?php if ($acao === 'criar' || $acao === 'visibilidade'): ?>
                        <label>Visibilidade
                            <select class="form-select" name="visibilidade">
                                <?php foreach ($visibilidades as $valor => $nome): ?>
                                    <option value="<?= $valor ?>" <?= $acao === 'visibilidade' && $selecionada['visibilidade'] === $valor ? 'selected' : '' ?>><?= $nome ?></option>
                                <?php endforeach ?>
                            </select>
                        </label>
                    <?php endif ?>
                    <?php if ($acao === 'criar' && $simulacaoSemVinculo): ?>
                        <label>Registrar criação em nome de
                            <select class="form-select" name="responsavel" required>
                                <option value="">Selecione um usuário</option>
                                <?php foreach ($autores as $id => $autor): ?>
                                    <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($autor['nome']) ?></option>
                                <?php endforeach ?>
                            </select>
                        </label>
                    <?php endif ?>
                    <?php if ($acao === 'mover'): ?>
                        <input type="hidden" name="origem" value="<?= esc($pastaAtual, 'attr') ?>">
                        <label>Pasta de destino
                            <select class="form-select" name="destino" <?= !$destinos && $pastaAtual === '' ? 'disabled' : '' ?>>
                                <?php if ($pastaAtual !== ''): ?><option value="">Pastas do setor (raiz)</option><?php endif ?>
                                <?php foreach ($destinos as $id => $caminho): ?>
                                    <option value="<?= esc((string) $id, 'attr') ?>"><?= esc($caminho) ?></option>
                                <?php endforeach ?>
                            </select>
                        </label>
                    <?php endif ?>
                    <?php if ($acao === 'remover'): ?>
                        <p>Excluir a pasta <strong><?= esc($selecionada['nome']) ?></strong>?</p>
                        <p>A pasta precisa estar vazia.</p>
                    <?php endif ?>
                    <button class="btn <?= $acao === 'remover' ? 'btn-danger' : 'btn-primary' ?>" type="submit" <?= $acao === 'mover' && !$destinos && $pastaAtual === '' ? 'disabled' : '' ?>><?= $acao === 'criar' ? 'Criar pasta' : $rotulo ?></button>
                </form>
            </details>
        <?php endforeach ?>
        <?php if ($selecionada): ?>
            <a class="pastas-limpar" href="<?= $endereco($pastaAtual, $busca === '' ? [] : ['q' => $busca]) ?>">Limpar seleção</a>
        <?php endif ?>
    </div>

    <div class="pastas-corpo">
        <nav class="pastas-arvore" aria-label="Árvore de pastas">
            <a href="<?= $endereco() ?>" <?= $pastaAtual === '' ? 'aria-current="page"' : '' ?>><i class="fa-solid fa-folder-tree" aria-hidden="true"></i> Pastas do setor</a>
            <?php foreach ($trilhas as $id => $caminho): ?>
                <a style="--nivel: <?= min(count($caminho), 6) ?>" href="<?= $endereco($id) ?>" <?= (string) $id === $pastaAtual ? 'aria-current="page"' : '' ?>>
                    <i class="fa-solid <?= (string) $id === $pastaAtual ? 'fa-folder-open' : 'fa-folder' ?>" aria-hidden="true"></i> <?= esc($pastas[$id]['nome']) ?>
                </a>
            <?php endforeach ?>
        </nav>
        <div class="pastas-listagem"<?= $podeRegistrar && $podeCriar ? ' data-nova-pasta="' . $endereco($pastaAtual, ['acao' => 'criar']) . '"' : '' ?>>
            <?php if (!$filhas && !$processos): ?>
                <div class="pastas-vazio">
                    <i class="fa-regular fa-folder-open" aria-hidden="true"></i>
                    <p><?= !$setor ? 'Selecione um setor para ver suas pastas.' : ($busca !== '' ? 'Nenhum item encontrado.' : 'Esta pasta está vazia.') ?></p>
                    <?php if ($busca !== ''): ?><a href="<?= $endereco($pastaAtual) ?>">Limpar pesquisa</a><?php endif ?>
                </div>
            <?php else: ?>
                <table class="pastas-tabela" aria-label="Conteúdo da pasta">
                    <thead><tr><th scope="col">Nome</th><th scope="col">Data de modificação</th><th scope="col">Tipo</th><th scope="col">Visibilidade ou situação</th></tr></thead>
                    <tbody>
                    <?php foreach ($filhas as $id => $item): ?>
                        <?php
                        $ativa = $selecionada && (string) $selecionada['id'] === (string) $id;
                        $gerencia = $administradorSimulado || (string) $item['criada_por'] === $vinculoPreferencias;
                        $selecao = ['selecionada' => $id] + ($busca === '' ? [] : ['q' => $busca]);
                        ?>
                        <tr tabindex="0" data-selecao="<?= $endereco($pastaAtual, $selecao) ?>"
                            <?= $gerencia ? 'data-gerencia' : '' ?>
                            <?= $gerencia && $podeRegistrar ? 'data-nova-pasta="' . $endereco($id, ['acao' => 'criar']) . '"' : '' ?>
                            <?= $ativa ? 'class="selecionada" aria-current="true"' : '' ?>>
                            <td><a href="<?= $endereco($id) ?>"><i class="fa-solid fa-folder" aria-hidden="true"></i> <?= esc($item['nome']) ?></a></td>
                            <td><?= esc(date('d/m/Y H:i', strtotime($item['atualizado_em'] ?? $item['criado_em']))) ?></td>
                            <td>Pasta</td>
                            <td><?= esc($visibilidades[$item['visibilidade']] ?? $item['visibilidade']) ?></td>
                        </tr>
                    <?php endforeach ?>
                    <?php foreach ($processos as $processo): ?>
                        <tr>
                            <td>
                                <a href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . $processo['hash_publico']), 'attr') ?>">
                                    <i class="fa-regular fa-file-lines pastas-icone-processo" aria-hidden="true"></i>
                                    <?= esc($processo['assunto'] ?: 'Processo sem assunto') ?>
                                </a>
                                <?php if ($processo['numero']): ?><small><?= esc($processo['numero']) ?></small><?php endif ?>
                            </td>
                            <td><?= esc(date('d/m/Y H:i', strtotime($processo['atualizacao']))) ?></td>
                            <td>Processo</td>
                            <td><?= esc($processo['situacao']) ?></td>
                        </tr>
                    <?php endforeach ?>
                    </tbody>
                </table>
            <?php endif ?>
        </div>
    </div>
    <footer class="pastas-status">
        <span><?= count($filhas) ?> <?= count($filhas) === 1 ? 'pasta' : 'pastas' ?></span>
        <?php if ($pastaAtual !== ''): ?><span><?= count($processos) ?> <?= count($processos) === 1 ? 'processo' : 'processos' ?></span><?php endif ?>
        <?php if ($selecionada): ?><span><?= esc($selecionada['nome']) ?> <?= $podeAlterar ? 'selecionada' : '· somente leitura' ?></span><?php endif ?>
    </footer>
    <nav class="pastas-menu-contexto" popover aria-label="Ações da pasta">
        <a data-opcao="abrir"><i class="fa-solid fa-folder-open" aria-hidden="true"></i> Abrir</a>
        <a data-opcao="criar"><i class="fa-solid fa-plus" aria-hidden="true"></i> Nova pasta</a>
        <a data-opcao="renomear"><i class="fa-solid fa-pen" aria-hidden="true"></i> Renomear</a>
        <a data-opcao="mover"><i class="fa-solid fa-arrow-right" aria-hidden="true"></i> Mover</a>
        <a data-opcao="visibilidade"><i class="fa-solid fa-lock" aria-hidden="true"></i> Visibilidade</a>
        <a data-opcao="remover"><i class="fa-solid fa-trash" aria-hidden="true"></i> Excluir</a>
    </nav>
</section>
<?= $this->endSection() ?>
