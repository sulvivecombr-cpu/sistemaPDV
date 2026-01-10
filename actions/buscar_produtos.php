<?php
// actions/buscar_produtos.php
header('Content-Type: application/json');
require_once '../config/db.php'; // Ajuste o caminho conforme sua estrutura

$term = $_GET['term'] ?? '';

if (strlen($term) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Busca produtos por nome ou código de barras, que estejam ativos
    $stmt = $pdo->prepare("SELECT id, codigo_barras, nome, preco_venda, quantidade_estoque FROM produtos WHERE (nome LIKE ? OR codigo_barras LIKE ?) AND ativo = TRUE ORDER BY nome LIMIT 10");
    $searchTerm = '%' . $term . '%';
    $stmt->execute([$searchTerm, $searchTerm]);
    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($produtos);

} catch (PDOException $e) {
    // Em caso de erro no banco de dados
    http_response_code(500); // Define o status HTTP para erro interno do servidor
    echo json_encode(['error' => 'Erro ao buscar produtos: ' . $e->getMessage()]);
}
?>
