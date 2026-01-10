<?php
// actions/check_point_status.php
header('Content-Type: application/json');
require_once 'mp_integration.php';

$paymentIntentId = $_GET['id'] ?? null;

if (!$paymentIntentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID não fornecido.']);
    exit;
}

try {
    $mp = new MercadoPagoIntegration();
    $response = $mp->getPaymentIntentStatus($paymentIntentId);

    if ($response['status'] >= 200 && $response['status'] < 300) {
        $body = $response['body'];
        $state = $body['state'] ?? 'UNKNOWN'; // OPEN, PROCESSED, CANCELED, ABANDONED

        // Se estiver PROCESSED, pegamos os dados do pagamento
        $paymentData = null;
        if ($state === 'PROCESSED' && isset($body['payment'])) {
            $paymentData = $body['payment']; 
            // ex: { "id": 12345, "status": "approved" ... }
        }

        echo json_encode([
            'success' => true, 
            'state' => $state,
            'payment_data' => $paymentData
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro ao verificar status',
            'details' => $response['body']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
