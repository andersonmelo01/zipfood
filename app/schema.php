<?php

function ensure_schema(PDO $pdo): void
{
    // ================= FEEDBACKS =================
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
        CREATE DATABASE IF NOT EXISTS zipfood_demo
        CHARACTER SET utf8mb4
        COLLATE utf8mb4_unicode_ci;
    ");

    // 🔥 Seleciona o banco
    $pdo->exec("USE zipfood_demo");

    // ================= PRODUTOS =================
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

    // 🔥 Garante colunas adicionais
    $columns = [
        'preco_promocional' => "ALTER TABLE produtos ADD COLUMN preco_promocional DECIMAL(10,2) DEFAULT 0 AFTER preco",
        'promo_ativa' => "ALTER TABLE produtos ADD COLUMN promo_ativa TINYINT(1) DEFAULT 0 AFTER disponivel",
    ];

    foreach ($columns as $column => $alterSql) {
        $stmt = $pdo->prepare("
            SELECT COUNT(*)
            FROM INFORMATION_SCHEMA.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
              AND TABLE_NAME = 'produtos'
              AND COLUMN_NAME = ?
        ");
        $stmt->execute([$column]);

        if ((int) $stmt->fetchColumn() === 0) {
            $pdo->exec($alterSql);
        }
    }

    // ================= PEDIDOS =================
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

    // ================= ITENS DO PEDIDO =================
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
}
