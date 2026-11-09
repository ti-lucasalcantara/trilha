<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<section class="cartao-admin" aria-label="Cadastro de usuários">
    <?= view('admin/cadastros/cabecalho', compact('tituloPagina', 'registros', 'cadastro', 'singular')) ?>
    <?php if (!$registros): ?>
        <p class="estado-lista-admin">Nenhum usuário cadastrado.</p>
    <?php else: ?>
        <div class="tabela-admin" tabindex="0" role="region" aria-label="Lista de usuários">
            <table>
                <thead><tr><th scope="col">Usuário</th><th scope="col">Organizações</th><th scope="col">Situação</th><th scope="col">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr data-registro-admin>
                            <th scope="row"><?= esc($registro['nome']) ?><small><?= esc($registro['email']) ?></small></th>
                            <td><?= esc((string) $registro['organizacoes']) ?></td>
                            <td><?= view('admin/cadastros/situacao', ['registro' => $registro]) ?></td>
                            <?= view('admin/cadastros/acoes', ['cadastro' => $cadastro, 'registro' => $registro]) ?>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    <?php endif ?>
</section>
<?= view('admin/cadastros/exclusao', ['singular' => $singular]) ?>
<?= $this->endSection() ?>
