<?php
include 'includes/header.php'; // Certifique-se de que este header inclui a conexão com o banco de dados ($pdo) e o CSS do Bootstrap

// Se a conexão com o banco não estiver no header.php, inclua-a aqui:
// require_once 'config/db.php';

// --- CONSULTAS EXISTENTES ---

// Consulta para o Ranking dos Mais Vendidos
$ranking_query = "
    SELECT
        p.nome,
        SUM(vi.quantidade) as total_vendido
    FROM venda_itens vi
    JOIN produtos p ON vi.id_produto = p.id
    GROUP BY p.id, p.nome
    ORDER BY total_vendido DESC
    LIMIT 10;
";
$ranking_stmt = $pdo->query($ranking_query);
$ranking_produtos = $ranking_stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta para o Ticket Médio Geral
$ticket_medio_query = "SELECT AVG(valor_total) as ticket_medio FROM vendas;";
$ticket_medio = $pdo->query($ticket_medio_query)->fetchColumn();


// --- NOVAS CONSULTAS ---

// 1. Vendas do Dia
$today = date('Y-m-d');
// Força a comparação de collation para evitar erro de mix de collations
$daily_sales_query = "SELECT SUM(valor_total) as total_vendas_dia, COUNT(id) as num_vendas_dia FROM vendas WHERE CONVERT(DATE(data_venda) USING utf8mb4) = CONVERT(? USING utf8mb4)";
$daily_sales_stmt = $pdo->prepare($daily_sales_query);
$daily_sales_stmt->execute([$today]);
$daily_sales = $daily_sales_stmt->fetch(PDO::FETCH_ASSOC);

// 2. Vendas do Mês
$current_month = date('Y-m'); // Formato YYYY-MM
// Força a comparação de collation para evitar erro de mix de collations
$monthly_sales_query = "SELECT SUM(valor_total) as total_vendas_mes, COUNT(id) as num_vendas_mes FROM vendas WHERE CONVERT(DATE_FORMAT(data_venda, '%Y-%m') USING utf8mb4) = CONVERT(? USING utf8mb4)";
$monthly_sales_stmt = $pdo->prepare($monthly_sales_query);
$monthly_sales_stmt->execute([$current_month]);
$monthly_sales = $monthly_sales_stmt->fetch(PDO::FETCH_ASSOC);

// // 3. Produtos com Estoque Baixo (limite de exemplo: 10 unidades)
// $low_stock_threshold = 10; // Você pode tornar isso configurável no futuro
// $low_stock_query = "SELECT id, codigo_barras, nome, quantidade_estoque FROM produtos WHERE quantidade_estoque <= ? AND ativo = TRUE ORDER BY quantidade_estoque ASC";
// $low_stock_stmt = $pdo->prepare($low_stock_query);
// $low_stock_stmt->execute([$low_stock_threshold]);
// $low_stock_products = $low_stock_stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Central de Relatórios</title>
    <link rel="stylesheet" href="css/home.css">
</head>
<body>

<div class="container-fluid mt-3">
    <div class="row g-4">
        <!-- Ranking de Mais Vendidos (Existente) -->
        <div class="col-md-6">
            <div class="card card_home p-4 h-100">
                <h4>Ranking de Mais Vendidos</h4>
                <ul class="list-group list-group-flush">
                    <?php if (empty($ranking_produtos)): ?>
                        <li class="list-group-item text-center">Nenhum produto vendido ainda.</li>
                    <?php else: ?>
                        <?php foreach($ranking_produtos as $prod): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo htmlspecialchars($prod['nome']); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $prod['total_vendido']; ?> un.</span>
                        </li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card card_home p-4 h-100">
                <h4>Vendas do Dia (<?php echo date('d/m/Y'); ?>)</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total de Vendas
                        <span class="badge bg-custom rounded-pill">R$ <?php echo number_format($daily_sales['total_vendas_dia'] ?? 0, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Quantidade de Vendas
                        <span class="badge bg-custom rounded-pill"><?php echo $daily_sales['num_vendas_dia'] ?? 0; ?></span>
                    </li>
                </ul>
            </div>
        </div>

       
    </div>
    <div class="row g-4 mt-4">
        <!-- Vendas do Dia (NOVO) -->
          <!-- Indicadores Gerais (Ticket Médio - Existente) -->
        <div class="col-md-6">
            <div class="card card_home p-4 h-100">
                <h4>Indicadores Gerais</h4>
                <div class="mt-3 ticket_ind_cont">
                    <h5>Ticket Médio por Venda</h5>
                    <h2>R$ <?php echo number_format($ticket_medio ?? 0, 2, ',', '.'); ?></h2>
                </div>
            </div>
        </div>
        

        <!-- Vendas do Mês (NOVO) -->
        <div class="col-md-6">
            <div class="card card_home p-4 h-100">
                <h4>Vendas do Mês (<?php echo date('m/Y'); ?>)</h4>
                <ul class="list-group list-group-flush">
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Total de Vendas
                        <span class="badge bg-custom rounded-pill">R$ <?php echo number_format($monthly_sales['total_vendas_mes'] ?? 0, 2, ',', '.'); ?></span>
                    </li>
                    <li class="list-group-item d-flex justify-content-between align-items-center">
                        Quantidade de Vendas
                        <span class="badge bg-custom rounded-pill"><?php echo $monthly_sales['num_vendas_mes'] ?? 0; ?></span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- <div class="row g-4 mt-4"> -->
        <!-- Produtos com Estoque Baixo (NOVO) -->
        <!-- <div class="col-md-12">
            <div class="card card_home p-4">
                <h4><i class="bi bi-exclamation-triangle-fill"></i> Produtos com Estoque Baixo (abaixo de <?php echo $low_stock_threshold; ?> un.)</h4>
                <?php if (empty($low_stock_products)): ?>
                    <div class="alert alert-info mt-3" role="alert">
                        Nenhum produto com estoque abaixo do limite. Ótimo!
                    </div>
                <?php else: ?>
                    <table class="table table-hover mt-3">
                        <thead>
                            <tr>
                                <th>Cód. Barras</th>
                                <th>Nome</th>
                                <th>Estoque Atual</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($low_stock_products as $product): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($product['codigo_barras']); ?></td>
                                <td><?php echo htmlspecialchars($product['nome']); ?></td>
                                <td><span class="badge bg-warning text-dark"><?php echo $product['quantidade_estoque']; ?> un.</span></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div> -->
    </div> 

    </body>
</html>
