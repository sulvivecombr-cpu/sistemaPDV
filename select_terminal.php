<?php
// select_terminal.php
$page_title = 'Selecionar Maquininha';
include 'includes/header.php';
require_once 'config/mercadopago.php';

// Se for POST, salva na sessão
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['device_id'])) {
    $_SESSION['device_id'] = $_POST['device_id'];
    $success_msg = "Maquininha selecionada com sucesso: " . htmlspecialchars($_POST['device_id']);
}

// Busca dispositivos via API
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

$currentDevice = $_SESSION['device_id'] ?? MP_DEVICE_ID;

?>

<div class="container mt-4">
    <h2>Selecionar Maquininha (Point)</h2>
    <p class="text-muted">Selecione qual dispositivo este caixa irá utilizar.</p>

    <?php if (isset($success_msg)): ?>
        <div class="alert alert-success"><?= $success_msg ?></div>
    <?php endif; ?>

    <div class="row">
        <?php if (isset($configDevices['devices'])): ?>
            <?php foreach ($configDevices['devices'] as $device): ?>
                <div class="col-md-4 mb-3">
                    <div class="card h-100 <?php echo ($currentDevice == $device['id']) ? 'border-primary bg-light' : ''; ?>">
                        <div class="card-body text-center">
                            <h5 class="card-title"><?php echo htmlspecialchars($device['operating_mode'] ?? 'Point Smart'); ?></h5>
                            <p class="card-text text-muted">ID: <?php echo htmlspecialchars($device['id']); ?></p>
                            <span class="badge bg-secondary mb-3">POS ID: <?php echo htmlspecialchars($device['pos_id']); ?></span>
                            
                            <form method="POST">
                                <input type="hidden" name="device_id" value="<?php echo htmlspecialchars($device['id']); ?>">
                                <button type="submit" class="btn <?php echo ($currentDevice == $device['id']) ? 'btn-primary' : 'btn-outline-primary'; ?> w-100">
                                    <?php echo ($currentDevice == $device['id']) ? 'Selecionado' : 'Usar esta'; ?>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="alert alert-warning">Não foi possível carregar os dispositivos do Mercado Pago. Verifique o token.</div>
        <?php endif; ?>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
