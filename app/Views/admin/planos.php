<?= $this->extend('admin/layout/principal') ?>

<?= $this->section('conteudo') ?>
<section class="cartao-admin" aria-label="Cadastro de planos">
    <?= view('admin/cadastros/cabecalho', compact('tituloPagina', 'registros', 'cadastro', 'singular')) ?>
    <?php if (!$registros): ?>
        <p class="estado-lista-admin">Nenhum plano cadastrado.</p>
    <?php else: ?>
        <div class="tabela-admin" tabindex="0" role="region" aria-label="Lista de planos">
            <table>
                <thead><tr><th scope="col">Plano</th><th scope="col">Preço</th><th scope="col">Armazenamento</th><th scope="col">Usuários</th><th scope="col">Situação</th><th scope="col">Ações</th></tr></thead>
                <tbody>
                    <?php foreach ($registros as $registro): ?>
                        <?php $limites = json_decode($registro['limites'] ?? '{}', true) ?: []; ?>
                        <tr data-registro-admin>
                            <th scope="row"><?= esc($registro['nome']) ?></th>
                            <td><?= esc($registro['preco'] === null ? 'Sem versão vigente' : $registro['moeda'] . ' ' . str_replace('.', ',', $registro['preco']) . ' · ' . $registro['periodicidade']) ?></td>
                            <td><?= isset($limites['armazenamento_gb']) ? esc((string) $limites['armazenamento_gb']) . ' GB' : '—' ?></td>
                            <td><?= esc((string) ($limites['usuarios'] ?? '—')) ?></td>
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
