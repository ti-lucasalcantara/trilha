# Migrations do Trilha

95 migrations independentes, uma para cada tabela. Não dependem de classes auxiliares do projeto.

## Execução

Configure a conexão MySQLi do CI4 para o MariaDB no ambiente local. Execute na raiz:

```
php spark migrate:status
php spark migrate
```

`php spark migrate:rollback` reverte o último lote e remove suas tabelas e dados.
Não use rollback em um banco com documentos que precisam ser preservados sem um plano de recuperação.

As migrations foram verificadas com PHP 8.3.14, CI4 4.7.6 e MariaDB 11.5.2.
Não há seeds de usuários, perfis, status, preços ou credenciais.

## Convenções

- IDs BIGINT UNSIGNED; campos de data em DATETIME, sem conversão automática de fuso.
- Datas devem ser enviadas pela aplicação em UTC. O ano de numeração usa o fuso da organização.
- `criado_em` obrigatório e `atualizado_em` opcional; sem timestamps ou status implícitos.
- Dados de negócio têm `organizacao_id`, FK para organizações e chave única `(organizacao_id, id)`.
- Referências entre dados de clientes usam FKs compostas para impedir associação entre organizações.
- FKs usam RESTRICT: não há exclusão em cascata de dados históricos.
- `usuarios`, `sessoes_usuario`, `permissoes`, `planos` e `plano_versoes` são globais.
- IDs de pessoas nas operações apontam para `vinculos_organizacao`, salvo campos explicitamente globais.
- Status de processos e documentos são tabelas cadastráveis por organização; não há ENUM ou catálogo inicial.
- Status técnicos e eventos são campos textuais sem catálogo ou transições pré-impostos.
- Cada tabela possui `id`, inclusive associações; chaves únicas evitam duplicações onde definido.
- JSON é usado para configurações variáveis; não substitui os vínculos estruturais com FK.

## Numeração

Processos: regras versionadas por organização/tipo; sequência única por organização/tipo/ano.
O número sequencial, o ano, a regra e o texto completo emitido ficam no processo.

Documentos: regras versionadas por organização/tipo, máscara, sigla, dígitos e opção explícita de reinício anual.
O contador usa `periodo` e o documento guarda `periodo_numeracao`; a aplicação deverá padronizar
essa chave conforme a configuração, sem misturar contadores anuais e contínuos.
Os campos numéricos podem ficar nulos enquanto não houver emissão; não há numeração automática no banco.

Tokens planejados para as máscaras: `{SIGLA}`, `{NUMERO}`, `{ANO}`.
A emissão transacional, a concorrência sobre contadores e a proibição de reutilizar números devem ser
implementadas na aplicação. Mudar a máscara não deve reescrever registros emitidos.

## Dependências circulares

`documentos.versao_atual_id` referencia uma versão do próprio documento. Essas FKs são adicionadas
na migration de `documento_versoes`, depois que ambas as tabelas existem, e removidas no seu `down()`.
Cada migration continua criando somente uma tabela. FKs adicionais verificam unidade/setor,
tipo/versão/regra, documento/versão e solicitação/signatário/versão.

## Acesso externo

`autorizacao_documentos.versao_id` é obrigatório e sua FK garante que a versão pertence ao documento.
Compartilhamento de processos registra se inclui futuros documentos, sem valor padrão implícito.
A aplicação deverá materializar os documentos atuais na concessão, confirmar o e-mail, verificar
expiração/revogação em cada acesso e respeitar restrições ao adicionar documentos futuros.

## Regras que o esquema não executa sozinho

- Autorização de leitura por organização/pessoa/setor, incluindo consultas, downloads e notificações.
- Associação ativa de pessoas aos setores, hierarquia sem ciclos e compatibilidade dos campos personalizados.
- Transições de status, recebimentos e conclusão com destinatários pendentes.
- Imutabilidade de versões assinadas, eventos e auditoria; descarte controlado de arquivos.
- Compartilhar pasta ou referência sem ampliar acesso ao conteúdo.
- Publicação pública, método de confirmação de assinatura, retenção e numeração inicial não têm padrão imposto.
- Cálculo de consumo, cobrança, limites e permissões das fontes/resultados de IA.

## Ferramentas de desenvolvimento

`tools/gerar_migrations.py` contém a especificação e gera os arquivos PHP. Não execute novamente
depois de aplicar migrations em ambientes persistentes: mudanças futuras devem ter novas migrations.

`tools/verificar_migrations.php` usa exclusivamente uma instância descartável local na porta 33319,
schema `trilha_migrations_check`, sem ler as credenciais da conexão do projeto.
Esse schema é criado e removido pelo teste. Ele valida aplicação, FK entre organizações,
rollback e reaplicação; não é um teste de regras de negócio.
