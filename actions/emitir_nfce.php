<?php
// actions/emitir_nfce.php
header('Content-Type: application/json');
require_once '../config/db.php';
require_once 'sefaz_service.php';

$json = file_get_contents('php://input');
$data = json_decode($json, true);

$idVenda = $data['id_venda'] ?? null;

if (!$idVenda) {
    echo json_encode(['success' => false, 'message' => 'ID da venda não informado.']);
    exit;
}

try {
    // 1. Busca dados da venda
    $stmt = $pdo->prepare("SELECT * FROM vendas WHERE id = ?");
    $stmt->execute([$idVenda]);
    $venda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$venda) {
        throw new Exception("Venda não encontrada.");
    }

    // 2. Busca itens da venda
    $stmtItens = $pdo->prepare("SELECT * FROM venda_itens WHERE id_venda = ?");
    $stmtItens->execute([$idVenda]);
    $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

    // 3. Busca pagamentos
    $stmtPag = $pdo->prepare("SELECT * FROM venda_pagamentos WHERE id_venda = ?");
    $stmtPag->execute([$idVenda]);
    $pagamentos = $stmtPag->fetchAll(PDO::FETCH_ASSOC);

    // 4. Emite NFC-e
    $sefaz = new SefazService($pdo);
    $resultado = $sefaz->emitirNFCe($idVenda, $itens, $pagamentos);

    if ($resultado['success']) {
        // Salva chave/xml no banco se quiser (tabela fiscal_vendas?)
        // Por enquanto retorna sucesso
        echo json_encode([
            'success' => true, 
            'message' => 'NFC-e emitida com sucesso!',
            'xml' => base64_encode($resultado['xml']),
            'recibo' => $resultado['recibo']
        ]);
    } else {
        echo json_encode([
            'success' => false, 
            'message' => 'Erro na SEFAZ: ' . $resultado['message']
        ]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
