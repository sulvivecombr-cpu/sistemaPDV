<?php
// actions/setup_db_terminals.php
require_once '../config/db.php';

try {
    $sql = "
    CREATE TABLE IF NOT EXISTS terminal_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL, 
        provider VARCHAR(50) NOT NULL, 
        name VARCHAR(100) NOT NULL,
        api_key VARCHAR(255),
        api_token VARCHAR(255),
        device_id VARCHAR(100),
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Tabela 'terminal_configs' criada ou já existente com sucesso!";

} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
}
?>
