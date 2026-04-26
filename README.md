# ZipFood

Sistema de delivery em PHP com MySQL, painel administrativo, controle de produtos, pedidos, financeiro, feedbacks e usuarios com perfis de acesso.

## Requisitos

- PHP 8.1 ou superior
- MySQL 8+ ou MariaDB 10.5+
- Apache com `mod_rewrite`
- Extensoes PHP: `PDO`, `pdo_mysql`, `mbstring`, `fileinfo`

## Perfis de acesso

- `Administrador`: acesso completo a todos os modulos
- `Gerente`: acesso a `produtos`, `pedidos` e `financeiro`
- `Vendedor`: acesso apenas a `pedidos`

## Usuario administrativo padrao

Na primeira execucao, o sistema cria automaticamente um usuario administrador padrao:

- Usuario: `admin`
- Senha: `Admin@123`

Altere a senha logo no primeiro acesso pela tela de login, na opcao `Alterar senha`.

Se desejar personalizar a criacao inicial do admin, use estas variaveis de ambiente antes da primeira carga do sistema:

- `ADMIN_USER`
- `ADMIN_PASSWORD`
- `ADMIN_PASSWORD_HASH`

## Estrutura principal

- `index.php`: cardapio publico
- `admin.php`: login e alteracao de senha
- `dashboard.php`: painel conforme o perfil
- `usuarios.php`: cadastro e gestao de usuarios
- `painel.php`: produtos
- `pedidos.php`: operacao de pedidos
- `financeiro.php`: relatorios financeiros
- `feedbacks.php`: moderacao de avaliacoes
- `config_emitente.php`: dados do estabelecimento
- `licenca.php`: manutencao da licenca

## Banco de dados

As tabelas sao criadas automaticamente em tempo de execucao por `app/schema.php`.

Estrutura principal:

- `usuarios`
- `produtos`
- `pedidos`
- `pedido_itens`
- `feedbacks`

Para recriar a estrutura manualmente, use `sql/reset_estrutura.sql`.

## Seguranca aplicada

- Senhas armazenadas com `password_hash`
- Controle de acesso por perfil e modulo
- Sessao com `httponly`, `samesite=lax` e `session.use_strict_mode`
- Upload de imagens com validacao de extensao, MIME e tamanho
- `.htaccess` para bloquear arquivos sensiveis e listagem de diretorio

## Configuracao

O projeto le `config.php` e permite sobrescrever valores por variaveis de ambiente.

Chaves principais:

- `APP_ENV`
- `APP_DEBUG`
- `DB_HOST`
- `DB_NAME`
- `DB_USER`
- `DB_PASS`
- `ADMIN_USER`
- `ADMIN_PASSWORD`
- `ADMIN_PASSWORD_HASH`

## Instalacao

O passo a passo completo para VPS Ubuntu esta em [MANUAL_INSTALACAO_UBUNTU.md](/z:/xampp/htdocs/delivery/MANUAL_INSTALACAO_UBUNTU.md).
