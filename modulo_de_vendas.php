<?php
error_log("TESTE DE LOG: Script modulo_de_vendas.php iniciado.");
include 'includes/header.php'; // Certifique-se de que este header inclui a conexão com o banco de dados ($pdo) e o CSS do Bootstrap

// Exemplo de dados para os filtros (substitua pelo que vier do banco, se necessário)
$departamentos = [
    'Supermercado',
    'Hortifruti',
    'Padaria',
    'Açougue'
];

$categorias = [
    'Todos',
    'Bebidas',
    'Alimentos',
    'Higiene',
    'Limpeza',
    'Utilidades'
];

$subcategorias = [
    'Refrigerantes',
    'Sucos',
    'Cervejas',
    'Arroz',
    'Feijão',
    'Massas'
];

$marcas = [
    'Coca-Cola',
    'Pepsi',
    'Nestlé',
    'Ypê',
    'Sadia',
    'Perdigão'
];

// Exemplo de produtos para a vitrine (agora cada produto tem um SKU)
$produtos = [
    [
        'sku' => '207023715MAD',
        'descricao' => 'Refrigerante Cola 2L',
        'preco' => 8.99,
        'parcelamento' => 'ou 2x de R$ 4,50',
    ],
    [
        'sku' => 'DEPE019',
        'descricao' => 'Arroz Branco 5kg',
        'preco' => 22.50,
        'parcelamento' => 'ou 3x de R$ 7,50',
    ],
    [
        'sku' => 'BGM551411404007002',
        'descricao' => 'Sabonete Neutro 90g',
        'preco' => 2.99,
        'parcelamento' => 'ou 2x de R$ 1,50',
    ],
    [
        'sku' => 'BGM5529300170210',
        'descricao' => 'Detergente Líquido 500ml',
        'preco' => 3.49,
        'parcelamento' => 'ou 2x de R$ 1,75',
    ],
    [
        'sku' => 'BGM45635452',
        'descricao' => 'Papel Higiênico 12 rolos',
        'preco' => 15.90,
        'parcelamento' => 'ou 3x de R$ 5,30',
    ],
    [
        'sku' => '55104234103504',
        'descricao' => 'Café Torrado 500g',
        'preco' => 12.00,
        'parcelamento' => 'ou 2x de R$ 6,00',
    ],
    [
        'sku' => '6910650016021',
        'descricao' => 'Óleo de Soja 900ml',
        'preco' => 7.80,
        'parcelamento' => 'ou 2x de R$ 3,90',
    ],
    [
        'sku' => '5529100115092',
        'descricao' => 'Macarrão Espaguete 500g',
        'preco' => 4.20,
        'parcelamento' => 'ou 2x de R$ 2,10',
    ],
];

/**
 * Busca imagens do FTP para um SKU específico.
 * @param string $sku
 * @return array
 */
function getImagensPorSKU($sku) {
    if (empty($sku)) {
        return [];
    }
    
    // --- CONFIGURAÇÕES CORRIGIDAS ---
    // --- CONFIGURAÇÕES DE FTP ---
    $ftp_host = 'ftp.example.com'; 
    $ftp_user = 'user_ftp';
    $ftp_pass = 'password_ftp';
    $ftp_base_dir = 'images/' . $sku . '/';
    
    // URL pública base para as imagens.
    $base_image_url = 'https://imagens.example.com/';

    $imagens = [];

    // Tenta conectar ao servidor FTP
    $conn_id = ftp_connect($ftp_host);
    if ($conn_id === false) {
        error_log("Falha na conexão FTP: Não foi possível conectar a {$ftp_host}.");
        return [];
    }

    // Tenta fazer o login
    if (@ftp_login($conn_id, $ftp_user, $ftp_pass)) {
        
        // Ativa o modo passivo
        ftp_pasv($conn_id, true);
        
        // Tenta listar os arquivos no diretório
        $files = ftp_nlist($conn_id, $ftp_base_dir);

        if ($files === false) {
            error_log("Erro ao listar arquivos no FTP para SKU {$sku}. Verifique o caminho '{$ftp_base_dir}' e as permissões.");
        } else {
            foreach ($files as $file) {
                $filename = basename($file);
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                    $imagens[] = $base_image_url . $sku . '/' . $filename;
                }
            }
        }
    } else {
        error_log("Falha no login FTP para o usuário {$ftp_user} no host {$ftp_host}. Verifique usuário e senha.");
    }

    // Fecha a conexão
    ftp_close($conn_id);
    
    return $imagens;
}

