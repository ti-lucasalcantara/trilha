# Trilha

O Trilha organiza processos, documentos e pastas por organização e setor. A área da organização reúne a Caixa de trabalho, o acompanhamento de processos e os cadastros de estrutura, equipe, perfis e tipos.

## Ambiente local

- PHP 8.2 ou superior com `intl`, `mbstring` e `mysqli`. Os testes de banco usam também `sqlite3`.
- Composer e um banco MySQL ou MariaDB dedicado ao ambiente.
- A raiz pública do servidor web deve apontar para `public/`.

Instale as dependências com `composer install`. Copie `env` para `.env` e defina `app.baseURL` com a URL local terminada em barra. Configure a conexão usando as chaves `database.default.*` do arquivo de exemplo.

Com o banco local vazio e configurado, rode `php spark migrate`. Para usar o servidor de desenvolvimento do CodeIgniter, execute `php spark serve` e configure a URL base de acordo com o endereço exibido.

A área `/admin` serve para o cadastro e a simulação local de organizações. O acesso normal de usuários internos começa em `/login` e requer um vínculo ativo. O painel administrativo tem controle de acesso local provisório.

## Verificações

- `composer test` executa os testes do projeto.
- `php spark routes` lista as rotas registradas.
- O [guia de migrations](app/Database/Migrations/README.md) descreve as regras do esquema e o verificador com banco descartável.

## Recursos em desenvolvimento

O financeiro administrativo usa dados demonstrativos. O esquema prevê assinaturas, acesso externo e outros fluxos cujas regras de negócio ainda precisam ser definidas ou implementadas no aplicativo.
