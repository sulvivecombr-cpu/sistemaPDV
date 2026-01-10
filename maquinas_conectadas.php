<?php
// maquinas_conectadas.php
$page_title = 'Selecionar Maquininha Conectada';
include 'includes/header.php';
require_once 'config/db.php';
require_once 'config/mercadopago.php';

// Se for POST, salva na sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['device_id'])) {
        // Seleção via API (modo antigo/direto)
        $_SESSION['device_id'] = $_POST['device_id'];
        $_SESSION['terminal_provider'] = 'mercadopago_api'; // Flag para saber origem
        $success_msg = "Maquininha (API) selecionada: " . htmlspecialchars($_POST['device_id']);
    } elseif (isset($_POST['terminal_id'])) {
        // Seleção via Banco de Dados (nova config)
        $tId = $_POST['terminal_id'];
        
        // Busca info para salvar na sessão se necessário
        $stmt = $pdo->prepare("SELECT * FROM terminal_configs WHERE id = ?");
        $stmt->execute([$tId]);
        $term = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($term) {
            $_SESSION['terminal_id_db'] = $term['id'];
            $_SESSION['terminal_provider'] = $term['provider'];
            $_SESSION['device_id'] = $term['device_id']; // Backward comp
            $success_msg = "Terminal Configurado selecionado: " . htmlspecialchars($term['name']);
        }
    }
}

// 1. Busca dispositivos via API do Mercado Pago (Legacy/Direct)
$token = MP_ACCESS_TOKEN;
$url = "https://api.mercadopago.com/point/integration-api/devices";

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Authorization: Bearer ' . $token,
    'Content-Type: application/json'
]);

$response = curl_exec($ch);
$configDevices = json_decode($response, true);
curl_close($ch);

// 2. Busca dispositivos cadastrados no Banco de Dados (Nova Centralização)
$dbTerminals = $pdo->query("SELECT * FROM terminal_configs WHERE active = TRUE ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);

$currentDevice = $_SESSION['device_id'] ?? MP_DEVICE_ID;
$currentDbId = $_SESSION['terminal_id_db'] ?? null;

?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-phone"></i> Selecionar Maquininha Conectada</h2>
        <a href="config_terminal.php" class="btn btn-outline-primary"><i class="bi bi-gear"></i> Configurar Novas</a>
    </div>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>

    <!-- SEÇÃO 1: Maquininhas Integradas (Banco de Dados) -->
    <h4 class="mb-3 text-primary border-bottom pb-2">Meus Terminais Configurados (Banco de Dados)</h4>
    <div class="row mb-5">
        <?php if (count($dbTerminals) === 0): ?>
            <div class="col-12"><p class="text-muted">Nenhum terminal configurado localmente.</p></div>
        <?php else: ?>
            <?php foreach ($dbTerminals as $term): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 <?php echo ($currentDbId == $term['id']) ? 'border-primary bg-light shadow-sm' : 'border-secondary'; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($term['name']); ?></h5>
                            <span class="badge bg-info mb-2"><?php echo strtoupper($term['provider']); ?></span>
                            
                            <?php if ($term['device_id']): ?>
                                <p class="card-text small text-muted">Device ID: <?php echo htmlspecialchars($term['device_id']); ?></p>
                            <?php endif; ?>
                            
                            <form method="POST">
                                <input type="hidden" name="terminal_id" value="<?php echo $term['id']; ?>">
                                <button type="submit" class="btn <?php echo ($currentDbId == $term['id']) ? 'btn-primary' : 'btn-outline-primary'; ?> w-100 mt-2">
                                    <?php echo ($currentDbId == $term['id']) ? '<i class="bi bi-check-circle"></i> Em Uso' : 'Usar este'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <!-- SEÇÃO 2: Dispositivos da API Mercado Pago (Conta Principal) -->
    <h4 class="mb-3 text-secondary border-bottom pb-2">Maquininhas da Conta Mercado Pago (API)</h4>
    <div class="row">
        <?php if (isset($configDevices['devices']) && count($configDevices['devices']) > 0): ?>
            <?php foreach ($configDevices['devices'] as $device): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 <?php echo ($currentDevice == $device['id'] && !$currentDbId) ? 'border-primary bg-light' : ''; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($device['operating_mode'] ?? 'Point Smart'); ?></h5>
                            <p class="card-text text-muted">ID: <?php echo htmlspecialchars($device['id']); ?></p>
                            <span class="badge bg-secondary mb-3">POS ID: <?php echo htmlspecialchars($device['pos_id']); ?></span>
                            
                            <form method="POST">
                                <input type="hidden" name="device_id" value="<?php echo htmlspecialchars($device['id']); ?>">
                                <button type="submit" class="btn <?php echo ($currentDevice == $device['id'] && !$currentDbId) ? 'btn-primary' : 'btn-outline-secondary'; ?> w-100">
                                    <?php echo ($currentDevice == $device['id'] && !$currentDbId) ? 'Conectado Agora' : 'Conectar (API Direta)'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="col-12">
                <div class="alert alert-warning">Não foi possível carregar dispositivos da API do Mercado Pago ou não há dispositivos.</div>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
