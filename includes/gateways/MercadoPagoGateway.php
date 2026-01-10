<?php
require_once 'PaymentGatewayInterface.php';

class MercadoPagoGateway implements PaymentGatewayInterface {
    private $token;
    private $deviceId;

    public function __construct($token, $deviceId) {
        $this->token = $token;
        $this->deviceId = $deviceId;
    }

    public function createPayment($amount, $type = 'credit_card') {
        $url = "https://api.mercadopago.com/point/integration-api/devices/{$this->deviceId}/payment-intents";
        
        $data = [
            "amount" => (float)$amount,
            "description" => "Venda PDV",
            "payment" => [
                "installments" => 1,
                "type" => $type, // 'credit_card', 'debit_card', 'voucher', 'qr_code'
                "installments_cost" => "seller"
            ],
            "additional_info" => [
                "print_on_terminal" => true
            ]
        ];

        return $this->makeRequest($url, 'POST', $data);
    }

    public function checkStatus($paymentId) {
        $url = "https://api.mercadopago.com/point/integration-api/payment-intents/{$paymentId}";
        return $this->makeRequest($url, 'GET');
    }

    public function cancelPayment($paymentId) {
        $url = "https://api.mercadopago.com/point/integration-api/payment-intents/{$paymentId}";
        return $this->makeRequest($url, 'DELETE');
    }

    private function makeRequest($url, $method, $data = null) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ];
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $body = json_decode($response, true);

        return [
            'success' => ($httpCode >= 200 && $httpCode < 300),
            'http_code' => $httpCode,
            'body' => $body
        ];
    }
}
?>
