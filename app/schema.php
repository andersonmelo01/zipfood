<?php

function ensure_column(PDO $pdo, string $table, string $column, string $definition): void
{
    $stmt = $pdo->prepare(
        'SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?'
    );
    $stmt->execute([$table, $column]);

    if ((int) $stmt->fetchColumn() === 0) {
        $pdo->exec(sprintf('ALTER TABLE `%s` ADD COLUMN %s', $table, $definition));
    }
}

function ensure_default_admin_user(PDO $pdo): void
{
    $adminsAtivos = (int) $pdo->query("SELECT COUNT(*) FROM usuarios WHERE papel = 'admin' AND ativo = 1")->fetchColumn();
    if ($adminsAtivos > 0) {
        return;
    }

    $auth = config_value('auth', []);
    $usuario = trim((string) ($auth['admin_user'] ?? ''));
    $senha = (string) ($auth['admin_password'] ?? 'Admin@123');
    $senhaHash = trim((string) ($auth['admin_password_hash'] ?? ''));

    if ($usuario === '') {
        $usuario = 'admin';
    }

    if ($senhaHash === '') {
        $senhaHash = password_hash($senha !== '' ? $senha : 'Admin@123', PASSWORD_DEFAULT);
    }

    $stmt = $pdo->prepare('SELECT id FROM usuarios WHERE usuario = ? LIMIT 1');
    $stmt->execute([$usuario]);
    $userId = $stmt->fetchColumn();

    if ($userId) {
        $update = $pdo->prepare("UPDATE usuarios SET nome = ?, senha_hash = ?, papel = 'admin', ativo = 1 WHERE id = ?");
        $update->execute(['Administrador', $senhaHash, $userId]);
        return;
    }

    $insert = $pdo->prepare("
        INSERT INTO usuarios (nome, usuario, senha_hash, papel, ativo, created_at, updated_at)
        VALUES (?, ?, ?, 'admin', 1, NOW(), NOW())
    ");
    $insert->execute(['Administrador', $usuario, $senhaHash]);
}

function ensure_schema(PDO $pdo): void
{
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS produtos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo_produto VARCHAR(50),
            codigo_barras VARCHAR(50),
            nome VARCHAR(150) NOT NULL,
            categoria VARCHAR(100),
            un VARCHAR(10) DEFAULT 'UN',
            preco DECIMAL(10,2) NOT NULL,
            preco_promocional DECIMAL(10,2) DEFAULT 0,
            preco_custo DECIMAL(10,2) DEFAULT 0,
            imagem TEXT,
            disponivel TINYINT(1) DEFAULT 1,
            promo_ativa TINYINT(1) DEFAULT 0,
            ncm VARCHAR(20),
            cfop VARCHAR(10),
            cst_icms VARCHAR(10),
            aliquota_icms DECIMAL(5,2),
            aliquota_pis DECIMAL(5,2),
            aliquota_cofins DECIMAL(5,2),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_produtos_disponivel (disponivel),
            INDEX idx_produtos_categoria (categoria)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    ensure_column($pdo, 'produtos', 'preco_promocional', 'preco_promocional DECIMAL(10,2) DEFAULT 0 AFTER preco');
    ensure_column($pdo, 'produtos', 'promo_ativa', 'promo_ativa TINYINT(1) DEFAULT 0 AFTER disponivel');

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedidos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(150) NOT NULL,
            telefone VARCHAR(30) NOT NULL,
            endereco TEXT NOT NULL,
            referencia TEXT,
            observacao TEXT,
            pagamento VARCHAR(50),
            taxa_entrega DECIMAL(10,2) DEFAULT 0,
            total DECIMAL(10,2) NOT NULL,
            status VARCHAR(50) DEFAULT 'novo',
            data_pedido DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX idx_pedidos_data (data_pedido),
            INDEX idx_pedidos_status (status)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS pedido_itens (
            id INT AUTO_INCREMENT PRIMARY KEY,
            pedido_id INT NOT NULL,
            produto_nome VARCHAR(150) NOT NULL,
            quantidade INT NOT NULL,
            preco DECIMAL(10,2) NOT NULL,
            subtotal DECIMAL(10,2) NOT NULL,
            INDEX idx_pedido_itens_pedido (pedido_id),
            FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS feedbacks (
            id VARCHAR(40) PRIMARY KEY,
            nome VARCHAR(100) NOT NULL,
            comentario TEXT NOT NULL,
            estrelas INT NOT NULL DEFAULT 5,
            aprovado TINYINT(1) DEFAULT 0,
            sugestao TINYINT(1) DEFAULT 0,
            data DATETIME DEFAULT CURRENT_TIMESTAMP,
            INDEX idx_feedbacks_aprovado (aprovado),
            INDEX idx_feedbacks_data (data)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nome VARCHAR(120) NOT NULL,
            usuario VARCHAR(120) NOT NULL,
            senha_hash VARCHAR(255) NOT NULL,
            papel VARCHAR(20) NOT NULL DEFAULT 'vendedor',
            ativo TINYINT(1) NOT NULL DEFAULT 1,
            ultimo_login_em DATETIME NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            UNIQUE KEY uniq_usuarios_usuario (usuario),
            INDEX idx_usuarios_papel (papel),
            INDEX idx_usuarios_ativo (ativo)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
    ");

    ensure_column($pdo, 'usuarios', 'papel', "papel VARCHAR(20) NOT NULL DEFAULT 'vendedor' AFTER senha_hash");
    ensure_column($pdo, 'usuarios', 'ativo', "ativo TINYINT(1) NOT NULL DEFAULT 1 AFTER papel");
    ensure_column($pdo, 'usuarios', 'ultimo_login_em', 'ultimo_login_em DATETIME NULL AFTER ativo');

    ensure_default_admin_user($pdo);
}
