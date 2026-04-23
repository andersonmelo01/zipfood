SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pedido_itens;
DROP TABLE IF EXISTS pedidos;
DROP TABLE IF EXISTS produtos;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE produtos (
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

CREATE TABLE pedidos (
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

CREATE TABLE pedido_itens (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT NOT NULL,
    produto_nome VARCHAR(150) NOT NULL,
    quantidade INT NOT NULL,
    preco DECIMAL(10,2) NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    INDEX idx_pedido_itens_pedido (pedido_id),
    CONSTRAINT fk_pedido_itens_pedido
        FOREIGN KEY (pedido_id) REFERENCES pedidos(id)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

