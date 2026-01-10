<?php
require_once __DIR__ . '/../config/db.php';

// Proteção de página: se não estiver logado, redireciona para o login
if (!isset($_SESSION['user_id'])) {
    header('Location: /pdv/login.php');
    exit();
}
?>
<!doctype html>
<html lang="pt-br">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo $page_title ?? 'Sistema PDV'; ?></title>
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link rel="stylesheet" href="css/header.css">
    <link rel="stylesheet" href="css/pdv.css">
    <link rel="manifest" href="manifest.json">
    <script>
      if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
          navigator.serviceWorker.register('service-worker.js')
            .then(reg => console.log('Service Worker registrado:', reg))
            .catch(err => console.log('Service Worker falhou:', err));
        });
      }
    </script>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container-fluid d-flex align-items-center justify-content-between" style="position: relative;">
            <!-- Botão Hamburguer para abrir sidebar -->
            <button class="navbar-hamburger me-2" id="sidebarToggle" type="button" aria-label="Abrir menu lateral">
                <i class="bi bi-list" style="font-size: 2rem;"></i>
            </button>
            <div class="navbar-header-centralizado">
                <!-- Logotipo centralizado (escondido no mobile) -->
                <div class="navbar-logo">
                    <a href="./dash.php" class="text-decoration-none text-white fs-4 fw-bold">
                        SISTEMA PDV
                    </a>
                </div>
                <!-- Botão Iniciar Venda centralizado entre logo e relógio -->
                <a href="pdv.php" class="btn btn-iniciar-venda">Iniciar Venda</a>
            </div>
            <!-- Botões do lado direito + Relógio (escondidos no mobile) -->
            <div class="d-flex align-items-center ms-auto" style="position: absolute; right: 0; top: 50%; transform: translateY(-50%); z-index: 4;">
                <div class="dropdown-user">
                    <div class="mini-relogio" id="miniRelogio">
                        --
                    </div>
                    <div class="dropdown">
                        <button class="btn dropdown-toggle" type="button" id="dropdownUserMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUserMenu">
                            <li>
                                <a class="dropdown-item" href="./config_terminal.php">
                                    <i class="bi bi-credit-card-2-front"></i> Configurar Maquininhas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="./maquinas_conectadas.php">
                                    <i class="bi bi-phone"></i> Selecionar Maquininha Conectada
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="./fiscal_setup.php">
                                    <i class="bi bi-gear"></i> Configuração Fiscal
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="./actions/logout_action.php">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </nav>
    <!-- Sidebar (oculto por padrão, exibe ao clicar no hamburguer) -->
    <div id="sidebar" class="bg-white shadow" style="position: fixed; top: 0; left: -260px; width: 260px; height: 100vh; z-index: 1040; transition: left 0.3s;">
        <div class="d-flex flex-column h-100">
            <!-- Logo no topo do sidebar apenas no mobile -->
            <div class="sidebar-logo-mobile" style="display:none;">
                <a href="/pdv/index.php" class="text-decoration-none text-dark fs-4 fw-bold">
                    SISTEMA PDV
                </a>
            </div>
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <span class="fw-bold fs-5">MENU</span>
                <button class="btn btn-light btn-sm" id="sidebarClose" aria-label="Fechar menu">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <ul class="nav flex-column p-2">
                <li class="nav-item mb-2"><a class="nav-link" href="pdv.php"><i class="bi bi-cart-check"></i> PDV</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="modulo_de_vendas.php"><i class="bi bi-graph-up"></i>Módulo de vendas</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="estoque.php"><i class="bi bi-box-seam"></i> Estoque</a></li>
                <li class="nav-item mb-2"><a class="nav-link" href="dash.php"><i class="bi bi-graph-up"></i>Inicio</a></li>

            </ul>
            <!-- Relógio e usuário no sidebar apenas no mobile -->
            <div class="d-block d-lg-none px-3 py-2">
                <div class="mini-relogio" id="miniRelogioSidebar" style="margin-bottom: 0.5rem;">
                    --
                </div>
                <div class="dropdown w-100">
                    <button class="btn dropdown-toggle w-100" type="button" id="dropdownUserMenuSidebar" data-bs-toggle="dropdown" aria-expanded="false">
                        <?php echo htmlspecialchars($_SESSION['user_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end w-100" aria-labelledby="dropdownUserMenuSidebar">
                            <li>
                                <a class="dropdown-item" href="./config_terminal.php">
                                    <i class="bi bi-credit-card-2-front"></i> Configurar Maquininhas
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="./maquinas_conectadas.php">
                                    <i class="bi bi-phone"></i> Selecionar Maquininha Conectada
                                </a>
                            </li>
                        <li>
                            <a class="dropdown-item" href="/pdv/fiscal_setup.php">
                                <i class="bi bi-gear"></i> Configuração Fiscal
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="/pdv/actions/logout_action.php">
                                <i class="bi bi-box-arrow-right"></i> Sair
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
            <div class="mt-auto p-3 border-top">
                <a href="/pdv/actions/logout_action.php" class="btn btn-outline-danger w-100"><i class="bi bi-box-arrow-right"></i> Sair</a>
            </div>
        </div>
    </div>
    <main class="container mt-4">

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Abrir sidebar
        document.addEventListener('DOMContentLoaded', function() {
            var sidebar = document.getElementById('sidebar');
            var sidebarToggle = document.getElementById('sidebarToggle');
            var sidebarClose = document.getElementById('sidebarClose');
            var sidebarOpen = false;

            sidebarToggle.addEventListener('click', function() {
                sidebar.style.left = '0';
                sidebarOpen = true;
            });

            sidebarClose.addEventListener('click', function() {
                sidebar.style.left = '-260px';
                sidebarOpen = false;
            });

            // Fechar sidebar ao clicar fora dele
            document.addEventListener('click', function(event) {
                if (sidebarOpen && !sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                    sidebar.style.left = '-260px';
                    sidebarOpen = false;
                }
            });

            // Relógio ao vivo (apenas horas e minutos)
            function atualizarRelogio() {
                var agora = new Date();
                var horas = agora.getHours().toString().padStart(2, '0');
                var minutos = agora.getMinutes().toString().padStart(2, '0');
                var miniRelogio = document.getElementById('miniRelogio');
                var miniRelogioSidebar = document.getElementById('miniRelogioSidebar');
                if (miniRelogio) {
                    miniRelogio.textContent = horas + ':' + minutos;
                }
                if (miniRelogioSidebar) {
                    miniRelogioSidebar.textContent = horas + ':' + minutos;
                }
            }
            atualizarRelogio();
            setInterval(atualizarRelogio, 1000);

            // Corrige o dropdown do usuário manualmente caso o Bootstrap não inicialize automaticamente
            var dropdownUserMenu = document.getElementById('dropdownUserMenu');
            if (dropdownUserMenu) {
                if (!dropdownUserMenu.classList.contains('show')) {
                    new bootstrap.Dropdown(dropdownUserMenu);
                }
            }
            var dropdownUserMenuSidebar = document.getElementById('dropdownUserMenuSidebar');
            if (dropdownUserMenuSidebar) {
                if (!dropdownUserMenuSidebar.classList.contains('show')) {
                    new bootstrap.Dropdown(dropdownUserMenuSidebar);
                }
            }
        });
    </script>