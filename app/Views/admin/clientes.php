<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<section class="cartao-admin" aria-label="Cadastro de clientes">
    <?= view('admin/cadastros/cabecalho', compact('tituloPagina', 'registros', 'cadastro', 'singular')) ?>
    <?php if (!$registros): ?>
        <p class="estado-lista-admin">Nenhum cliente cadastrado.</p>
    <?php else: ?>
        <div class="tabela-admin" tabindex="0" role="region" aria-label="Lista de clientes">
            <table>
                <thead><tr><th scope="col">Organização</th><th scope="col">País</th><th scope="col">Plano</th><th scope="col">Vínculos</th><th scope="col">Situação</th><th scope="col">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <tr data-registro-admin>
                            <th scope="row"><a class="link-empresa-admin" href="<?= esc(site_url('admin/clientes/' . $registro['id']), 'attr') ?>"><?= esc($registro['nome']) ?></a><?php if ($registro['nome_fantasia']): ?><small><?= esc($registro['nome_fantasia']) ?></small><?php endif ?></th>
                            <td><?= esc($registro['pais']) ?></td>
                            <td><?= esc($registro['plano'] ?? 'Sem plano') ?></td>
                            <td><?= esc((string) $registro['usuarios']) ?></td>
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
