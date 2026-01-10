<?php
// actions/create_point_payment.php
header('Content-Type: application/json');
require_once 'mp_integration.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['amount']) || !is_numeric($data['amount'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Valor inválido.']);
    exit;
}

try {
    $mp = new MercadoPagoIntegration();
    $type = $data['type'] ?? null;
    $response = $mp->createPaymentIntent($data['amount'], $type);

    if ($response['status'] >= 200 && $response['status'] < 300) {
        // Sucesso na criação da intenção
        // O ID retornado aqui é o que usaremos para checar o status
        echo json_encode([
            'success' => true, 
            'payment_intent_id' => $response['body']['id']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao comunicar com a maquininha: ' . ($response['body']['message'] ?? 'Erro desconhecido'),
            'details' => $response['body']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
