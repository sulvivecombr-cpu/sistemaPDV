<?php
// actions/mp_integration.php
require_once __DIR__ . '/../config/mercadopago.php';

class MercadoPagoIntegration {
    private $accessToken;
    private $deviceId;

    public function __construct() {
        $this->accessToken = MP_ACCESS_TOKEN;
        // Prioriza a seleção da sessão, senão usa o padrão
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $this->deviceId = $_SESSION['device_id'] ?? MP_DEVICE_ID;
    }

    private function request($method, $endpoint, $data = null) {
        $url = "https://api.mercadopago.com" . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->accessToken,
            'Content-Type: application/json',
            'X-Idempotency-Key: ' . uniqid() // Importante para evitar cobranças duplicadas
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }
        
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => json_decode($response, true)
        ];
    }

    public function createPaymentIntent($amount, $type = 'credit_card') {
        // Doc: https://www.mercadopago.com.br/developers/pt/reference/integrations_api_payment_intents/post
        
        $data = [
            "amount" => (int)($amount * 100), // Valor em centavos (integer)
            "description" => "Venda PDV",
            "additional_info" => [
                "external_reference" => uniqid("PDV_")
            ]
        ];

        if ($type) {
            $paymentParams = [
                "type" => $type 
            ];
            
            if ($type === 'credit_card') {
                $paymentParams['installments'] = 1;
            }

            $data["payment"] = $paymentParams;
        }

        // NOTA: A API do Point pode requerer parametros especificos
        // Endpoint correto para Point Smart/Mini via Integrations API:
        $endpoint = "/point/integration-api/devices/" . $this->deviceId . "/payment-intents";

        return $this->request('POST', $endpoint, $data);
    }

    public function getPaymentIntentStatus($paymentIntentId) {
        $endpoint = "/point/integration-api/payment-intents/" . $paymentIntentId;
        return $this->request('GET', $endpoint);
    }

    public function cancelPaymentIntent($paymentIntentId) {
        $endpoint = "/point/integration-api/payment-intents/" . $paymentIntentId;
        return $this->request('DELETE', $endpoint);
    }
    public function createOnlinePixPayment($amount) {
        $endpoint = "/v1/payments";
        $data = [
            "transaction_amount" => (float)$amount,
            "description" => "Venda PDV - PIX",
            "payment_method_id" => "pix",
            "payer" => [
                "email" => "cliente@email.com"
            ]
        ];
        return $this->request('POST', $endpoint, $data);
    }

    public function checkOnlinePaymentStatus($paymentId) {
        $endpoint = "/v1/payments/" . $paymentId;
        return $this->request('GET', $endpoint);
    }
}
?>
