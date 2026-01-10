<?php
require_once '../config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS config_fiscal (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cnpj VARCHAR(20) NOT NULL,
        razao_social VARCHAR(255) NOT NULL,
        nome_fantasia VARCHAR(255),
        ie VARCHAR(20) NOT NULL,
        uf VARCHAR(2) NOT NULL,
        ambiente INT DEFAULT 2 COMMENT '1=Producao, 2=Homologacao',
        csc_id VARCHAR(10),
        csc_token VARCHAR(255),
        certificado_path VARCHAR(255),
        certificado_senha VARCHAR(255),
        serie_atual INT DEFAULT 1,
        numero_atual INT DEFAULT 1,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )";

    $pdo->exec($sql);
    echo "Table 'config_fiscal' created or already exists successfully.";
    
    // Inserir um registro padrão vazio se não existir
    $stmt = $pdo->query("SELECT COUNT(*) FROM config_fiscal");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO config_fiscal (cnpj, razao_social, ie, uf) VALUES ('00000000000000', 'EMPRESA PADRAO', '000000000', 'SP')");
        echo " Default record inserted.";
    }

} catch (PDOException $e) {
    die("Error creating table: " . $e->getMessage());
}
?>
