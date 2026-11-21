<?= $this->extend('organizacao/configuracoes') ?>

<?= $this->section('painelConfiguracoes') ?>
<section id="permissoes" aria-label="Perfis e permissões">
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
<?= $this->endSection() ?>
