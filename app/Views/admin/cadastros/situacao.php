<?php // Diferencia as situações editáveis e preserva a leitura de valores já existentes. ?>
<span class="situacao-admin <?= $registro['status'] === 'ativo' ? '' : 'inativa' ?>"><?= esc(ucfirst($registro['status'])) ?></span>
