# Manual de Instalacao - ZipFood em VPS Ubuntu

## 1. Preparar o servidor

```bash
sudo apt update && sudo apt upgrade -y
sudo apt install apache2 mysql-server unzip git -y
sudo apt install php php-mysql php-mbstring php-xml php-curl php-zip php-cli php-common php-opcache -y
```

## 2. Habilitar modulos do Apache

```bash
sudo a2enmod rewrite headers expires
sudo systemctl restart apache2
```

## 3. Criar banco e usuario no MySQL

```bash
sudo mysql
CREATE DATABASE zipfood CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'zipfood'@'localhost' IDENTIFIED BY 'TroqueEssaSenhaForte';
GRANT ALL PRIVILEGES ON zipfood.* TO 'zipfood'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## 4. Publicar os arquivos

Exemplo com `git`:

```bash
sudo git clone https://github.com/andersonmelo01/PDV_DELIVERY.git /var/www/zipfood
```

Ou envie os arquivos do projeto para `/var/www/zipfood`.

## 5. Ajustar permissoes

```bash
sudo chown -R www-data:www-data /var/www/zipfood
sudo find /var/www/zipfood -type d -exec chmod 755 {} \\;
sudo find /var/www/zipfood -type f -exec chmod 644 {} \\;
sudo chmod -R 775 /var/www/zipfood/img
```

## 6. Configurar o projeto

Edite `config.php` ou prefira variaveis de ambiente no VirtualHost.

Valores minimos esperados:

- `DB_HOST=localhost`
- `DB_NAME=zipfood`
- `DB_USER=zipfood`
- `DB_PASS=TroqueEssaSenhaForte`
- `APP_ENV=production`
- `APP_DEBUG=false`

Se quiser personalizar o admin inicial antes do primeiro acesso:

- `ADMIN_USER`
- `ADMIN_PASSWORD`

Se nada for definido, o sistema cria automaticamente:

- Usuario: `admin`
- Senha: `Admin@123`

## 7. Criar o VirtualHost

Arquivo: `/etc/apache2/sites-available/zipfood.conf`

```apache
<VirtualHost *:80>
    ServerName seu-dominio.com
    DocumentRoot /var/www/zipfood

    SetEnv APP_ENV production
    SetEnv APP_DEBUG false
    SetEnv DB_HOST localhost
    SetEnv DB_NAME zipfood
    SetEnv DB_USER zipfood
    SetEnv DB_PASS TroqueEssaSenhaForte

    <Directory /var/www/zipfood>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/zipfood_error.log
    CustomLog ${APACHE_LOG_DIR}/zipfood_access.log combined
</VirtualHost>
```

Ative o site:

```bash
sudo a2ensite zipfood.conf
sudo a2dissite 000-default.conf
sudo systemctl reload apache2
```

## 8. Estrutura do banco

O sistema cria o banco automaticamente ao conectar, e as tabelas sao garantidas por `app/schema.php`.

Se quiser recriar manualmente:

```bash
mysql -u zipfood -p zipfood < /var/www/zipfood/sql/reset_estrutura.sql
```

## 9. Primeiro acesso

Abra:

- `http://IP_DO_SERVIDOR/`
- ou `http://seu-dominio.com/`

Entre em `admin.php` com:

- Usuario: `admin`
- Senha: `Admin@123`

Depois:

1. altere a senha na propria tela de login
2. revise os dados do emitente
3. configure taxa de entrega e status da loja
4. cadastre usuarios adicionais

## 10. Recomendacoes finais de producao

- Instale HTTPS com Let's Encrypt
- Mantenha `APP_DEBUG=false`
- Faça backup frequente do banco e da pasta `img/`
- Monitore permissões de escrita em `img/`, `config.json` e `emitente.json`
