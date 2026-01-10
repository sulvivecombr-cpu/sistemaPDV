<?php
// actions/venda_action.php
header('Content-Type: application/json');
require_once '../config/db.php';

// Pega os dados enviados pelo JavaScript
$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data || !isset($data['itens']) || empty($data['itens'])) {
    echo json_encode(['success' => false, 'message' => 'Nenhum item enviado.']);
    exit;
}

$itens = $data['itens'];
$id_usuario = $_SESSION['user_id']; // Pega o ID do usuário logado na sessão

// Inicia a transação
$pdo->beginTransaction();

try {
    // 1. Calcula o valor total da venda
    $valor_total = 0;
    foreach ($itens as $item) {
        $valor_total += $item['quantidade'] * $item['preco_unitario'];
    }

    // 2. Insere o registro na tabela 'vendas'
    $stmtVenda = $pdo->prepare("INSERT INTO vendas (id_usuario, valor_total) VALUES (?, ?)");
    $stmtVenda->execute([$id_usuario, $valor_total]);
    $id_venda = $pdo->lastInsertId(); // Pega o ID da venda que acabamos de criar

    // 3. Prepara as queries para inserir os itens e atualizar o estoque
    $stmtItem = $pdo->prepare(
        "INSERT INTO venda_itens (id_venda, id_produto, quantidade, preco_unitario) VALUES (?, ?, ?, ?)"
    );
    $stmtEstoque = $pdo->prepare(
        "UPDATE produtos SET quantidade_estoque = quantidade_estoque - ? WHERE id = ?"
    );

    // 4. Itera sobre cada item para salvá-lo e atualizar o estoque
    foreach ($itens as $item) {
        // Insere o item na tabela 'venda_itens'
        $stmtItem->execute([
            $id_venda,
            $item['id'],
            $item['quantidade'],
            $item['preco_unitario']
        ]);

        // Atualiza (diminui) a quantidade no estoque
        $stmtEstoque->execute([
            $item['quantidade'],
            $item['id']
        ]);
    }

    // 5. Salvar os pagamentos
    if (isset($data['pagamentos']) && is_array($data['pagamentos'])) {
        $stmtPagamento = $pdo->prepare(
            "INSERT INTO venda_pagamentos (id_venda, forma_pagamento, valor, payment_id) VALUES (?, ?, ?, ?)"
        );
        foreach ($data['pagamentos'] as $pagamento) {
            $stmtPagamento->execute([
                $id_venda,
                $pagamento['forma_pagamento'],
                $pagamento['valor_pago'],
                $pagamento['payment_id'] ?? null
            ]);
        }
    }

    // 6. Se tudo deu certo, confirma a transação
    $pdo->commit();

    echo json_encode(['success' => true, 'id_venda' => $id_venda]);

} catch (Exception $e) {
    // 6. Se algo deu errado, desfaz tudo
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Falha na transação: ' . $e->getMessage()]);
}
?>