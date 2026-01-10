<?php
// actions/debug_get_devices.php
require_once '../config/mercadopago.php';

// Script simples para listar os dispositivos vinculados à conta
$token = MP_ACCESS_TOKEN;
$url = "https://api.mercadopago.com/point/integration-api/devices";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

echo "Consultando dispositivos na conta...\n";
echo "URL: $url\n";

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

if (curl_errno($ch)) {
    echo "Erro CURL: " . curl_error($ch) . "\n";
} else {
    echo "HTTP Code: $httpCode\n\n";
    echo "Resposta:\n";
    
    $json = json_decode($response, true);
    if ($json) {
        // Formata o JSON para ler melhor
        if (isset($json['devices'])) {
            echo "--------------------------------------------------\n";
            echo "DISPOSITIVOS ENCONTRADOS: " . count($json['devices']) . "\n";
            echo "--------------------------------------------------\n";
            foreach ($json['devices'] as $device) {
                echo "Nome: " . ($device['name'] ?? 'Sem nome') . "\n";
                echo "ID (Use este no config): " . $device['id'] . "\n";
                echo "Serial: " . ($device['serial_number'] ?? 'N/A') . "\n";
                echo "Modelo: " . ($device['device_type'] ?? 'N/A') . "\n";
                echo "--------------------------------------------------\n";
            }
        } else {
            echo json_encode($json, JSON_PRETTY_PRINT);
        }
    } else {
        echo $response;
    }
}

curl_close($ch);
?>
