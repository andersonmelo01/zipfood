# Documentacao do Sistema - ZipFood

## Visao geral

O ZipFood e um sistema web de delivery com frente publica para pedidos e back-office com controle por usuario.

## Fluxos principais

1. O cliente monta o pedido em `index.php`
2. O pedido e salvo em `salvar_pedido.php`
3. O cliente acompanha o status em `acompanhar.php`
4. A equipe opera o pedido em `pedidos.php`
5. O financeiro consolida vendas em `financeiro.php`

## Autenticacao e permissoes

O login agora e baseado na tabela `usuarios`.

Perfis:

- `admin`: acesso total
- `gerente`: `produtos`, `pedidos`, `financeiro`
- `vendedor`: `pedidos`

Regras importantes:

- O cadastro e manutencao de usuarios fica em `usuarios.php`
- A alteracao de senha e feita na tela `admin.php`, exigindo a senha atual
- Se um usuario for desativado, o acesso e bloqueado automaticamente
- O sistema garante pelo menos um administrador ativo

## Licenca

- A validade fica em `emitente.json`
- Quando a licenca expira, a loja e fechada automaticamente
- Com licenca expirada, apenas o administrador pode entrar para acessar `dashboard.php`, `config_emitente.php` e `licenca.php`

## Arquivos de configuracao

- `config.php`: configuracao base do projeto
- `config.json`: configuracoes operacionais da loja
- `emitente.json`: dados do estabelecimento e validade da licenca

## Estrutura do banco

### usuarios

- `nome`
- `usuario`
- `senha_hash`
- `papel`
- `ativo`
- `ultimo_login_em`

### produtos

Cadastro do cardapio, precos, promocao, imagem e tributacao.

### pedidos

Cabecalho do pedido: cliente, endereco, pagamento, total, status e datas.

### pedido_itens

Itens vinculados aos pedidos.

### feedbacks

Avaliacoes enviadas pelos clientes com moderacao.

## Operacao de producao

- Troque a senha do admin padrao no primeiro acesso
- Desative `APP_DEBUG` em producao
- Use HTTPS no servidor
- Garanta backup do banco e dos arquivos `config.json`, `emitente.json` e `img/`
- Restrinja permissoes de escrita ao necessario

## Ajustes de seguranca implementados

- Upload de imagem validado
- Sessao endurecida
- Controle de acesso centralizado
- Bloqueio de arquivos sensiveis via `.htaccess`
