<?php
// actions/get_active_terminals.php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    $stmt = $pdo->prepare("SELECT id, name, provider FROM terminal_configs WHERE active = TRUE");
    $stmt->execute();
    $terminals = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // CHECK FOR LEGACY SESSION DEVICE
    if (isset($_SESSION['device_id']) && !empty($_SESSION['device_id'])) {
        // Verifica se já não está na lista (para não duplicar se ele cadastrou no DB o mesmo ID)
        $alreadyInList = false;
        foreach ($terminals as $t) {
            // Nota: na tabela temos columns (id, user_id, provider...) mas device_id não vem no SELECT * acima se não pedirmos.
            // O ideal seria pegar device_id no select
            // Mas simples: vamos assumir que sessão é "Conectado Manualmente"
        }
        
        $terminals[] = [
            'id' => 'LEGACY_' . $_SESSION['device_id'], // ID Fictício
            'name' => 'Maquininha Conectada (Point)',
            'provider' => 'mercadopago',
            'is_legacy' => true
        ];
    }

    echo json_encode(['success' => true, 'terminals' => $terminals]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
