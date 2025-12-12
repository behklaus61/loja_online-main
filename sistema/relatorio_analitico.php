<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

include 'conexao.php';

// 1. Quantidade total de produtos diferentes
$stmt1 = $pdo->query("SELECT COUNT(DISTINCT id) AS total FROM Produto");
$totalProdutos = $stmt1->fetch(PDO::FETCH_ASSOC)['total'];

// 2. Média de preço por categoria (tratando SET com GROUP BY direto)
$stmt2 = $pdo->query("
    SELECT categoria, AVG(preco) AS media_preco 
    FROM Produto 
    GROUP BY categoria
");
$mediaPorCategoria = $stmt2->fetchAll(PDO::FETCH_ASSOC);

// 3. Produto mais caro e mais barato de cada loja
$stmt3 = $pdo->query("
    SELECT 
        l.nome AS loja,
        MAX(p.preco) AS preco_max,
        (SELECT p2.nome FROM Produto p2 JOIN Estoque e2 ON p2.id = e2.id_produto WHERE e2.id_loja = l.id ORDER BY p2.preco DESC LIMIT 1) AS produto_mais_caro,
        MIN(p.preco) AS preco_min,
        (SELECT p3.nome FROM Produto p3 JOIN Estoque e3 ON p3.id = e3.id_produto WHERE e3.id_loja = l.id ORDER BY p3.preco ASC LIMIT 1) AS produto_mais_barato
    FROM Loja l
    JOIN Estoque e ON l.id = e.id_loja
    JOIN Produto p ON e.id_produto = p.id
    GROUP BY l.id, l.nome
");
$produtosPorLoja = $stmt3->fetchAll(PDO::FETCH_ASSOC);

// 4. Total de produtos em estoque por cidade
$stmt4 = $pdo->query("
    SELECT l.cidade, SUM(e.quantidade_disponivel) AS total_estoque
    FROM Estoque e
    JOIN Loja l ON e.id_loja = l.id
    GROUP BY l.cidade
");
$estoquePorCidade = $stmt4->fetchAll(PDO::FETCH_ASSOC);

// 5. Produtos lançados por mês em 2023
$stmt5 = $pdo->query("
    SELECT 
        DATE_FORMAT(data_lancamento, '%M') AS mes,
        COUNT(*) AS qtd
    FROM Produto
    WHERE YEAR(data_lancamento) = 2023
    GROUP BY DATE_FORMAT(data_lancamento, '%Y-%m')
    ORDER BY data_lancamento
");
$lancamentos2023 = $stmt5->fetchAll(PDO::FETCH_ASSOC);

// 6. Lojas com mais de 20 produtos em estoque (total somado)
$stmt6 = $pdo->query("
    SELECT 
        l.nome AS loja,
        SUM(e.quantidade_disponivel) AS total_itens
    FROM Loja l
    JOIN Estoque e ON l.id = e.id_loja
    GROUP BY l.id
    HAVING total_itens > 20
    ORDER BY total_itens DESC
");
$lojasComMaisDe20 = $stmt6->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório com Funções MySQL</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .card-header {
            font-weight: bold;
            background-color: #f8f9fa;
        }
        .list-group-item {
            border-left: 3px solid #0d6efd;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container py-5">
        <h1 class="text-center mb-5 display-5 fw-bold">Relatório com Funções Embutidas do MySQL</h1>

        
        <!-- Linha 1 -->
        <div class="row g-4 mb-5">
            <!-- Card 1: Total de produtos -->
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">Total de Produtos Únicos</div>
                    <div class="card-body d-flex align-items-center justify-content-center">
                        <h2 class="display-4 text-primary"><?= $totalProdutos ?></h2>
                    </div>
                </div>
            </div>

            <!-- Card 2: Média por categoria -->
            <div class="col-12 col-md-6 col-lg-8">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">Média de Preço por Categoria</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($mediaPorCategoria as $cat): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= htmlspecialchars($cat['categoria']) ?></span>
                                    <span>R$ <?= number_format($cat['media_preco'], 2, ',', '.') ?></span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha 2 -->
        <div class="row g-4 mb-5">
            <!-- Card 3: Produto mais caro/barato por loja -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">Produto Mais Caro e Mais Barato por Loja</div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Loja</th>
                                        <th>Mais Caro (Produto - Preço)</th>
                                        <th>Mais Barato (Produto - Preço)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($produtosPorLoja as $item): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($item['loja']) ?></td>
                                            <td><?= htmlspecialchars($item['produto_mais_caro'] ?? '—') ?> - R$ <?= number_format($item['preco_max'], 2, ',', '.') ?></td>
                                            <td><?= htmlspecialchars($item['produto_mais_barato'] ?? '—') ?> - R$ <?= number_format($item['preco_min'], 2, ',', '.') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha 3 -->
        <div class="row g-4 mb-5">
            <!-- Card 4: Estoque por cidade -->
            <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">Total em Estoque por Cidade</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($estoquePorCidade as $cidade): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= htmlspecialchars($cidade['cidade']) ?></span>
                                    <span><?= $cidade['total_estoque'] ?> unidades</span>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Card 5: Lançamentos por mês (2023) -->
            <div class="col-12 col-md-6">
                <div class="card h-100 shadow-sm">
                    <div class="card-header">Lançamentos em 2023 (por mês)</div>
                    <div class="card-body">
                        <ul class="list-group">
                            <?php foreach ($lancamentos2023 as $mes): ?>
                                <li class="list-group-item d-flex justify-content-between">
                                    <span><?= htmlspecialchars($mes['mes']) ?></span>
                                    <span><?= $mes['qtd'] ?> produto(s)</span>
                                </li>
                            <?php endforeach; ?>
                            <!-- Preencher meses com 0 se necessário (opcional) -->
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Linha 4 -->
        <div class="row g-4">
            <!-- Card 6: Lojas com +20 itens -->
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header">Lojas com Mais de 20 Itens em Estoque (Total)</div>
                    <div class="card-body">
                        <?php if ($lojasComMaisDe20): ?>
                            <div class="table-responsive">
                                <table class="table table-striped">
                                    <thead>
                                        <tr>
                                            <th>Loja</th>
                                            <th>Total de Itens em Estoque</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($lojasComMaisDe20 as $loja): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($loja['loja']) ?></td>
                                                <td><?= $loja['total_itens'] ?></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php else: ?>
                            <p class="text-muted">Nenhuma loja com mais de 20 itens em estoque.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>