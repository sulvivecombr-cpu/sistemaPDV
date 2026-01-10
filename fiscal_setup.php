<?php
// fiscal_setup.php
$page_title = 'Configuração Fiscal';
include 'includes/header.php';
require_once 'config/db.php';

$message = '';

// Handle Post
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $stmt = $pdo->prepare("UPDATE config_fiscal SET 
            cnpj = ?, razao_social = ?, nome_fantasia = ?, ie = ?, uf = ?, 
            ambiente = ?, csc_id = ?, csc_token = ?, certificado_senha = ?, serie_atual = ?, numero_atual = ?
            WHERE id = 1"); // Assumindo id 1 (criado no migration)
        
        $stmt->execute([
            $_POST['cnpj'], $_POST['razao_social'], $_POST['nome_fantasia'], $_POST['ie'], $_POST['uf'],
            $_POST['ambiente'], $_POST['csc_id'], $_POST['csc_token'], $_POST['certificado_senha'], 
            $_POST['serie_atual'], $_POST['numero_atual']
        ]);

        // Upload Certificado
        if (isset($_FILES['certificado_path']) && $_FILES['certificado_path']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'certificados/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = basename($_FILES['certificado_path']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['certificado_path']['tmp_name'], $targetPath)) {
                $pdo->exec("UPDATE config_fiscal SET certificado_path = '$targetPath' WHERE id = 1");
            }
        }

        $message = '<div class="alert alert-success">Configurações salvas com sucesso!</div>';
    } catch (Exception $e) {
        $message = '<div class="alert alert-danger">Erro ao salvar: ' . $e->getMessage() . '</div>';
    }
}

// Fetch Current Data
$data = $pdo->query("SELECT * FROM config_fiscal LIMIT 1")->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-4">
    <h2>Configuração Fiscal (NFC-e)</h2>
    <?= $message ?>
    <form method="POST" enctype="multipart/form-data" class="card p-4">
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>CNPJ</label>
                <input type="text" name="cnpj" class="form-control" value="<?= $data['cnpj'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>IE (Inscrição Estadual)</label>
                <input type="text" name="ie" class="form-control" value="<?= $data['ie'] ?>" required>
            </div>
            <div class="col-md-4 mb-3">
                <label>UF</label>
                <input type="text" name="uf" class="form-control" value="<?= $data['uf'] ?>" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Razão Social</label>
                <input type="text" name="razao_social" class="form-control" value="<?= $data['razao_social'] ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label>Nome Fantasia</label>
                <input type="text" name="nome_fantasia" class="form-control" value="<?= $data['nome_fantasia'] ?>">
            </div>
        </div>
        <hr>
        <h4>Ambiente e Token (CSC)</h4>
        <div class="row">
            <div class="col-md-4 mb-3">
                <label>Ambiente</label>
                <select name="ambiente" class="form-select">
                    <option value="2" <?= $data['ambiente'] == 2 ? 'selected' : '' ?>>Homologação (Teste)</option>
                    <option value="1" <?= $data['ambiente'] == 1 ? 'selected' : '' ?>>Produção</option>
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label>CSC ID (Ex: 000001)</label>
                <input type="text" name="csc_id" class="form-control" value="<?= $data['csc_id'] ?>">
            </div>
            <div class="col-md-4 mb-3">
                <label>CSC Token (Código Alfanumérico)</label>
                <input type="text" name="csc_token" class="form-control" value="<?= $data['csc_token'] ?>">
            </div>
        </div>
        <hr>
        <h4>Certificado Digital A1</h4>
        <div class="row">
            <div class="col-md-6 mb-3">
                <label>Arquivo PFX (Upload)</label>
                <input type="file" name="certificado_path" class="form-control">
                <small class="text-muted">Atual: <?= $data['certificado_path'] ?></small>
            </div>
            <div class="col-md-6 mb-3">
                <label>Senha do Certificado</label>
                <input type="password" name="certificado_senha" class="form-control" value="<?= $data['certificado_senha'] ?>">
            </div>
        </div>
        <hr>
        <h4>Numeração</h4>
        <div class="row">
             <div class="col-md-6 mb-3">
                <label>Série Atual</label>
                <input type="number" name="serie_atual" class="form-control" value="<?= $data['serie_atual'] ?>">
            </div>
            <div class="col-md-6 mb-3">
                <label>Número Atual (Próxima Nota)</label>
                <input type="number" name="numero_atual" class="form-control" value="<?= $data['numero_atual'] ?>">
            </div>
        </div>

        <button type="submit" class="btn btn-primary btn-lg">Salvar Configurações</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
