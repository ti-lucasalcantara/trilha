<!-- Usa listas e detalhes nativos para permitir navegação por mouse e teclado. -->
<aside class="painel-explorador" aria-label="Explorador de processos">
    <div class="titulo-explorador"><span><i class="fa-regular fa-folder-open" aria-hidden="true"></i>Explorador</span><button id="alternar-explorador" type="button" aria-controls="arvore-explorador" aria-expanded="true" aria-label="Recolher explorador"><i class="fa-solid fa-chevron-up" aria-hidden="true"></i></button></div>
    <nav id="arvore-explorador" class="arvore-explorador" aria-label="Navegação por unidades e pastas">
        <div class="atalhos-explorador"><button type="button" class="item-explorador selecionado" data-escopo="todos" data-rotulo="Todos os processos" aria-current="true"><i class="fa-solid fa-layer-group" aria-hidden="true"></i><span>Todos os processos</span><small><?= esc((string) count($processos)) ?></small></button><button type="button" class="item-explorador" data-escopo="acompanhados" data-rotulo="Meus acompanhamentos"><i class="fa-regular fa-star" aria-hidden="true"></i><span>Meus acompanhamentos</span><small id="total-acompanhados"><?= esc((string) count(array_filter($processos, static fn (array $processo): bool => $processo['acompanhado']))) ?></small></button></div>
        <div class="grupo-explorador"><h3>POR UNIDADE E SETOR</h3><details class="ramo-explorador" open><summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-building" aria-hidden="true"></i><span><?= esc($nomeOrganizacao) ?></span></summary><ul>
            <?php foreach ($unidades as $unidade => $setores): ?>
            <li><details class="ramo-explorador" open><summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($unidade) ?></span></summary><ul><li><button type="button" class="item-explorador" data-escopo="unidade" data-valor="<?= esc($unidade, 'attr') ?>" data-rotulo="<?= esc($unidade, 'attr') ?>"><i class="fa-solid fa-border-all" aria-hidden="true"></i><span>Toda a unidade</span></button></li>
                <?php foreach (array_keys($setores) as $setor): ?>
                <li><button type="button" class="item-explorador" data-escopo="setor" data-unidade="<?= esc($unidade, 'attr') ?>" data-valor="<?= esc($setor, 'attr') ?>" data-rotulo="<?= esc($unidade . ' / ' . $setor, 'attr') ?>"><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($setor) ?></span></button></li>
                <?php endforeach ?>
            </ul></details></li>
            <?php endforeach ?>
        </ul></details></div>
        <div class="grupo-explorador"><h3>PASTAS DEMONSTRATIVAS</h3>
            <?php foreach ($pastas as $grupoPasta => $subpastas): ?>
            <details class="ramo-explorador" open><summary><i class="fa-solid fa-chevron-right seta-ramo" aria-hidden="true"></i><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($grupoPasta) ?></span></summary><ul>
                <?php foreach (array_keys($subpastas) as $pasta): ?>
                <li><button type="button" class="item-explorador" data-escopo="pasta" data-grupo="<?= esc($grupoPasta, 'attr') ?>" data-valor="<?= esc($pasta, 'attr') ?>" data-rotulo="<?= esc($grupoPasta . ' / ' . $pasta, 'attr') ?>"><i class="fa-regular fa-folder" aria-hidden="true"></i><span><?= esc($pasta) ?></span></button></li>
                <?php endforeach ?>
            </ul></details>
            <?php endforeach ?>
        </div>
        <p class="nota-explorador"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>As pastas organizam a visualização. Não alteram o acesso aos processos.</p>
    </nav>
</aside>
