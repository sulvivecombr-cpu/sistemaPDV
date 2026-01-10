<?php
$page_title = 'Configuração de Maquininhas';
include 'includes/header.php';
require_once 'config/db.php';

// Handle Actions (Add/Delete/Toggle)
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $name = $_POST['name'] ?? '';
        $provider = $_POST['provider'] ?? '';
        
        // Extract fields based on provider from the 'config' array
        $config = $_POST['config'][$provider] ?? [];
        
        $api_key   = $config['api_key'] ?? null;
        $api_token = $config['api_token'] ?? null;
        $device_id = $config['device_id'] ?? null;

        // Validations can be added here
        
        if ($name && $provider) {
            $stmt = $pdo->prepare("INSERT INTO terminal_configs (name, provider, api_key, api_token, device_id) VALUES (?, ?, ?, ?, ?)");
            if ($stmt->execute([$name, $provider, $api_key, $api_token, $device_id])) {
                $message = '<div class="alert alert-success">Maquininha adicionada com sucesso!</div>';
            } else {
                $message = '<div class="alert alert-danger">Erro ao adicionar.</div>';
            }
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM terminal_configs WHERE id = ?");
        $stmt->execute([$id]);
        $message = '<div class="alert alert-success">Removido com sucesso.</div>';
    }
}

// List Terminals
$terminals = $pdo->query("SELECT * FROM terminal_configs ORDER BY created_at DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2><i class="bi bi-credit-card"></i> Configurar Maquininhas</h2>
    <?= $message ?>
    
    <div class="card mt-3">
        <div class="card-header">Nova Maquininha</div>
        <div class="card-body">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Nome de Identificação</label>
                        <input type="text" class="form-control" name="name" placeholder="Ex: Caixa 1 - MP" required>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="form-label">Provedor</label>
                        <select class="form-select" name="provider" id="providerSelect" required>
                            <option value="">Selecione...</option>
                            <option value="mercadopago">Mercado Pago Point</option>
                            <option value="pagseguro">PagBank / Pagar.me</option>
                            <option value="getnet">Getnet (Santander)</option>
                            <option value="rede">Rede (Itaú/Laranjinha)</option>
                            <option value="stone">Stone</option>
                            <option value="manual">Manual (Sem integração)</option>
                        </select>
                    </div>
                    
                    <!-- MERCADO PAGO -->
                    <div class="col-md-5 mb-3 provider-field field-mercadopago" style="display:none;">
                         <label class="form-label">Access Token</label>
                         <input type="text" class="form-control" name="config[mercadopago][api_token]" placeholder="APP_USR-...">
                    </div>
                    <div class="col-md-4 mb-3 provider-field field-mercadopago" style="display:none;">
                         <label class="form-label">Device ID</label>
                         <input type="text" class="form-control" name="config[mercadopago][device_id]" placeholder="Ex: PACET...">
                    </div>

                    <!-- PAGSEGURO / PAGAR.ME -->
                    <div class="col-md-9 mb-3 provider-field field-pagseguro" style="display:none;">
                         <label class="form-label">Chave de API (Secret Key)</label>
                         <input type="text" class="form-control" name="config[pagseguro][api_token]" placeholder="sk_test_...">
                         <small class="text-muted">Insira sua chave de API do Pagar.me/PagBank.</small>
                    </div>

                    <!-- GETNET -->
                    <div class="col-md-5 mb-3 provider-field field-getnet" style="display:none;">
                         <label class="form-label">Client ID</label>
                         <input type="text" class="form-control" name="config[getnet][api_key]" placeholder="Client ID...">
                    </div>
                    <div class="col-md-4 mb-3 provider-field field-getnet" style="display:none;">
                         <label class="form-label">Client Secret</label>
                         <input type="text" class="form-control" name="config[getnet][api_token]" placeholder="Client Secret...">
                    </div>

                    <!-- REDE (ITAÚ) -->
                    <div class="col-md-5 mb-3 provider-field field-rede" style="display:none;">
                         <label class="form-label">PV (Número do Estabelecimento)</label>
                         <input type="text" class="form-control" name="config[rede][device_id]" placeholder="PV...">
                    </div>
                    <div class="col-md-4 mb-3 provider-field field-rede" style="display:none;">
                         <label class="form-label">Token / Chave API</label>
                         <input type="text" class="form-control" name="config[rede][api_token]" placeholder="Token...">
                    </div>

                    <!-- STONE -->
                    <div class="col-md-9 mb-3 provider-field field-stone" style="display:none;">
                         <label class="form-label">Stone Code / RC Key</label>
                         <input type="text" class="form-control" name="config[stone][api_token]" placeholder="Stone Code...">
                    </div>
                </div>
                <button type="submit" class="btn btn-primary"><i class="bi bi-plus-lg"></i> Salvar</button>
            </form>
        </div>
    </div>

    <h3 class="mt-4">Maquininhas Cadastradas</h3>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Provedor</th>
                    <th>ID Dispositivo</th>
                    <th>Status</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($terminals) === 0): ?>
                    <tr><td colspan="5" class="text-center">Nenhuma configurada.</td></tr>
                <?php endif; ?>
                <?php foreach ($terminals as $t): ?>
                    <tr>
                        <td><?= htmlspecialchars($t['name']) ?></td>
                        <td>
                            <?php 
                                $badges = [
                                    'mercadopago' => 'bg-info',
                                    'manual' => 'bg-secondary'
                                ];
                                $badge = $badges[$t['provider']] ?? 'bg-primary';
                                echo "<span class='badge $badge'>" . strtoupper($t['provider']) . "</span>";
                            ?>
                        </td>
                        <td><?= htmlspecialchars($t['device_id'] ?? '-') ?></td>
                        <td>
                            <span class="badge bg-success">Ativo</span>
                        </td>
                        <td>
                            <form method="POST" style="display:inline;" onsubmit="return confirm('Tem certeza?');">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
    document.getElementById('providerSelect').addEventListener('change', function() {
        // Esconde todos
        document.querySelectorAll('.provider-field').forEach(el => el.style.display = 'none');
        
        // Mostra o selecionado
        const val = this.value;
        if (val) {
            document.querySelectorAll('.field-' + val).forEach(el => el.style.display = 'block');
        }
    });
</script>

<?php include 'includes/footer.php'; ?>
