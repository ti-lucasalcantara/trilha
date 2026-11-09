<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<a class="botao-admin secundario voltar-cadastro-admin" href="<?= esc(site_url('admin/clientes/' . $empresa['id']), 'attr') ?>"><i class="fa-solid fa-arrow-left" aria-hidden="true"></i> Voltar à empresa</a>

<!-- Exibe atribuições registradas sem iniciar sessão como a pessoa selecionada. -->
<section class="cartao-admin" aria-labelledby="titulo-contexto-usuario">
    <header>
        <h2 id="titulo-contexto-usuario"><?= esc($usuario['nome']) ?></h2>
        <p><?= esc($usuario['email']) ?></p>
        <p class="nota-admin">Esta prévia mostra os vínculos cadastrados. A validação de acesso a processos e documentos depende da implementação da ACL da organização.</p>
    </header>
</section>
<section class="cartao-admin" aria-labelledby="titulo-setores-simulacao">
    <header><h2 id="titulo-setores-simulacao">Setores vinculados</h2></header>
    <?php if (!$setores): ?><p class="estado-lista-admin">Nenhum setor vinculado.</p><?php endif ?>
    <ul class="lista-contexto-admin">
        <?php foreach ($setores as $setor): ?>
            <li><strong><?= esc($setor['setor']) ?></strong><span><?= esc($setor['unidade'] . ($setor['funcao'] ? ' · ' . $setor['funcao'] : '')) ?></span></li>
        <?php endforeach ?>
    </ul>
</section>
<section class="cartao-admin" aria-labelledby="titulo-perfis-simulacao">
    <header><h2 id="titulo-perfis-simulacao">Perfis e permissões atribuídos</h2></header>
    <?php if (!$perfis): ?><p class="estado-lista-admin">Nenhum perfil atribuído.</p><?php endif ?>
    <ul class="lista-contexto-admin">
        <?php foreach ($perfis as $perfil): ?>
            <?php $ambito = implode(' / ', array_filter([$perfil['unidade'], $perfil['setor']])); ?>
            <li>
                <strong><?= esc($perfil['perfil']) ?></strong>
                <span><?= esc(($ambito ?: 'Organização inteira') . ' · ' . ucfirst($perfil['status'])) ?></span>
                <?php foreach ($permissoes[$perfil['id']] ?? [] as $permissao): ?>
                    <small><?= esc($permissao['modulo'] . ' · ' . $permissao['descricao']) ?></small>
                <?php endforeach ?>
            </li>
        <?php endforeach ?>
    </ul>
</section>
<?= $this->endSection() ?>