// Lógica para buscar imagens e adicioná-las aos produtos
// Adicionei uma imagem padrão caso o FTP não retorne nada.
$imagem_padrao = 'https://via.placeholder.com/150';

foreach ($produtos as $key => $produto) {
    $imagens_encontradas = getImagensPorSKU($produto['sku']);
    if (empty($imagens_encontradas)) {
        // Se não encontrar imagens, use a imagem padrão
        $produtos[$key]['imagens'] = [$imagem_padrao];
    } else {
        $produtos[$key]['imagens'] = $imagens_encontradas;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo de Vendas</title>
    <link rel="stylesheet" href="css/home.css">
    <style>
        .container-vitrine {
            display: flex;
            margin-top: 40px;
            min-height: 80vh;
        }
        .sidebar-filtros {
            width: 256px;
            background: #F5F5F5;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            padding: 24px 16px;
            margin-right: 32px;
            min-height: 350px;
            color: #707070;
            position: fixed;
            left: 0px;
            overflow-y: auto;
            bottom: 5px;
        }
        .main-content-vitrine {
            flex: 1;
        }
        .search-bar-vitrine {
            display: flex;
            justify-content: center;
            margin: 32px 0 24px 0;
        }
        .search-bar-vitrine input[type="search"] {
            width: 420px;
            padding: 12px 20px;
            border-radius: 30px;
            border: 1px solid #ccc;
            font-size: 1.1rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.04);
            outline: none;
            transition: border 0.2s;
        }
        .search-bar-vitrine input[type="search"]:focus {
            border: 1.5px solid #ff6600;
        }
        .vitrine-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 28px;
            margin-left: 18%;
        }
        .item-vitrine {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.07);
            padding: 18px 14px 20px 14px;
            display: flex;
            flex-direction: column;
            align-items: center;
            transition: box-shadow 0.2s;
            height: 100%;
        }
        .item-vitrine:hover {
            box-shadow: 0 4px 16px rgba(255,102,0,0.13);
        }
        .item-vitrine .slider-container {
            width: 140px;
            height: 140px;
            position: relative;
            margin-bottom: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .item-vitrine .slider-img {
            width: 140px;
            height: 140px;
            object-fit: cover;
            border-radius: 8px;
            display: block;
        }
        .item-vitrine .slider-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(255,255,255,0.8);
            border: none;
            border-radius: 50%;
            width: 24px;
            height: 24px;
            font-size: 1.2rem;
            color: #ff6600;
            cursor: pointer;
            z-index: 2;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }
        .item-vitrine .slider-btn.prev {
            left: -18px;
        }
        .item-vitrine .slider-btn.next {
            right: -18px;
        }
        .item-vitrine .slider-dots {
            display: flex;
            justify-content: center;
            margin-top: 4px;
        }
        .item-vitrine .slider-dot {
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: #ccc;
            margin: 0 2px;
            display: inline-block;
            cursor: pointer;
        }
        .item-vitrine .slider-dot.active {
            background: #ff6600;
        }
        .item-vitrine .descricao {
            font-size: 1.08rem;
            font-weight: 500;
            text-align: center;
            margin-bottom: 8px;
            min-height: 40px;
        }
        .item-vitrine .preco {
            font-size: 1.25rem;
            color: #ff6600;
            font-weight: bold;
            margin-bottom: 4px;
        }
        .item-vitrine .parcelamento {
            font-size: 0.92rem;
            color: #888;
            margin-bottom: 12px;
        }
        .item-vitrine .btn-comprar {
            background: #ff6600;
            color: #fff;
            border: none;
            border-radius: 22px;
            padding: 8px 28px;
            font-size: 1rem;
            font-weight: 500;
            transition: background 0.2s;
        }
        .item-vitrine .btn-comprar:hover {
            background: #e65c00;
        }
        /* Customização dos accordions dos filtros */
        .accordion .accordion-item,
        .accordion .accordion-header,
        .accordion .accordion-button,
        .accordion .accordion-collapse,
        .accordion .accordion-body {
            background: transparent !important;
            box-shadow: none !important;
        }
        .accordion .accordion-button {
            color: #707070;
            font-weight: 500;
            border: none;
        }
        .accordion .accordion-button:not(.collapsed) {
            color: #ff6600;
            background: transparent !important;
            box-shadow: none !important;
        }
        .accordion .accordion-button:focus {
            box-shadow: none;
        }
        .accordion .accordion-item {
            border: none;
        }
        .accordion .accordion-body ul li {
            margin-bottom: 8px;
        }
        .filtro-lista-item {
            color: #707070;
            text-decoration: none;
            cursor: pointer;
            font-size: 1rem;
            font-weight: 400;
            padding: 0;
            background: none;
            border: none;
            display: block;
        }
        .filtro-lista-item:hover {
            color: #ff6600;
            background: none;
            text-decoration: none;
        }
        @media (max-width: 1200px) {
            .vitrine-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        @media (max-width: 900px) {
            .container-vitrine {
                flex-direction: column;
            }
            .sidebar-filtros {
                width: 100%;
                margin-bottom: 24px;
                margin-right: 0;
                position: static;
            }
            .main-content-vitrine {
                width: 100%;
            }
            .vitrine-grid {
                grid-template-columns: repeat(2, 1fr);
                margin-left: 0;
            }
        }
        @media (max-width: 600px) {
            .vitrine-grid {
                grid-template-columns: 1fr;
            }
            .search-bar-vitrine input[type="search"] {
                width: 100%;
            }
        }
        @media (min-width:1900px){
            .sidebar-filtros {
            width: 318px;
            background: #F5F5F5;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.07);
            padding: 24px 16px;
            margin-right: 32px;
            min-height: 350px;
            color: #707070;
            position: fixed;
            left: 8%;
            overflow-y: auto;
            bottom: 29%;
        }
        }
    </style>
</head>
<body>
    <div class="search-bar-vitrine">
        <input type="search" placeholder="Buscar produtos..." aria-label="Buscar produtos">
    </div>
    <div class="container-vitrine container">
        <aside class="sidebar-filtros">
            <div class="accordion" id="accordionFiltros">
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingDepartamento">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDepartamento" aria-expanded="false" aria-controls="collapseDepartamento">
                            Departamento
                        </button>
                    </h2>
                    <div id="collapseDepartamento" class="accordion-collapse collapse" aria-labelledby="headingDepartamento" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body p-2">
                            <ul class="mb-0 ps-2">
                                <?php foreach($departamentos as $dep): ?>
                                    <li style="margin-bottom:8px;">
                                        <span class="filtro-lista-item"><?= htmlspecialchars($dep) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingCategoria">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseCategoria" aria-expanded="false" aria-controls="collapseCategoria">
                            Categoria
                        </button>
                    </h2>
                    <div id="collapseCategoria" class="accordion-collapse collapse" aria-labelledby="headingCategoria" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body p-2">
                            <ul class="mb-0 ps-2">
                                <?php foreach($categorias as $cat): ?>
                                    <li style="margin-bottom:8px;">
                                        <span class="filtro-lista-item"><?= htmlspecialchars($cat) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingSubcategoria">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSubcategoria" aria-expanded="false" aria-controls="collapseSubcategoria">
                            Subcategoria
                        </button>
                    </h2>
                    <div id="collapseSubcategoria" class="accordion-collapse collapse" aria-labelledby="headingSubcategoria" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body p-2">
                            <ul class="mb-0 ps-2">
                                <?php foreach($subcategorias as $sub): ?>
                                    <li style="margin-bottom:8px;">
                                        <span class="filtro-lista-item"><?= htmlspecialchars($sub) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="accordion-item">
                    <h2 class="accordion-header" id="headingMarca">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMarca" aria-expanded="false" aria-controls="collapseMarca">
                            Marca
                        </button>
                    </h2>
                    <div id="collapseMarca" class="accordion-collapse collapse" aria-labelledby="headingMarca" data-bs-parent="#accordionFiltros">
                        <div class="accordion-body p-2">
                            <ul class="mb-0 ps-2">
                                <?php foreach($marcas as $marca): ?>
                                    <li style="margin-bottom:8px;">
                                        <span class="filtro-lista-item"><?= htmlspecialchars($marca) ?></span>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                    </div>
                </div>
            </div>
        </aside>
        <main class="main-content-vitrine">
            <div class="vitrine-grid">
                <h1>Itens mais vendidos </h1>
                <?php foreach($produtos as $produto): ?>
                    <div class="item-vitrine">
                        <div class="slider-container" data-sku="<?= htmlspecialchars($produto['sku']) ?>">
                            <button class="slider-btn prev" type="button" aria-label="Imagem anterior" onclick="sliderPrev('<?= $produto['sku'] ?>')">&lt;</button>
                            <img class="slider-img" id="slider-img-<?= htmlspecialchars($produto['sku']) ?>" src="<?= htmlspecialchars($produto['imagens'][0]) ?>" alt="Foto do produto">
                            <button class="slider-btn next" type="button" aria-label="Próxima imagem" onclick="sliderNext('<?= $produto['sku'] ?>')">&gt;</button>
                        </div>
                        <div class="slider-dots" id="slider-dots-<?= htmlspecialchars($produto['sku']) ?>">
                            <?php foreach($produto['imagens'] as $idx => $img): ?>
                                <span class="slider-dot<?= $idx === 0 ? ' active' : '' ?>" onclick="sliderGoTo('<?= htmlspecialchars($produto['sku']) ?>', <?= $idx ?>)"></span>
                            <?php endforeach; ?>
                        </div>
                        <div class="descricao"><?= htmlspecialchars($produto['descricao']) ?></div>
                        <div class="preco">R$ <?= number_format($produto['preco'], 2, ',', '.') ?></div>
                        <div class="parcelamento"><?= htmlspecialchars($produto['parcelamento']) ?></div>
                        <button class="btn-comprar">Comprar</button>
                        <script>
                            // Passa as imagens para JS para cada produto
                            window.sliderImgs = window.sliderImgs || {};
                            window.sliderImgs['<?= htmlspecialchars($produto['sku']) ?>'] = <?= json_encode($produto['imagens']) ?>;
                            window.sliderIndex = window.sliderIndex || {};
                            window.sliderIndex['<?= htmlspecialchars($produto['sku']) ?>'] = 0;
                        </script>
                    </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Funções do mini slider manual
        function sliderPrev(sku) {
            if (!window.sliderImgs || !window.sliderImgs[sku]) return;
            let idx = window.sliderIndex[sku] || 0;
            idx = (idx - 1 + window.sliderImgs[sku].length) % window.sliderImgs[sku].length;
            sliderShow(sku, idx);
        }
        function sliderNext(sku) {
            if (!window.sliderImgs || !window.sliderImgs[sku]) return;
            let idx = window.sliderIndex[sku] || 0;
            idx = (idx + 1) % window.sliderImgs[sku].length;
            sliderShow(sku, idx);
        }
        function sliderGoTo(sku, idx) {
            sliderShow(sku, idx);
        }
        function sliderShow(sku, idx) {
            window.sliderIndex[sku] = idx;
            let imgEl = document.getElementById('slider-img-' + sku);
            if (imgEl) {
                imgEl.src = window.sliderImgs[sku][idx];
            }
            // Atualiza dots
            let dots = document.querySelectorAll('#slider-dots-' + sku + ' .slider-dot');
            dots.forEach(function(dot, i) {
                if (i === idx) {
                    dot.classList.add('active');
                } else {
                    dot.classList.remove('active');
                }
            });
        }
    </script>
</body>
</html>