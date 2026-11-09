<?= $this->extend('layout/principal') ?>

<?= $this->section('scripts') ?>
<script src="<?= esc(base_url('assets/js/organizacao.js'), 'attr') ?>" defer></script>
<?= $this->endSection() ?>

<?= $this->section('conteudo') ?>
<!-- Define o contexto de trabalho apenas nesta demonstração, sem alterar vínculos. -->
<div class="linha-contexto"><div class="caminho-pagina"><i class="fa-regular fa-building" aria-hidden="true"></i><?= esc($nomeOrganizacao) ?><span>/</span><span>Área de trabalho</span></div></div>
<div class="titulo-acoes"><div><p class="sobretitulo">SEU DIA, MAIS ORGANIZADO</p><h1>Caixa de trabalho<span class="ponto-titulo">.</span></h1><p class="descricao-pagina">Tudo o que chegou até você. Cada próximo passo, à vista.</p></div><button type="button" class="btn btn-primary botao-novo" data-aviso="A tela de abertura de processo será construída na próxima etapa. Nenhum processo foi criado."><i class="fa-solid fa-plus" aria-hidden="true"></i>Novo processo</button></div>

<!-- Os contadores são calculados a partir dos mesmos exemplos exibidos na lista. -->
<section class="resumos-trabalho" aria-label="Resumo da caixa de trabalho">
    <button type="button" class="resumo-trabalho" data-filtrar="todos"><div><span>Na sua caixa</span><strong><?= esc((string) $quantidades['todos']) ?></strong><small>Processos para acompanhar</small></div><span class="icone-resumo roxo"><i class="fa-solid fa-layer-group" aria-hidden="true"></i></span></button>
    <button type="button" class="resumo-trabalho" data-filtrar="receber"><div><span>Para receber</span><strong><?= esc((string) $quantidades['receber']) ?></strong><small>Aguardando seu recebimento</small></div><span class="icone-resumo laranja"><i class="fa-solid fa-arrow-down" aria-hidden="true"></i></span></button>
    <button type="button" class="resumo-trabalho" data-filtrar="providencia"><div><span>Para providência</span><strong><?= esc((string) $quantidades['providencia']) ?></strong><small>Precisam da sua atenção</small></div><span class="icone-resumo azul"><i class="fa-regular fa-clock" aria-hidden="true"></i></span></button>
    <button type="button" class="resumo-trabalho" data-filtrar="assinar"><div><span>Para assinar</span><strong><?= esc((string) $quantidades['assinar']) ?></strong><small>Documento aguardando você</small></div><span class="icone-resumo verde"><i class="fa-solid fa-signature" aria-hidden="true"></i></span></button>
</section>

