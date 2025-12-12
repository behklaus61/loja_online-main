<?php
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php", true, 302);
    exit();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Sistema de Produtos</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .dashboard-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .icon-placeholder {
            font-size: 2.5rem;
            color: #0d6efd;
        }
    </style>
</head>
<body>

<div class="container py-5">
    <h1 class="text-center mb-5">Painel de Controle</h1>

    <div class="row g-4">
        <!-- Relatórios -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="relProdutosLoja.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            📊
                        </div>
                        <h5 class="card-title">Relatórios</h5>
                        <p class="card-text text-muted">Visualize métricas e análises</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cadastro de Produtos -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="produtos.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            📦
                        </div>
                        <h5 class="card-title">Produtos</h5>
                        <p class="card-text text-muted">Cadastre e gerencie produtos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cadastro de Lojas -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="lojas.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            🏪
                        </div>
                        <h5 class="card-title">Lojas</h5>
                        <p class="card-text text-muted">Cadastre e gerencie lojas</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Cadastro de Características -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="caracteristicas.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            ⚙️
                        </div>
                        <h5 class="card-title">Características</h5>
                        <p class="card-text text-muted">Tipos, categorias e atributos</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Relatório Analítico -->
        <div class="col-12 col-md-6 col-lg-3">
            <a href="relatorio_analitico.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            🏷️
                        </div>
                        <h5 class="card-title">Relatório Analítico</h5>
                        <p class="card-text text-muted">Visualize métricas e análises</p>
                    </div>
                </div>
            </a>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <a href="relAnalitico.php" class="text-decoration-none">
                <div class="card dashboard-card shadow-sm h-100">
                    <div class="card-body text-center">
                        <div class="icon-placeholder mb-3">
                            🏷️
                        </div>
                        <h5 class="card-title">Relatório Analítico</h5>
                        <p class="card-text text-muted">Visualize métricas e análises</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
