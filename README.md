# ZipFood

Sistema de delivery em PHP com MySQL, painel administrativo, acompanhamento de pedidos e controle básico de produtos. Marca ZipFood.

## Requisitos

- PHP 8.1 ou superior
- MySQL/MariaDB
- Apache ou outro servidor compatível
- Extensões PHP: `PDO`, `pdo_mysql`

## Configuração

O projeto usa os valores de `config.php` como padrão e permite sobrescrita por variáveis de ambiente.

### Banco de dados

- Host: `localhost`
- Nome: `zipfood`
- Usuário: `root`
- Senha: `admin`

### Acesso mestre

Se nenhuma variável de ambiente for definida, o acesso administrativo padrão é:

- Usuário: `andersonmelo01@gmail.com`
- Senha: `123456`

Essas credenciais podem ser alteradas por:

- `ADMIN_USER`
- `ADMIN_PASSWORD`
- `ADMIN_PASSWORD_HASH`

Se `ADMIN_PASSWORD_HASH` estiver definido, a autenticação passa a validar o hash em vez da senha em texto puro.

## Estrutura do projeto

- `index.php`: vitrine / início do fluxo do pedido
- `admin.php`: login administrativo
- `dashboard.php`: painel administrativo
- `pedidos.php`: listagem de pedidos
- `financeiro.php`: visão financeira
- `feedbacks.php`: listagem de feedbacks
- `painel.php`: área administrativa auxiliar
- `app/bootstrap.php`: inicialização da aplicação
- `app/helpers.php`: funções utilitárias
- `app/schema.php`: criação automática das tabelas
- `conexao.php`: conexão com o banco
- `config.php`: configuração padrão do projeto
- `config.json`: configuração operacional da loja

## Tabelas do banco

O sistema trabalha com estas tabelas:

- `produtos`
- `pedidos`
- `pedido_itens`

## Reset do banco

Para apagar a estrutura atual e recriar tudo do zero, execute o arquivo:

- `sql/reset_estrutura.sql`

Esse script remove as tabelas na ordem correta e recria toda a estrutura do banco.

## Observações

- A estrutura também é garantida em tempo de execução por `app/schema.php`.
- O arquivo `gerar_hash.php` pode ser usado para gerar um hash seguro de senha administrativa.
- O arquivo `config.json` guarda parâmetros dinâmicos da operação, como taxa de entrega e status da loja.

