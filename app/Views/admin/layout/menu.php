<!-- A navegação administrativa é independente do menu das organizações. -->
<aside class="lateral-admin">
    <div class="topo-lateral-admin">
        <a href="<?= esc(site_url('admin'), 'attr') ?>" class="marca-admin" aria-label="Trilha Admin, visão geral">
            <img src="<?= esc(base_url('favicon.svg'), 'attr') ?>" alt="" width="34" height="34">
            <span>Trilha <small>ADMIN</small></span>
        </a>
        <button type="button" class="alternar-menu-admin" aria-expanded="false" aria-controls="menu-admin">
            <i class="fa-solid fa-bars" aria-hidden="true"></i> Menu
        </button>
    </div>
    <nav id="menu-admin" aria-label="Menu administrativo">
        <span class="rotulo-admin">PLATAFORMA</span>
        <a href="<?= esc(site_url('admin'), 'attr') ?>"<?= $paginaAtiva === 'painel' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-chart-simple" aria-hidden="true"></i>Visão geral</a>
        <a href="<?= esc(site_url('admin/clientes'), 'attr') ?>"<?= $paginaAtiva === 'clientes' ? ' aria-current="page"' : '' ?>><i class="fa-regular fa-building" aria-hidden="true"></i>Clientes</a>
        <a href="<?= esc(site_url('admin/planos'), 'attr') ?>"<?= $paginaAtiva === 'planos' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-layer-group" aria-hidden="true"></i>Planos</a>
        <a href="<?= esc(site_url('admin/usuarios'), 'attr') ?>"<?= $paginaAtiva === 'usuarios' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-users" aria-hidden="true"></i>Usuários</a>
        <a href="<?= esc(site_url('admin/financeiro'), 'attr') ?>"<?= $paginaAtiva === 'financeiro' ? ' aria-current="page"' : '' ?>><i class="fa-solid fa-wallet" aria-hidden="true"></i>Financeiro</a>
    </nav>
    <a class="voltar-site-admin" href="<?= esc(site_url(), 'attr') ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>Site do Trilha</a>
</aside>
