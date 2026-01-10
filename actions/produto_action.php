<?php
// actions/produto_action.php
header('Content-Type: application/json');
require_once '../config/db.php'; // Ajuste o caminho conforme sua estrutura

// Inicia a sessão se ainda não estiver iniciada (necessário para $_SESSION)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Verifica se o usuário está logado (opcional, mas recomendado para ações de estoque)
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Usuário não autenticado.']);
    exit;
}

$json = file_get_contents('php://input');
$data = json_decode($json, true);

if (!$data) {
    echo json_encode(['success' => false, 'message' => 'Dados inválidos.']);
    exit;
}

$pdo->beginTransaction();

try {
    // Lógica para adicionar ou editar produto
    if (!isset($data['action'])) { // Se não houver uma ação específica, assume-se adicionar/editar produto
        $id = $data['id'] ?? null;
        $codigo_barras = $data['codigo_barras'] ?? '';
        $nome = $data['nome'] ?? '';
        $descricao = $data['descricao'] ?? null;
        $preco_venda = $data['preco_venda'] ?? 0;
        $quantidade_estoque = $data['quantidade_estoque'] ?? 0;
        $ativo = $data['ativo'] ?? 1; // Default para ativo

        if (empty($codigo_barras) || empty($nome) || empty($preco_venda)) {
            throw new Exception('Campos obrigatórios (Código de Barras, Nome, Preço de Venda) não preenchidos.');
        }

        if ($id) {
            // Editar produto existente
            $stmt = $pdo->prepare("UPDATE produtos SET codigo_barras = ?, nome = ?, descricao = ?, preco_venda = ?, ativo = ? WHERE id = ?");
            $stmt->execute([$codigo_barras, $nome, $descricao, $preco_venda, $ativo, $id]);
            $message = 'Produto atualizado com sucesso!';
        } else {
            // Adicionar novo produto
            $stmt = $pdo->prepare("INSERT INTO produtos (codigo_barras, nome, descricao, preco_venda, quantidade_estoque, ativo) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->execute([$codigo_barras, $nome, $descricao, $preco_venda, $quantidade_estoque, $ativo]);
            $message = 'Produto adicionado com sucesso!';
        }
    } elseif ($data['action'] === 'add_stock') {
        // Lógica para adicionar estoque a um produto existente
        $id_produto = $data['id_produto'] ?? null;
        $quantidade_adicionar = $data['quantidade'] ?? 0;

        if (empty($id_produto) || !is_numeric($quantidade_adicionar) || $quantidade_adicionar <= 0) {
            throw new Exception('ID do produto ou quantidade inválida para adicionar estoque.');
        }

        $stmt = $pdo->prepare("UPDATE produtos SET quantidade_estoque = quantidade_estoque + ? WHERE id = ?");
        $stmt->execute([$quantidade_adicionar, $id_produto]);
        $message = "Estoque de produto atualizado com sucesso! Adicionado: {$quantidade_adicionar} unidades.";

    } elseif ($data['action'] === 'toggle_active') {
        // Lógica para ativar/desativar produto
        $id_produto = $data['id'] ?? null;
        $ativo = $data['ativo'] ?? null; // 0 para inativo, 1 para ativo

        if (empty($id_produto) || !isset($ativo) || !is_numeric($ativo)) {
            throw new Exception('ID do produto ou status ativo inválido para alternar.');
        }

        $stmt = $pdo->prepare("UPDATE produtos SET ativo = ? WHERE id = ?");
        $stmt->execute([$ativo, $id_produto]);
        $status_text = $ativo ? 'ativado' : 'desativado';
        $message = "Produto {$status_text} com sucesso!";

    } else {
        throw new Exception('Ação desconhecida.');
    }

    $pdo->commit();
    echo json_encode(['success' => true, 'message' => $message]);

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Falha na operação: ' . $e->getMessage()]);
}
?>
