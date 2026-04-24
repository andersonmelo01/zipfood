# Documentação do Sistema - ZipFood

## Visão Geral
Sistema de pedidos online para delivery, com painel administrativo, acompanhamento de pedidos, controle de produtos e feedbacks. Marca ZipFood.

## Estrutura de Pastas
- `index.php` — Cardápio e carrinho do cliente
- `salvar_pedido.php` — Recebe e grava pedidos
- `acompanhar.php` — Acompanhamento do pedido pelo cliente
- `dashboard.php` — Painel administrativo
- `config.json` — Configurações dinâmicas (taxa, loja fechada, etc)
- `app/` — Helpers, bootstrap e schema
- `assets/` — CSS e JS
- `sql/` — Scripts de banco de dados

## Fluxo do Pedido
1. Cliente monta pedido no cardápio (`index.php`)
2. Pedido é salvo via `salvar_pedido.php`
3. Cliente acompanha status em `acompanhar.php`
4. Admin gerencia pedidos em `dashboard.php`

## Configurações
- Taxa de entrega e status da loja são definidos em `dashboard.php` e salvos em `config.json`.
- Alterações são refletidas em tempo real para o cliente.
- **Controle de Licença:**
	- O campo `validade` em `emitente.json` define até quando o sistema pode ser utilizado.
	- Quando faltar 5 dias ou menos para expirar, um aviso é exibido no dashboard.
	- Se expirar, o login administrativo é bloqueado e a loja é automaticamente fechada.
	- Para atualizar a validade/licença, acesse `licenca.php` (requer usuário: admin, senha: And95079@).

## Segurança
- **Validação de dados**: Todos os dados recebidos via POST são validados e tratados.
- **Prepared Statements**: Todas as queries usam prepared statements para evitar SQL Injection.
- **Painel Admin**: Protegido por autenticação (verifique `require_admin()` em arquivos sensíveis).
- **XSS**: Saída de dados com função `e()` (htmlspecialchars).
- **Arquivos sensíveis**: Não exponha arquivos como `config.php` e `config.json` publicamente.
- **Permissões**: Recomenda-se permissões 755 para pastas e 644 para arquivos.
- **Uploads**: Se ativar upload de imagens, valide tipo e tamanho.

## Checklist de Segurança
- [x] Prepared Statements em todas as queries
- [x] Validação de campos obrigatórios
- [x] Escape de saída HTML
- [x] Autenticação no painel admin
- [x] Configuração de permissões adequada
- [ ] HTTPS habilitado no servidor
- [ ] Backup regular do banco de dados

## Recomendações
- Ative HTTPS no Apache para proteger dados dos clientes.
- Altere senhas padrão e mantenha-as seguras.
- Faça backup regular do banco de dados e arquivos.
- Mantenha o sistema e dependências atualizados.

## Suporte
- Consulte o README ou abra uma issue no GitHub para dúvidas.
