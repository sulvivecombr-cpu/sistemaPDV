<?php
// actions/check_pix_status.php
header('Content-Type: application/json');
require_once 'mp_integration.php';

$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(['error' => 'ID não informado']);
    exit;
}

try {
    $mp = new MercadoPagoIntegration();
    $response = $mp->checkOnlinePaymentStatus($id);

    if (isset($response['body']['status'])) {
        $status = $response['body']['status'];
        
        $result = [
            'status' => $status
        ];

        // Se aprovado, retorna dados completos similar ao pagamento por maquininha
        if ($status === 'approved') {
            $result['payment_data'] = [
                'payment_id' => $id,
                'status' => $status,
                // Mapeamento para formato esperado pelo frontend
                'transaction_amount' => $response['body']['transaction_amount'],
                'payment_method_id' => 'pix'
            ];
        }

        echo json_encode($result);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Resposta inválida da API', 'details' => $response]);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
