<?php
// actions/load_offline_data.php
header('Content-Type: application/json');
require_once '../config/db.php';

try {
    // Fetch all active products for offline cache
    // We get more fields here than the search to ensure we have everything needed for the cart
    $stmt = $pdo->prepare("SELECT id, codigo_barras, nome, preco_venda, quantidade_estoque FROM produtos WHERE ativo = TRUE");
    $stmt->execute();
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode([
        'success' => true,
        'timestamp' => time(),
        'products' => $produtos
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Erro ao carregar dados offline: ' . $e->getMessage()]);
}
?>