<div class="grade-trabalho">
    <section class="painel-processos" aria-labelledby="titulo-processos">
        <div class="cabecalho-painel"><div><h2 id="titulo-processos">Seus processos</h2><p>Um lugar para acompanhar e fazer avançar.</p></div><span class="selo-demonstracao">Dados de exemplo</span></div>
        <div class="abas-caixa" role="group" aria-label="Filtrar processos por pendência"><button type="button" data-aba="todos" aria-pressed="true">Todos<span><?= esc((string) $quantidades['todos']) ?></span></button><button type="button" data-aba="receber" aria-pressed="false">Para receber<span><?= esc((string) $quantidades['receber']) ?></span></button><button type="button" data-aba="providencia" aria-pressed="false">Para providência<span><?= esc((string) $quantidades['providencia']) ?></span></button><button type="button" data-aba="assinar" aria-pressed="false">Para assinar<span><?= esc((string) $quantidades['assinar']) ?></span></button></div>
        <div class="ferramentas-listagem"><label class="campo-pesquisa"><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span class="visualmente-oculto">Pesquisar processo por número, assunto ou setor</span><input id="pesquisa-processos" type="search" placeholder="Buscar por número, assunto ou setor…" autocomplete="off"></label><label class="filtro-tipo"><span class="visualmente-oculto">Filtrar por tipo de processo</span><select id="tipo-processo"><option value="">Todos os tipos</option>Compras</option><option>Admissão</option><option>Contratos</option></select></label></div>
        <div class="tabela-responsiva"><table class="tabela-processos"><thead><tr><th scope="col">Processo / assunto</th><th scope="col">Origem</th><th scope="col">Situação</th><th scope="col">Prazo</th><th scope="col"><span class="visualmente-oculto">Ações</span></th></tr></thead><tbody>
            <?php foreach ($processos as $processo): ?>
            <tr data-processo="<?= esc((string) $processo['id'], 'attr') ?>" data-categoria="<?= esc($processo['categoria'], 'attr') ?>" data-tipo="<?= esc($processo['tipo'], 'attr') ?>" data-assunto="<?= esc($processo['assunto'], 'attr') ?>" data-numero="<?= esc($processo['numero'], 'attr') ?>" data-origem="<?= esc($processo['origem'], 'attr') ?>" data-unidade="<?= $processo['id'] === 5 ? 'Filial Centro' : 'Matriz' ?>">
                <td class="celula-processo"><a class="abrir-processo" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . $processo['id']), 'attr') ?>"><span><?= esc($processo['numero']) ?></span><strong><?= esc($processo['assunto']) ?></strong></a><div class="metadados-processo"><span class="etiqueta-processo"><?= esc($processo['etiqueta']) ?></span><span><i class="fa-solid fa-paperclip" aria-hidden="true"></i><?= esc((string) $processo['documentos']) ?></span></div></td>
                <td><div class="origem-processo"><span class="avatar-remetente"><?= esc($processo['iniciais']) ?></span><div><strong><?= esc($processo['origem']) ?></strong><small><?= esc($processo['responsavel']) ?></small></div></div></td>
                <td><span class="situacao-processo <?= esc($processo['destaque'], 'attr') ?>"><span></span><?= esc($processo['situacao']) ?></span></td>
                <td><span class="prazo-processo <?= $processo['prazo'] === 'Hoje' ? 'prazo-proximo' : '' ?>"><?= esc($processo['prazo']) ?></span><small class="atualizacao-processo"><?= esc($processo['atualizacao']) ?></small></td>
                <td><a class="botao-icone abrir-detalhes" href="<?= esc(site_url('organizacao/' . $identificadorOrganizacao . '/processos/' . $processo['id']), 'attr') ?>" aria-label="Visualizar <?= esc($processo['numero'], 'attr') ?>"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i></a></td>
            </tr>
            <?php endforeach ?>
        </tbody></table></div>
        <div class="estado-vazio" id="nenhum-processo" hidden><i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><h3>Nenhum processo encontrado</h3><p>Tente outro termo ou ajuste os filtros.</p><button type="button" id="limpar-filtros">Limpar filtros</button></div>
        <div class="rodape-listagem"><span id="quantidade-resultados" aria-live="polite">Exibindo <?= esc((string) count($processos)) ?> processos</span><span>Mais recentes primeiro</span></div>
    </section>
    <aside class="coluna-acompanhamento" aria-label="Prazos e últimas movimentações">
        <section class="painel-lateral"><div class="titulo-lateral"><span class="icone-lateral laranja"><i class="fa-regular fa-clock" aria-hidden="true"></i></span><h2>De olho nos prazos</h2></div><p class="descricao-lateral">O que merece atenção primeiro.</p><button type="button" class="pendencia-lateral" data-abrir-processo="1"><span class="marcador-prazo laranja"></span><div><small>COMPRAS-0008/2026</small><strong>Aquisição de equipamentos</strong><span>Hoje <i aria-hidden="true">·</i> Aguardando recebimento</span></div><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button><button type="button" class="pendencia-lateral" data-abrir-processo="2"><span class="marcador-prazo roxo"></span><div><small>ADM-0012/2026</small><strong>Admissão de colaborador</strong><span>Hoje <i aria-hidden="true">·</i> Para assinar</span></div><i class="fa-solid fa-chevron-right" aria-hidden="true"></i></button><button type="button" class="link-painel" data-filtrar="todos">Ver na caixa de trabalho<i class="fa-solid fa-arrow-right" aria-hidden="true"></i></button></section>
        <section class="painel-lateral"><div class="titulo-lateral"><span class="icone-lateral roxo"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i></span><h2>Últimas movimentações</h2></div><div class="linha-tempo"><div><span class="ponto-evento verde"></span><p><strong>Mariana Costa</strong> encaminhou um processo para seu setor.</p><small>COMPRAS-0008/2026 · Há 15 min</small></div><div><span class="ponto-evento roxo"></span><p><strong>Ana Souza</strong> disponibilizou um documento para assinatura.</p><small>ADM-0012/2026 · Há 40 min</small></div><div><span class="ponto-evento neutro"></span><p>O processo de licenças foi recebido no <strong>Administrativo</strong>.</p><small>COMPRAS-0007/2026 · Há 1 h</small></div></div></section>
        <div class="nota-organizacao"><span class="simbolo-nota"><i class="fa-solid fa-route" aria-hidden="true"></i></span><p>Menos papel.<br><strong>Mais clareza no caminho.</strong></p></div>
    </aside>
</div>
<?= $this->endSection() ?>

