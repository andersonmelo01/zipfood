# Manual de Instalação - ZipFood (Ubuntu + Apache2)

## Requisitos
- Ubuntu 22.04 LTS ou superior
- Apache2
- PHP 8.1 ou superior
- MySQL/MariaDB
- Git (opcional)

## Passos

### 1. Atualize o sistema
```bash
sudo apt update && sudo apt upgrade -y
```

### 2. Instale Apache2, PHP e extensões
```bash
sudo apt install apache2 php php-mysql php-xml php-mbstring php-curl php-zip unzip -y
```

### 3. Instale o MySQL/MariaDB
```bash
sudo apt install mysql-server -y
```

### 4. Configure o banco de dados
```bash
sudo mysql -u root
CREATE DATABASE delivery DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'deliveryuser'@'localhost' IDENTIFIED BY 'SENHA_FORTE';
GRANT ALL PRIVILEGES ON delivery.* TO 'deliveryuser'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### 5. Baixe o sistema
- Clone o repositório ou envie os arquivos para `/var/www/html/zipfood`:
```bash
sudo git clone https://github.com/andersonmelo01/PDV_DELIVERY.git /var/www/html/zipfood
```

### 6. Configure permissões
```bash
sudo chown -R www-data:www-data /var/www/html/zipfood
sudo chmod -R 755 /var/www/html/zipfood
```

### 7. Configure o Apache
Crie o arquivo `/etc/apache2/sites-available/zipfood.conf`:
```apache
<VirtualHost *:80>
    ServerName seu_dominio.com
    DocumentRoot /var/www/html/zipfood
    <Directory /var/www/html/zipfood>
        AllowOverride All
        Require all granted
    </Directory>
    ErrorLog ${APACHE_LOG_DIR}/zipfood_error.log
    CustomLog ${APACHE_LOG_DIR}/zipfood_access.log combined
</VirtualHost>
```

Ative o site e o mod_rewrite:
```bash
sudo a2ensite zipfood.conf
sudo a2enmod rewrite
sudo systemctl reload apache2
```

### 8. Configure o .env (opcional)
- Ajuste as variáveis de ambiente conforme necessário.

### 9. Importe o banco de dados
```bash
mysql -u zipfooduser -p zipfood < /var/www/html/zipfood/sql/reset_estrutura.sql
```

### 10. Acesse o sistema
- Navegue até `http://seu_dominio.com` ou `http://IP_DO_SERVIDOR/zipfood`

---

# Suporte
- Para dúvidas, consulte o README ou abra uma issue no GitHub.
