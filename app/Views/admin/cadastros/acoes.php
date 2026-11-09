<?php
// A confirmação de exclusão usa POST com token CSRF.
$enderecoCadastro = 'admin/' . $cadastro . '/' . $registro['id'];
?>
<td class="acoes-cadastro-admin">
    <a class="botao-admin secundario" href="<?= esc(site_url($enderecoCadastro . '/editar'), 'attr') ?>" aria-label="Editar <?= esc($registro['nome'], 'attr') ?>"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Editar</a>
    <form method="post" action="<?= esc(site_url($enderecoCadastro . '/excluir'), 'attr') ?>" data-excluir-admin="<?= esc($registro['nome'], 'attr') ?>">
        <?= csrf_field() ?>
        <button class="botao-admin perigo" type="submit" aria-label="Excluir <?= esc($registro['nome'], 'attr') ?>"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Excluir</button>
    </form>
</td>
