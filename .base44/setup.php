<?php
// Idempotent development setup only; never run against a production database.
require __DIR__ . '/../config/db.php';

$tables = [
    'CREATE TABLE IF NOT EXISTS usuarios (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL UNIQUE,
        senha_hash VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'CREATE TABLE IF NOT EXISTS produtos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        codigo_barras VARCHAR(100) NOT NULL UNIQUE,
        nome VARCHAR(255) NOT NULL,
        descricao TEXT NULL,
        preco_venda DECIMAL(10,2) NOT NULL,
        quantidade_estoque INT NOT NULL DEFAULT 0,
        ativo BOOLEAN NOT NULL DEFAULT TRUE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'CREATE TABLE IF NOT EXISTS vendas (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_usuario INT NOT NULL,
        valor_total DECIMAL(10,2) NOT NULL,
        data_venda DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'CREATE TABLE IF NOT EXISTS venda_itens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_venda INT NOT NULL,
        id_produto INT NOT NULL,
        quantidade INT NOT NULL,
        preco_unitario DECIMAL(10,2) NOT NULL,
        FOREIGN KEY (id_venda) REFERENCES vendas(id),
        FOREIGN KEY (id_produto) REFERENCES produtos(id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
    'CREATE TABLE IF NOT EXISTS terminal_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        provider VARCHAR(50) NOT NULL,
        name VARCHAR(100) NOT NULL,
        api_key VARCHAR(255),
        api_token VARCHAR(255),
        device_id VARCHAR(100),
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4',
];
foreach ($tables as $sql) {
    $pdo->exec($sql);
}

// Reuse the project's existing optional module migrations (relative includes).
chdir(__DIR__ . '/../actions');
require 'setup_payments_table.php';
require 'setup_fiscal_table.php';

// Only seed a fresh installation; keep existing users and inventory untouched.
if ((int)$pdo->query('SELECT COUNT(*) FROM usuarios')->fetchColumn() === 0) {
    $stmt = $pdo->prepare('INSERT INTO usuarios (nome, email, senha_hash) VALUES (?, ?, ?)');
    $stmt->execute(['Administrador Demo', 'admin@admin.com', password_hash('admin', PASSWORD_DEFAULT)]);
}
if ((int)$pdo->query('SELECT COUNT(*) FROM terminal_configs')->fetchColumn() === 0) {
    $pdo->exec("INSERT INTO terminal_configs (provider, name) VALUES ('manual', 'Pagamento Manual')");
}
echo "\nDevelopment database ready.\n";
