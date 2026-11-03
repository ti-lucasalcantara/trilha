"""Gera migrations independentes do CI4. Utilitário de desenvolvimento; não altera bancos diretamente."""
from pathlib import Path
import hashlib
import json
from datetime import datetime, timedelta
RAIZ = Path(__file__).resolve().parents[1]
DIRETORIO_SAIDA = RAIZ / 'app/Database/Migrations'
tabelas = {}

def definir_tabela(nome, especificacao, *, tabela_global=False, chaves_unicas=(), verificacoes=()):
    """Registra os campos e as restrições de uma tabela."""
    campos = {'id': ('id', False)}
    if not tabela_global:
        campos['organizacao_id'] = ('@organizacoes', False)
    for item in especificacao.split():
        nome_campo, tipo = item.split(':', 1)
        aceita_nulo = tipo.endswith('?')
        campos[nome_campo] = (tipo.rstrip('?'), aceita_nulo)
    campos['criado_em'] = ('dt', False)
    campos['atualizado_em'] = ('dt', True)
    tabelas[nome] = dict(fields=campos, tenant=not tabela_global, unique=list(chaves_unicas), checks=list(verificacoes))
definir_tabela('usuarios', 'nome:s200 email:s254 email_verificado_em:dt? senha_hash:s255 idioma:s20? status:s40', tabela_global=True, chaves_unicas=[['email']])
definir_tabela('organizacoes', 'nome:s200 nome_fantasia:s200? pais:s2 idioma:s20 fuso_horario:s80 status:s40', tabela_global=True)
definir_tabela('organizacao_identificadores', 'tipo:s40 valor:s100 pais:s2', chaves_unicas=[['organizacao_id', 'tipo', 'pais', 'valor']])
definir_tabela('unidades', 'nome:s200 sigla:s40? unidade_superior_id:@unidades? status:s40')
definir_tabela('unidade_identificadores', 'unidade_id:@unidades tipo:s40 valor:s100 pais:s2')
definir_tabela('setores', 'unidade_id:@unidades setor_superior_id:@setores? nome:s200 sigla:s40? status:s40')
definir_tabela('vinculos_organizacao', 'usuario_id:@usuarios tipo_vinculo:s40 status:s40 inicio_em:dt fim_em:dt?', chaves_unicas=[['organizacao_id', 'usuario_id']])
definir_tabela('vinculos_setor', 'vinculo_id:@vinculos_organizacao setor_id:@setores funcao:s150? responsavel_setor:bool inicio_em:dt fim_em:dt?')
definir_tabela('convites_organizacao', 'email:s254 tipo_vinculo:s40 token_hash:s64 expira_em:dt aceito_em:dt? revogado_em:dt? convidado_por:@vinculos_organizacao', chaves_unicas=[['token_hash']])
definir_tabela('sessoes_usuario', 'usuario_id:@usuarios token_hash:s64 expira_em:dt revogado_em:dt? ip:s45? agente_usuario:text?', tabela_global=True, chaves_unicas=[['token_hash']])
definir_tabela('arquivos', 'chave_armazenamento:s500 nome_original:s255 mime_type:s150 tamanho_bytes:uint hash:s128 algoritmo_hash:s30 status_verificacao:s40 criado_por:@vinculos_organizacao?', chaves_unicas=[['organizacao_id', 'chave_armazenamento']])
definir_tabela('organizacao_configuracoes', 'logo_arquivo_id:@arquivos? preferencias:json?', chaves_unicas=[['organizacao_id']])
definir_tabela('permissoes', 'codigo:s120 descricao:s255 modulo:s80', tabela_global=True, chaves_unicas=[['codigo']])
definir_tabela('perfis', 'nome:s150 descricao:text? status:s40', chaves_unicas=[['organizacao_id', 'nome']])
definir_tabela('perfil_permissoes', 'perfil_id:@perfis permissao_id:@permissoes', chaves_unicas=[['organizacao_id', 'perfil_id', 'permissao_id']])
definir_tabela('atribuicoes_perfil', 'vinculo_id:@vinculos_organizacao perfil_id:@perfis unidade_id:@unidades? setor_id:@setores?')
definir_tabela('status_processo', 'nome:s100 descricao:text? cor:s20? ordem:int ativo:bool', chaves_unicas=[['organizacao_id', 'nome']])
definir_tabela('status_documento', 'nome:s100 descricao:text? cor:s20? ordem:int ativo:bool', chaves_unicas=[['organizacao_id', 'nome']])
definir_tabela('tipos_processo', 'nome:s150 sigla:s40 descricao:text? ativo:bool', chaves_unicas=[['organizacao_id', 'nome']])
definir_tabela('tipo_processo_versoes', 'tipo_processo_id:@tipos_processo versao:uint instrucoes:text? acesso_padrao:s40? prazo_padrao_dias:int? publicado_em:dt? publicado_por:@vinculos_organizacao?', chaves_unicas=[['organizacao_id', 'tipo_processo_id', 'versao']])
definir_tabela('tipos_documento', 'nome:s150 sigla:s40 descricao:text? ativo:bool', chaves_unicas=[['organizacao_id', 'nome']])
definir_tabela('tipo_documento_versoes', 'tipo_documento_id:@tipos_documento versao:uint instrucoes:text? configuracao:json? publicado_em:dt? publicado_por:@vinculos_organizacao?', chaves_unicas=[['organizacao_id', 'tipo_documento_id', 'versao']])
definir_tabela('definicoes_campo', 'chave:s100 nome:s150 tipo_dado:s40 ativo:bool', chaves_unicas=[['organizacao_id', 'chave']])
definir_tabela('opcoes_campo', 'campo_id:@definicoes_campo valor:s150 rotulo:s200 ordem:int ativo:bool', chaves_unicas=[['organizacao_id', 'campo_id', 'valor']])
definir_tabela('tipo_processo_campos', 'tipo_processo_versao_id:@tipo_processo_versoes campo_id:@definicoes_campo obrigatorio:bool ordem:int validacao:json?', chaves_unicas=[['organizacao_id', 'tipo_processo_versao_id', 'campo_id']])
definir_tabela('tipo_documento_campos', 'tipo_documento_versao_id:@tipo_documento_versoes campo_id:@definicoes_campo obrigatorio:bool ordem:int validacao:json?', chaves_unicas=[['organizacao_id', 'tipo_documento_versao_id', 'campo_id']])
definir_tabela('documentos_exigidos', 'tipo_processo_versao_id:@tipo_processo_versoes tipo_documento_id:@tipos_documento quantidade_minima:uint etapa_validacao:s100?')
definir_tabela('regras_numeracao_processo', 'tipo_processo_id:@tipos_processo versao:uint mascara:s200 sigla:s40 digitos_minimos:int vigente_desde:dt vigente_ate:dt?', chaves_unicas=[['organizacao_id', 'tipo_processo_id', 'versao']])
definir_tabela('sequencias_processo', 'tipo_processo_id:@tipos_processo ano:year ultimo_numero:uint', chaves_unicas=[['organizacao_id', 'tipo_processo_id', 'ano']])
definir_tabela('regras_numeracao_documento', 'tipo_documento_id:@tipos_documento versao:uint mascara:s200 sigla:s40 digitos_minimos:int reinicia_anualmente:bool vigente_desde:dt vigente_ate:dt?', chaves_unicas=[['organizacao_id', 'tipo_documento_id', 'versao']])
definir_tabela('sequencias_documento', 'tipo_documento_id:@tipos_documento periodo:s20 ultimo_numero:uint', chaves_unicas=[['organizacao_id', 'tipo_documento_id', 'periodo']])
definir_tabela('processos', 'tipo_processo_id:@tipos_processo tipo_processo_versao_id:@tipo_processo_versoes regra_numeracao_id:@regras_numeracao_processo numero_sequencial:uint ano:year numero_completo:s200 assunto:s500 descricao:text? prioridade:s40? status_id:@status_processo unidade_origem_id:@unidades setor_origem_id:@setores aberto_por:@vinculos_organizacao aberto_em:dt encerrado_em:dt?', chaves_unicas=[['organizacao_id', 'numero_completo'], ['organizacao_id', 'tipo_processo_id', 'ano', 'numero_sequencial']])
definir_tabela('processo_valores_campo', 'processo_id:@processos campo_configuracao_id:@tipo_processo_campos valor_texto:text? valor_numero:decimal? valor_data:dt? valor_booleano:bool?', chaves_unicas=[['organizacao_id', 'processo_id', 'campo_configuracao_id']])
definir_tabela('processo_valores_opcao', 'processo_valor_id:@processo_valores_campo opcao_id:@opcoes_campo', chaves_unicas=[['organizacao_id', 'processo_valor_id', 'opcao_id']])
definir_tabela('interessados', 'tipo_pessoa:s40 nome:s200 email:s254? telefone:s40?')
definir_tabela('interessado_identificadores', 'interessado_id:@interessados tipo:s40 valor:s100 pais:s2')
definir_tabela('processo_interessados', 'processo_id:@processos interessado_id:@interessados papel:s100')
definir_tabela('processo_relacoes', 'processo_origem_id:@processos processo_destino_id:@processos tipo_relacao:s80 justificativa:text? criado_por:@vinculos_organizacao', verificacoes=['processo_origem_id <> processo_destino_id'])
definir_tabela('processo_eventos_estado', 'processo_id:@processos estado_anterior_id:@status_processo? estado_novo_id:@status_processo motivo:text? autor_id:@vinculos_organizacao ocorrido_em:dt')
definir_tabela('modelos_documento', 'tipo_documento_id:@tipos_documento nome:s200 ativo:bool')
definir_tabela('modelo_documento_versoes', 'modelo_id:@modelos_documento versao:uint conteudo:longtext variaveis:json? publicado_em:dt? criado_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'modelo_id', 'versao']])
definir_tabela('textos_padrao', 'setor_id:@setores? proprietario_id:@vinculos_organizacao? titulo:s200 conteudo:longtext visibilidade:s40')
definir_tabela('documentos', 'tipo_documento_id:@tipos_documento tipo_documento_versao_id:@tipo_documento_versoes processo_principal_id:@processos? titulo:s500 origem:s40 status_id:@status_documento unidade_origem_id:@unidades setor_origem_id:@setores versao_atual_id:@documento_versoes? regra_numeracao_id:@regras_numeracao_documento? numero_sequencial:uint? periodo_numeracao:s20? numero_completo:s200? criado_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'numero_completo'], ['organizacao_id', 'tipo_documento_id', 'periodo_numeracao', 'numero_sequencial']])
definir_tabela('documento_versoes', 'documento_id:@documentos numero_versao:uint modelo_versao_id:@modelo_documento_versoes? conteudo_editor:longtext? arquivo_original_id:@arquivos? arquivo_final_id:@arquivos? hash_conteudo:s128? algoritmo_hash:s30? status_id:@status_documento criada_por:@vinculos_organizacao finalizada_em:dt?', chaves_unicas=[['organizacao_id', 'documento_id', 'numero_versao']])
definir_tabela('documento_valores_campo', 'documento_id:@documentos campo_configuracao_id:@tipo_documento_campos valor_texto:text? valor_numero:decimal? valor_data:dt? valor_booleano:bool?', chaves_unicas=[['organizacao_id', 'documento_id', 'campo_configuracao_id']])
definir_tabela('documento_valores_opcao', 'documento_valor_id:@documento_valores_campo opcao_id:@opcoes_campo', chaves_unicas=[['organizacao_id', 'documento_valor_id', 'opcao_id']])
definir_tabela('processo_documento_referencias', 'processo_id:@processos documento_id:@documentos versao_referenciada_id:@documento_versoes? criado_por:@vinculos_organizacao')
definir_tabela('documento_eventos_estado', 'documento_id:@documentos versao_id:@documento_versoes? status_anterior_id:@status_documento? status_novo_id:@status_documento? evento:s80 motivo:text autor_id:@vinculos_organizacao ocorrido_em:dt')
definir_tabela('tramitacoes', 'processo_id:@processos? documento_id:@documentos? unidade_origem_id:@unidades setor_origem_id:@setores remetente_id:@vinculos_organizacao finalidade:s200? mensagem:text? enviada_em:dt', verificacoes=['(processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1'])
definir_tabela('tramitacao_destinatarios', 'tramitacao_id:@tramitacoes unidade_id:@unidades setor_id:@setores destinatario_usuario_id:@vinculos_organizacao? modalidade:s40 prazo_em:dt? status:s40 recebido_por:@vinculos_organizacao? recebido_em:dt? concluido_em:dt?')
definir_tabela('tramitacao_eventos', 'destinatario_id:@tramitacao_destinatarios tipo_evento:s80 autor_id:@vinculos_organizacao ocorrido_em:dt motivo:text?')
definir_tabela('manifestacoes', 'destinatario_id:@tramitacao_destinatarios autor_id:@vinculos_organizacao texto:text? documento_resposta_id:@documentos?')
definir_tabela('atribuicoes_responsabilidade', 'processo_id:@processos? documento_id:@documentos? destinatario_id:@tramitacao_destinatarios? responsavel_id:@vinculos_organizacao atribuido_por:@vinculos_organizacao inicio_em:dt fim_em:dt?', verificacoes=['(processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1'])
definir_tabela('politicas_acesso', 'processo_id:@processos? documento_id:@documentos? nivel:s40 herda_processo:bool alterada_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'processo_id'], ['organizacao_id', 'documento_id']], verificacoes=['(processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1'])
definir_tabela('concessoes_acesso', 'politica_id:@politicas_acesso vinculo_id:@vinculos_organizacao? setor_id:@setores? permite_visualizar:bool permite_download:bool permite_editar:bool permite_tramitar:bool inicio_em:dt expira_em:dt? revogado_em:dt? concedido_por:@vinculos_organizacao motivo:text?', verificacoes=['(vinculo_id IS NOT NULL) + (setor_id IS NOT NULL) = 1'])
definir_tabela('historico_acesso', 'politica_id:@politicas_acesso evento:s80 autor_id:@vinculos_organizacao ocorrido_em:dt dados_alteracao:json?')
definir_tabela('blocos_assinatura', 'nome:s200 descricao:text? setor_criador_id:@setores criado_por:@vinculos_organizacao status:s40')
definir_tabela('bloco_documentos', 'bloco_id:@blocos_assinatura documento_versao_id:@documento_versoes ordem:int incluido_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'bloco_id', 'documento_versao_id']])
definir_tabela('solicitacoes_assinatura', 'documento_versao_id:@documento_versoes bloco_id:@blocos_assinatura? signatario_id:@vinculos_organizacao ordem:int? obrigatoria:bool prazo_em:dt? status:s40 solicitada_por:@vinculos_organizacao')
definir_tabela('confirmacoes_assinatura', 'solicitacao_id:@solicitacoes_assinatura metodo:s40 desafio_hash:s128? expira_em:dt? confirmado_em:dt? tentativas:uint')
definir_tabela('assinaturas', 'solicitacao_id:@solicitacoes_assinatura documento_versao_id:@documento_versoes signatario_id:@vinculos_organizacao nome_registrado:s200 cargo_registrado:s150? metodo:s40 hash_assinado:s128 algoritmo_hash:s30 assinada_em:dt ip:s45 agente_usuario:text?', chaves_unicas=[['organizacao_id', 'solicitacao_id']])
definir_tabela('verificacoes_documento', 'documento_versao_id:@documento_versoes codigo_verificacao:s100 politica_exibicao:json?', chaves_unicas=[['codigo_verificacao']])
definir_tabela('pastas', 'pasta_pai_id:@pastas? nome:s200 proprietario_id:@vinculos_organizacao? setor_id:@setores? visibilidade:s40 criada_por:@vinculos_organizacao', verificacoes=['(proprietario_id IS NOT NULL) + (setor_id IS NOT NULL) = 1'])
definir_tabela('pasta_compartilhamentos', 'pasta_id:@pastas vinculo_id:@vinculos_organizacao? setor_id:@setores? permite_gerenciar:bool', verificacoes=['(vinculo_id IS NOT NULL) + (setor_id IS NOT NULL) = 1'])
definir_tabela('pasta_processos', 'pasta_id:@pastas processo_id:@processos incluido_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'pasta_id', 'processo_id']])
definir_tabela('pasta_documentos', 'pasta_id:@pastas documento_id:@documentos incluido_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'pasta_id', 'documento_id']])
definir_tabela('etiquetas', 'nome:s100 cor:s20? escopo:s40 proprietario_id:@vinculos_organizacao? setor_id:@setores?')
definir_tabela('processo_etiquetas', 'processo_id:@processos etiqueta_id:@etiquetas aplicado_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'processo_id', 'etiqueta_id']])
definir_tabela('documento_etiquetas', 'documento_id:@documentos etiqueta_id:@etiquetas aplicado_por:@vinculos_organizacao', chaves_unicas=[['organizacao_id', 'documento_id', 'etiqueta_id']])
definir_tabela('acompanhamentos', 'vinculo_id:@vinculos_organizacao processo_id:@processos? documento_id:@documentos? preferencias:json?', verificacoes=['(processo_id IS NOT NULL) + (documento_id IS NOT NULL) = 1'])
definir_tabela('convites_externos', 'email:s254 token_hash:s64 enviado_por:@vinculos_organizacao expira_em:dt aceito_em:dt? revogado_em:dt?', chaves_unicas=[['token_hash']])
definir_tabela('autorizacoes_externas', 'vinculo_externo_id:@vinculos_organizacao concedida_por:@vinculos_organizacao inicio_em:dt expira_em:dt revogada_em:dt? motivo:text?')
definir_tabela('autorizacao_processos', 'autorizacao_id:@autorizacoes_externas processo_id:@processos incluir_documentos_futuros:bool permite_download:bool', chaves_unicas=[['organizacao_id', 'autorizacao_id', 'processo_id']])
definir_tabela('autorizacao_documentos', 'autorizacao_id:@autorizacoes_externas documento_id:@documentos versao_id:@documento_versoes autorizacao_processo_id:@autorizacao_processos? origem_concessao:s40 permite_download:bool', chaves_unicas=[['organizacao_id', 'autorizacao_id', 'versao_id']])
definir_tabela('acessos_externos', 'autorizacao_id:@autorizacoes_externas documento_id:@documentos? processo_id:@processos? versao_id:@documento_versoes? acao:s80 ocorrido_em:dt ip:s45?')
definir_tabela('prazos', 'processo_id:@processos? documento_id:@documentos? destinatario_id:@tramitacao_destinatarios? descricao:s255 vence_em:dt status:s40 concluido_em:dt?', verificacoes=['(processo_id IS NOT NULL) + (documento_id IS NOT NULL) + (destinatario_id IS NOT NULL) = 1'])
definir_tabela('prazo_eventos', 'prazo_id:@prazos evento:s80 vencimento_anterior:dt? novo_vencimento:dt? motivo:text autor_id:@vinculos_organizacao')
definir_tabela('notificacoes', 'destinatario_id:@vinculos_organizacao tipo:s80 titulo:s255 referencia:json? processo_id:@processos? documento_id:@documentos? lida_em:dt?')
definir_tabela('notificacao_entregas', 'notificacao_id:@notificacoes canal:s40 status:s40 tentativas:uint enviada_em:dt? erro:text?')
definir_tabela('historico_eventos', 'processo_id:@processos? documento_id:@documentos? tipo:s80 autor_id:@vinculos_organizacao? ocorrido_em:dt detalhes:json? visibilidade:s40')
definir_tabela('auditoria_eventos', 'usuario_id:@usuarios? vinculo_id:@vinculos_organizacao? acao:s120 entidade:s100 entidade_id:uint? ocorrido_em:dt ip:s45? detalhes:json?')
definir_tabela('planos', 'nome:s150 status:s40', tabela_global=True)
definir_tabela('plano_versoes', 'plano_id:@planos preco:money moeda:s3 periodicidade:s40 limites:json vigente_desde:dt vigente_ate:dt?', tabela_global=True)
definir_tabela('contratacoes', 'plano_versao_id:@plano_versoes status:s40 inicio_em:dt renovacao_em:dt? encerramento_em:dt?')
definir_tabela('adicionais_contratados', 'contratacao_id:@contratacoes tipo:s80 quantidade:uint preco:money moeda:s3 vigente_desde:dt vigente_ate:dt?')
definir_tabela('cobrancas', 'contratacao_id:@contratacoes referencia:s100 valor:money moeda:s3 vencimento:date status:s40 provedor:s80? identificador_provedor:s200?')
definir_tabela('consumo_armazenamento', 'categoria:s80 bytes_utilizados:uint apurado_em:dt', chaves_unicas=[['organizacao_id', 'categoria']])
definir_tabela('movimentos_armazenamento', 'arquivo_id:@arquivos variacao_bytes:int motivo:s100 ocorrido_em:dt')
definir_tabela('ia_configuracoes', 'habilitada:bool recursos_permitidos:json? politica_conteudo:json? limite_consumo:json?', chaves_unicas=[['organizacao_id']])
definir_tabela('ia_execucoes', 'solicitante_id:@vinculos_organizacao recurso:s80 status:s40 provedor:s100? modelo:s150? versao_instrucao:s80? iniciada_em:dt concluida_em:dt? erro:text?')
definir_tabela('ia_fontes', 'execucao_id:@ia_execucoes documento_versao_id:@documento_versoes? evento_historico_id:@historico_eventos? trecho_referencia:text?', verificacoes=['(documento_versao_id IS NOT NULL) + (evento_historico_id IS NOT NULL) = 1'])
definir_tabela('ia_resultados', 'execucao_id:@ia_execucoes conteudo:longtext status_revisao:s40? revisado_por:@vinculos_organizacao? revisado_em:dt?')
definir_tabela('ia_consumo', 'execucao_id:@ia_execucoes unidades_entrada:uint? unidades_saida:uint? paginas:uint? custo_estimado:decimal? moeda:s3?')
definir_tabela('documento_extracoes', 'documento_versao_id:@documento_versoes texto_extraido:longtext? metodo:s80 status:s40')
definir_tabela('indice_documental', 'documento_versao_id:@documento_versoes referencia_indice:s255 versao_indexacao:s80 status:s40')

def converter_php(valor):
    """Converte valores Python para a sintaxe literal do PHP."""
    if isinstance(valor, bool):
        return 'true' if valor else 'false'
    if isinstance(valor, str):
        return "'" + valor.replace('\\', '\\\\').replace("'", "\\'") + "'"
    if isinstance(valor, list):
        return '[' + ', '.join(map(converter_php, valor)) + ']'
    if isinstance(valor, dict):
        return '[' + ', '.join((converter_php(chave) + ' => ' + converter_php(valor_item) for chave, valor_item in valor.items())) + ']'
    return str(valor)

def definir_campo(tipo, aceita_nulo):
    """Converte o tipo resumido para uma definição do Database Forge."""
    if tipo == 'id' or tipo.startswith('@') or tipo == 'uint':
        resultado = dict(type='BIGINT', unsigned=True)
        if tipo == 'id':
            resultado['auto_increment'] = True
    elif tipo.startswith('s') and tipo[1:].isdigit():
        resultado = dict(type='VARCHAR', constraint=int(tipo[1:]))
    elif tipo in ('decimal', 'money'):
        resultado = dict(type='DECIMAL', constraint='24,6' if tipo == 'decimal' else '19,2')
    elif tipo == 'bool':
        resultado = dict(type='TINYINT', constraint=1, unsigned=True)
    else:
        resultado = dict(type={'dt': 'DATETIME', 'date': 'DATE', 'text': 'TEXT', 'longtext': 'LONGTEXT', 'json': 'JSON', 'int': 'BIGINT', 'year': 'SMALLINT'}[tipo])
        if tipo == 'year':
            resultado['unsigned'] = True
    resultado['null'] = aceita_nulo
    return resultado

def nome_curto(prefixo, *partes):
    """Gera nomes estáveis e curtos para índices e restrições."""
    return prefixo + '_' + hashlib.sha256('|'.join(partes).encode()).hexdigest()[:20]
nomes = list(tabelas)
posicoes = {nome: indice for indice, nome in enumerate(nomes)}
relacionamentos = []
for origem, dados in tabelas.items():
    for coluna, (tipo, aceita_nulo) in dados['fields'].items():
        if not tipo.startswith('@'):
            continue
        destino = tipo[1:]
        assert destino in tabelas, (origem, coluna, destino)
        colunas_locais = ['organizacao_id', coluna] if dados['tenant'] and tabelas[destino]['tenant'] else [coluna]
        colunas_referenciadas = ['organizacao_id', 'id'] if len(colunas_locais) == 2 else ['id']
        relacionamentos.append((origem, colunas_locais, destino, colunas_referenciadas, nome_curto('fk', origem, coluna)))

def adicionar_relacionamento(origem, colunas_locais, destino, colunas_referenciadas):
    """Garante compatibilidade entre os registros relacionados."""
    tabelas[destino]['unique'].append(colunas_referenciadas)
    relacionamentos.append((origem, colunas_locais, destino, colunas_referenciadas, nome_curto('fk', origem, *colunas_locais, destino)))
adicionar_relacionamento('setores', ['organizacao_id', 'unidade_id', 'setor_superior_id'], 'setores', ['organizacao_id', 'unidade_id', 'id'])
for origem, unidade, setor in [('processos', 'unidade_origem_id', 'setor_origem_id'), ('documentos', 'unidade_origem_id', 'setor_origem_id'), ('tramitacoes', 'unidade_origem_id', 'setor_origem_id'), ('tramitacao_destinatarios', 'unidade_id', 'setor_id')]:
    adicionar_relacionamento(origem, ['organizacao_id', unidade, setor], 'setores', ['organizacao_id', 'unidade_id', 'id'])
adicionar_relacionamento('processos', ['organizacao_id', 'tipo_processo_id', 'tipo_processo_versao_id'], 'tipo_processo_versoes', ['organizacao_id', 'tipo_processo_id', 'id'])
adicionar_relacionamento('processos', ['organizacao_id', 'tipo_processo_id', 'regra_numeracao_id'], 'regras_numeracao_processo', ['organizacao_id', 'tipo_processo_id', 'id'])
adicionar_relacionamento('documentos', ['organizacao_id', 'tipo_documento_id', 'tipo_documento_versao_id'], 'tipo_documento_versoes', ['organizacao_id', 'tipo_documento_id', 'id'])
adicionar_relacionamento('documentos', ['organizacao_id', 'tipo_documento_id', 'regra_numeracao_id'], 'regras_numeracao_documento', ['organizacao_id', 'tipo_documento_id', 'id'])
adicionar_relacionamento('documentos', ['organizacao_id', 'id', 'versao_atual_id'], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'])
for origem, coluna_indice in [('processo_documento_referencias', 'versao_referenciada_id'), ('autorizacao_documentos', 'versao_id'), ('documento_eventos_estado', 'versao_id')]:
    adicionar_relacionamento(origem, ['organizacao_id', 'documento_id', coluna_indice], 'documento_versoes', ['organizacao_id', 'documento_id', 'id'])
adicionar_relacionamento('assinaturas', ['organizacao_id', 'solicitacao_id', 'documento_versao_id', 'signatario_id'], 'solicitacoes_assinatura', ['organizacao_id', 'id', 'documento_versao_id', 'signatario_id'])
for nome, dados in tabelas.items():
    indice = posicoes[nome]
    nome_classe = 'Create' + ''.join((parte.title() for parte in nome.split('_')))
    linhas = ['<?php', '', 'namespace App\\Database\\Migrations;', '', 'use CodeIgniter\\Database\\Migration;', '', f'class {nome_classe} extends Migration', '{', '    /** Cria a tabela e suas restrições; nome exigido pelo CI4. */', '    public function up()', '    {', '        $this->forge->addField([']
    for coluna, (tipo, aceita_nulo) in dados['fields'].items():
        linhas.append(f'            {converter_php(coluna)} => {converter_php(definir_campo(tipo, aceita_nulo))},')
    linhas += ['        ]);', "        $this->forge->addPrimaryKey('id');"]
    chaves = ([['organizacao_id', 'id']] if dados['tenant'] else []) + dados['unique']
    for colunas in dict.fromkeys((tuple(chave) for chave in chaves)):
        linhas.append(f"        $this->forge->addUniqueKey({converter_php(list(colunas))}, {converter_php(nome_curto('uq', nome, *colunas))});")
    for coluna_indice in ('status_id', 'status', 'ocorrido_em', 'vence_em', 'expira_em', 'numero_completo'):
        if coluna_indice in dados['fields']:
            colunas = (['organizacao_id'] if dados['tenant'] else []) + [coluna_indice]
            linhas.append(f"        $this->forge->addKey({converter_php(colunas)}, false, false, {converter_php(nome_curto('ix', nome, coluna_indice))});")
    linhas.append(f"        $this->forge->createTable('{nome}', false, ['ENGINE' => 'InnoDB']);")
    for indice_verificacao, verificacao in enumerate(dados['checks']):
        sufixo = f"` ADD CONSTRAINT `{nome_curto('ck', nome, str(indice_verificacao))}` CHECK ({verificacao})"
        linhas.append(f"        $this->db->query('ALTER TABLE `' . $this->db->prefixTable('{nome}') . {converter_php(sufixo)});")
    relacionamentos_ativados = [relacionamento for relacionamento in relacionamentos if max(posicoes[relacionamento[0]], posicoes[relacionamento[2]]) == indice]
    for origem, colunas_locais, destino, colunas_referenciadas, nome_chave_estrangeira in relacionamentos_ativados:
        linhas.append(f"        $this->forge->addForeignKey({converter_php(colunas_locais)}, '{destino}', {converter_php(colunas_referenciadas)}, 'RESTRICT', 'RESTRICT', '{nome_chave_estrangeira}');")
        linhas.append(f"        $this->forge->processIndexes('{origem}');")
    linhas += ['    }', '', '    /** Reverte a tabela e suas restrições; nome exigido pelo CI4. */', '    public function down()', '    {']
    for origem, colunas_locais, destino, colunas_referenciadas, nome_chave_estrangeira in reversed(relacionamentos_ativados):
        linhas.append(f"        $this->forge->dropForeignKey('{origem}', '{nome_chave_estrangeira}');")
    linhas += [f"        $this->forge->dropTable('{nome}');", '    }', '}', '']
    instante = (datetime(2026, 11, 2, 22) + timedelta(seconds=indice)).strftime('%Y-%m-%d-%H%M%S')
    (DIRETORIO_SAIDA / f'{instante}_{nome_classe}.php').write_text('\n'.join(linhas), encoding='utf-8')
print(f'Geradas {len(tabelas)} migrations e {len(relacionamentos)} chaves estrangeiras.')
