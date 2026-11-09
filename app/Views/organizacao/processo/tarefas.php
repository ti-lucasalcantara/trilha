<!-- As ações do processo ficam agrupadas junto à sua identificação. -->
<div class="tarefas-processo" aria-label="Ações do processo">
    <button type="button" class="acao-processo destaque" data-acao="tramitar" data-ambito="processo"><i class="fa-solid fa-arrow-right-arrow-left" aria-hidden="true"></i>Tramitar</button>
    <button type="button" class="acao-processo" data-acao="historico" data-ambito="processo"><i class="fa-solid fa-clock-rotate-left" aria-hidden="true"></i>Histórico</button>
    <button type="button" id="acompanhar-processo" class="acao-processo acao-icone" aria-label="Acompanhar processo" title="Acompanhar processo" aria-pressed="<?= $processo['acompanhado'] ? 'true' : 'false' ?>"><i class="<?= $processo['acompanhado'] ? 'fa-solid' : 'fa-regular' ?> fa-star" aria-hidden="true"></i></button>
    <details class="menu-suspenso menu-acoes-processo">
        <summary class="acao-processo acao-icone" aria-label="Mais ações do processo" title="Mais ações do processo"><i class="fa-solid fa-ellipsis" aria-hidden="true"></i></summary>
        <div class="conteudo-menu">
            <button type="button" class="opcao-menu" data-acao="informacoes" data-ambito="processo"><i class="fa-solid fa-circle-info" aria-hidden="true"></i>Informações do processo</button>
            <button type="button" class="opcao-menu" data-acao="acesso" data-ambito="processo"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Nível de acesso</button>
            <button type="button" class="opcao-menu" data-acao="etiquetas" data-ambito="processo"><i class="fa-solid fa-tags" aria-hidden="true"></i>Gerenciar etiquetas</button>
            <button type="button" class="opcao-menu" data-acao="pastas" data-ambito="processo"><i class="fa-regular fa-folder" aria-hidden="true"></i>Organizar em pasta</button>
            <button type="button" class="opcao-menu" data-acao="concluir" data-ambito="processo"><i class="fa-regular fa-circle-check" aria-hidden="true"></i>Concluir processo</button>
        </div>
    </details>
</div>
