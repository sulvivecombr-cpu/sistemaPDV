<?php
// actions/process_payment_gateway.php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once '../includes/gateways/MercadoPagoGateway.php';
// require_once '../includes/gateways/RedeGateway.php'; // Future

$input = json_decode(file_get_contents('php://input'), true);

$terminalId = $input['terminal_id'] ?? null;
$amount = $input['amount'] ?? 0;
$type = $input['type'] ?? 'credit_card'; // credit_card, debit_card, pix, voucher

if (!$terminalId || !$amount) {
    echo json_encode(['success' => false, 'message' => 'Dados incompletos.']);
    exit;
}

try {
    // 1. Get Terminal Config
    if (strpos($terminalId, 'LEGACY_') === 0) {
        // FLUXO LEGADO (Sessão)
        require_once '../config/mercadopago.php';
        if (!defined('MP_ACCESS_TOKEN')) {
             throw new Exception("Token MP não definido no config.");
        }
        
        $realDeviceId = str_replace('LEGACY_', '', $terminalId);
        $config = [
            'provider' => 'mercadopago',
            'api_token' => MP_ACCESS_TOKEN,
            'device_id' => $realDeviceId,
            'name' => 'Legacy Terminal'
        ];
    } else {
        // FLUXO BANCO DE DADOS
        $stmt = $pdo->prepare("SELECT * FROM terminal_configs WHERE id = ? AND active = TRUE");
        $stmt->execute([$terminalId]);
        $config = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$config) {
            throw new Exception("Terminal não encontrado ou inativo.");
        }
    }

    // 2. Instantiate Gateway
    $gateway = null;
    require_once '../includes/gateways/PlaceholderGateway.php';

    switch ($config['provider']) {
        case 'mercadopago':
            if (empty($config['api_token']) || empty($config['device_id'])) {
                throw new Exception("Configuração do Mercado Pago incompleta.");
            }
            $gateway = new MercadoPagoGateway($config['api_token'], $config['device_id']);
            break;
            
        case 'manual':
             echo json_encode(['success' => true, 'manual' => true, 'message' => 'Pagamento Manual Iniciado']);
             exit;

        // Novos Provedores (Placeholders)
        case 'getnet':
        case 'rede':
        case 'pagseguro':
        case 'stone':
            $gateway = new PlaceholderGateway($config['provider']);
            break;
             
        default:
            throw new Exception("Provedor não suportado: " . $config['provider']);
    }

    // 3. Process Request
    $result = $gateway->createPayment($amount, $type);

    if ($result['success']) {
         echo json_encode([
             'success' => true,
             'provider' => $config['provider'],
             'payment_intent_id' => $result['body']['id'] ?? null, // MP specific
             'data' => $result['body']
         ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['body']['message'] ?? 'Erro no gateway',
            'details' => $result['body']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
