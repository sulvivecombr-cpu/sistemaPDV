<?php
require_once 'PaymentGatewayInterface.php';

class PlaceholderGateway implements PaymentGatewayInterface {
    private $providerName;

    public function __construct($providerName) {
        $this->providerName = ucfirst($providerName);
    }

    public function createPayment($amount, $type = 'credit_card') {
        // Simula uma resposta de sucesso ou pendente para fins de teste/configuração
        // Na prática, aqui entraria a chamada cURL específica de cada banco via API
        
        return [
            'success' => true,
            'http_code' => 200,
            'body' => [
                'id' => 'MOCK_' . strtoupper($this->providerName) . '_' . time(),
                'status' => 'approved', // Simula aprovado direto para facilitar teste MVP
                'message' => "Pagamento simulado via {$this->providerName}"
            ]
        ];
    }

    public function checkStatus($paymentId) {
        return [
            'success' => true,
            'body' => ['status' => 'approved']
        ];
    }

    public function cancelPayment($paymentId) {
        return [
            'success' => true,
            'body' => ['status' => 'cancelled']
        ];
    }
}
?>
