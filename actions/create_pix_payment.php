<?php
// actions/create_pix_payment.php
header('Content-Type: application/json');
require_once 'mp_integration.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!isset($data['amount'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Valor não informado']);
    exit;
}

try {
    $mp = new MercadoPagoIntegration();
    
    // Para PIX Online sem Point, usamos /v1/payments
    // Mas a classe mp_integration.php está focada no Point (/point/integration-api)
    // Vamos adicionar um método genérico ou fazer hardcode aqui via curl para /v1/payments
    // Como a classe já tem o token, vamos estender ou instanciar.
    
    // Melhor: Adicionar método createPixPayment na classe mp_integration.php
    // Vou fazer isso num passo separado. Por hora, vou chamar o método novo que vou criar.
    
    $response = $mp->createOnlinePixPayment($data['amount']);
    
    if (isset($response['body']['id'])) {
        echo json_encode([
            'payment_id' => $response['body']['id'],
            'qr_code' => $response['body']['point_of_interaction']['transaction_data']['qr_code'],
            'qr_code_base64' => $response['body']['point_of_interaction']['transaction_data']['qr_code_base64']
        ]);
    } else {
        // --- FALLBACK: PIX ESTÁTICO (EVP) ---
        // Se a API falhar (ex: falta de chave), geramos um BR Code localmente com a chave fornecida.
        
        $fallbackKey = 'e925c337-2883-4276-8f0a-8b01258f0560'; // Chave do cliente (Atualizada)
        
        require_once 'PixPayload.php';
        
        // Dados da empresa (Pegar do config ou hardcode)
        $nomeEmpresa = 'LOJA PDV'; // Pode ser melhorado buscando do DB
        $cidade = 'SAO PAULO';
        $txid = preg_replace('/[^a-zA-Z0-9]/', '', uniqid()); 
        
        $obPayload = (new PixPayload())
            ->setPixKey($fallbackKey)
            ->setMerchantName($nomeEmpresa)
            ->setMerchantCity($cidade)
            ->setAmount($data['amount'])
            ->setTxid($txid);
            
        $payloadQrCode = $obPayload->getPayload();
        
        // Gera Imagem via API externa (para não precisar de lib local)
        // Em produção idealmente usaria lib local (phpqrcode)
        $qrUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($payloadQrCode);
        $qrImage = file_get_contents($qrUrl);
        $base64 = base64_encode($qrImage);
        
        echo json_encode([
            'payment_id' => 'static_' . time(),
            'qr_code' => $payloadQrCode,
            'qr_code_base64' => $base64,
            'manual_confirmation' => true, // Flag para o Frontend saber que não haverá polling
            'message' => 'Modo Contingência: Confirmação Manual Necessária'
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
?>
