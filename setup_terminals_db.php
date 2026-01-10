<?php
// setup_terminals_db.php
require 'config/db.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS terminal_configs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL, 
        provider VARCHAR(50) NOT NULL COMMENT 'mercadopago, manual, stone, rede, getnet',
        name VARCHAR(100) NOT NULL,
        api_key VARCHAR(255),
        api_token VARCHAR(255),
        device_id VARCHAR(100),
        active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";

    $pdo->exec($sql);
    echo "Tabela 'terminal_configs' criada/verificada com sucesso.<br>";

    // Insere um registro manual padrão se não existir
    $stmt = $pdo->query("SELECT count(*) FROM terminal_configs WHERE provider = 'manual'");
    if ($stmt->fetchColumn() == 0) {
        $pdo->exec("INSERT INTO terminal_configs (provider, name, active) VALUES ('manual', 'Pagamento Manual', 1)");
        echo "Configuração 'Manual' padrão inserida.<br>";
    }
    
    // Tenta migrar a config do mercadopago.php se existir (opcional, mas bom pra UX)
    // Lendo o arquivo mercadopago.php para extrair constantes
    $mpFile = 'config/mercadopago.php';
    if (file_exists($mpFile)) {
        $content = file_get_contents($mpFile);
        if (preg_match("/define\('MP_ACCESS_TOKEN', '([^']+)'\)/", $content, $mToken) &&
            preg_match("/define\('MP_DEVICE_ID', '([^']+)'\)/", $content, $mDevice)) {
            
            $token = $mToken[1];
            $device = $mDevice[1];
            
            $stmt = $pdo->prepare("SELECT count(*) FROM terminal_configs WHERE provider = 'mercadopago' AND device_id = ?");
            $stmt->execute([$device]);
            if ($stmt->fetchColumn() == 0) {
                 $stmtIns = $pdo->prepare("INSERT INTO terminal_configs (provider, name, api_token, device_id, active) VALUES ('mercadopago', 'Mercado Pago Point', ?, ?, 1)");
                 $stmtIns->execute([$token, $device]);
                 echo "Configuração 'Mercado Pago' migrada do arquivo estático.<br>";
            }
        }
    }

} catch (PDOException $e) {
    echo "Erro ao criar tabela: " . $e->getMessage();
}
?>
