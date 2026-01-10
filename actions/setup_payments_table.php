<?php
require_once '../config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS venda_pagamentos (
        id INT AUTO_INCREMENT PRIMARY KEY,
        id_venda INT NOT NULL,
        forma_pagamento VARCHAR(50) NOT NULL,
        valor DECIMAL(10, 2) NOT NULL,
        payment_id VARCHAR(100) NULL,
        data_pagamento DATETIME DEFAULT CURRENT_TIMESTAMP,
        INDEX (id_venda)
    )";

    $pdo->exec($sql);
    echo "Table 'venda_pagamentos' created or already exists successfully.";
} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
