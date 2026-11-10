<?= $this->extend('layout/principal') ?>

<?= $this->section('estilos') ?>
<link rel="stylesheet" href="<?= esc(base_url('assets/css/configuracoes.css'), 'attr') ?>">
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/configuracoes.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<header class="cabecalho-configuracoes">
    <p class="contexto-configuracoes"><i class="fa-solid fa-sliders" aria-hidden="true"></i>Configurações da organização</p>
    <h1>Estrutura e equipe</h1>
    <p>Organize as unidades, vincule pessoas aos setores e defina o que cada perfil pode fazer.</p>
</header>

<!-- A mesma interface recebe os dados do banco quando há uma sessão administrativa. -->
<div class="area-configuracoes"<?= isset($configuracoesReais) ? ' data-configuracoes-reais="' . esc(json_encode($configuracoesReais, JSON_THROW_ON_ERROR | JSON_INVALID_UTF8_SUBSTITUTE), 'attr') . '"' : '' ?>>
    <nav class="navegacao-configuracoes" aria-label="Configurações da organização">
        <a href="#estrutura" data-pagina-configuracoes="estrutura"><i class="fa-regular fa-building" aria-hidden="true"></i>Estrutura</a>
        <a href="#equipe" data-pagina-configuracoes="equipe"><i class="fa-solid fa-users" aria-hidden="true"></i>Equipe</a>
        <a href="#permissoes" data-pagina-configuracoes="permissoes"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Perfis e permissões</a>
        <?php if (isset($tiposProcesso)): ?>
            <a href="#tipos-processo" data-pagina-configuracoes="tipos-processo"><i class="fa-regular fa-file-lines" aria-hidden="true"></i>Tipos de processo</a>
        <?php endif ?>
        <?php if (isset($situacoesProcesso)): ?>
            <a href="#situacoes-processo" data-pagina-configuracoes="situacoes-processo"><i class="fa-solid fa-list-check" aria-hidden="true"></i>Situações de processo</a>
        <?php endif ?>
    </nav>
    <div class="conteudo-configuracoes">
        <section id="estrutura" aria-label="Unidades e setores">
            <article class="cartao-configuracoes">
                <header><div><h2>Unidades e setores <span class="contagem-configuracoes" id="contagem-unidades"></span></h2><p>Abra uma unidade para consultar e gerenciar seus setores.</p></div><button type="button" class="btn btn-primary" data-novo="unidades"><i class="fa-solid fa-plus" aria-hidden="true"></i>Nova unidade</button></header>
                <div id="lista-unidades" class="lista-configuracoes"></div>
            </article>
            <!-- Cada unidade recebe uma cópia independente do painel de setores. -->
            <template id="modelo-setores-da-unidade">
                <section class="setores-da-unidade">
                    <header><div><h3>Setores <span class="contagem-configuracoes"></span></h3><p class="nome-unidade"></p></div><button type="button" class="controle-configuracoes" data-novo="setores"><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo setor</button></header>
                    <div class="lista-configuracoes"></div>
                </section>
            </template>
        </section>
        <section id="equipe" class="cartao-configuracoes" aria-labelledby="titulo-equipe" hidden>
            <header><div><h2 id="titulo-equipe">Equipe <span class="contagem-configuracoes" id="contagem-pessoas"></span></h2><p>Consulte as pessoas da organização e os setores aos quais estão vinculadas.</p></div><button type="button" class="btn btn-primary" data-novo="pessoas"><i class="fa-solid fa-plus" aria-hidden="true"></i>Adicionar pessoa</button></header>
            <label class="busca-equipe"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visualmente-oculto">Buscar por nome ou e-mail</span><input type="search" id="buscar-equipe" placeholder="Buscar por nome ou e-mail"></label>
            <div id="lista-pessoas" class="lista-configuracoes"></div>
        </section>
        <section id="permissoes" aria-label="Perfis e permissões" hidden>
            <article class="cartao-configuracoes">
                <header><div><h2>Perfis de acesso <span class="contagem-configuracoes" id="contagem-perfis"></span></h2><p>Escolha um perfil para ver suas permissões e seus usuários.</p></div></header>
                <div class="seletor-abas-perfis">
                    <button type="button" class="btn btn-primary" data-novo="perfis" aria-label="Novo perfil" title="Novo perfil"><i class="fa-solid fa-plus" aria-hidden="true"></i></button>
                    <div id="abas-perfis-cadastrados" role="tablist" aria-label="Perfis cadastrados"></div>
                </div>
                <p id="perfis-vazios" class="vazio-configuracoes" hidden>Cadastre um perfil para configurar o acesso.</p>
                <div id="detalhes-perfil" role="tabpanel">
                    <div class="barra-perfil">
                        <div><h3 id="nome-perfil"></h3><p id="descricao-perfil"></p></div>
                        <div id="acoes-perfil" class="acoes-configuracoes"></div>
                    </div>
                    <div class="abas-perfil" role="tablist" aria-label="Detalhes do perfil">
                        <button type="button" role="tab" id="aba-permissoes-perfil" aria-controls="painel-permissoes-perfil" aria-selected="true">Permissões</button>
                        <button type="button" role="tab" id="aba-usuarios-perfil" aria-controls="painel-usuarios-perfil" aria-selected="false" tabindex="-1">Usuários <span class="contagem-configuracoes" id="contagem-atribuicoes"></span></button>
                    </div>
                    <div id="painel-permissoes-perfil" role="tabpanel" aria-labelledby="aba-permissoes-perfil">
                        <form id="formulario-permissoes-perfil">
                            <p>Marque as ações disponíveis para este perfil.</p>
                            <div id="marcacoes-perfil" class="marcacoes-perfil"></div>
                            <button type="submit" class="btn btn-primary">Salvar permissões</button>
                        </form>
                    </div>
                    <div id="painel-usuarios-perfil" role="tabpanel" aria-labelledby="aba-usuarios-perfil" hidden>
                        <header><div><h3>Usuários do perfil</h3><p>Consulte as pessoas vinculadas e onde o perfil se aplica.</p></div><button type="button" class="controle-configuracoes" data-novo="atribuicoes"><i class="fa-solid fa-plus" aria-hidden="true"></i>Vincular usuário</button></header>
                        <div id="lista-atribuicoes" class="lista-configuracoes"></div>
                    </div>
                </div>
            </article>
        </section>
        <?php if (isset($tiposProcesso)): ?>
            <?= $this->include('organizacao/configuracoes/tipos_processo') ?>
        <?php endif ?>
        <?php if (isset($situacoesProcesso)): ?>
            <?= $this->include('organizacao/configuracoes/situacoes_processo') ?>
        <?php endif ?>
        <?php if (isset($configuracoesReais) && ($mensagem = session()->getFlashdata('sucessoSimulacao'))): ?>
            <p class="alert alert-success" role="status"><?= esc($mensagem) ?></p>
        <?php elseif (isset($configuracoesReais) && ($mensagem = session()->getFlashdata('erroSimulacao'))): ?>
            <p class="alert alert-danger" role="alert"><?= esc($mensagem) ?></p>
        <?php endif ?>
        <p class="nota-configuracoes"><i class="fa-solid fa-circle-info" aria-hidden="true"></i> <?= isset($configuracoesReais) ? 'Dados da organização. As ações disponíveis gravam no banco.' : 'Prévia demonstrativa. As alterações valem até recarregar a página.' ?></p>
        <p id="aviso-configuracoes" class="nota-configuracoes" role="status"></p>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('dialogos') ?>
<dialog id="dialogo-configuracoes" class="dialogo-configuracoes" aria-labelledby="titulo-edicao-configuracoes">
    <form method="dialog">
        <h2 id="titulo-edicao-configuracoes"></h2>
        <div id="campos-configuracoes"></div>
        <p id="erro-configuracoes" role="alert" hidden></p>
        <footer>
            <button class="controle-configuracoes" type="button" value="cancelar" data-cancelar-dialogo>Cancelar</button>
            <button class="btn btn-primary" id="confirmar-configuracoes" type="submit" value="confirmar">Salvar</button>
        </footer>
    </form>
</dialog>
<?= $this->endSection() ?>
