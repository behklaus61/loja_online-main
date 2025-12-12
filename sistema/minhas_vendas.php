<?php
session_start();

// Verificar se está logado e é cliente
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SESSION['cliente_tipo'] !== 'cliente') {
    header("Location: index.php");
    exit;
}

require_once 'conexao.php';

// Buscar vendas do cliente
$stmt = $pdo->prepare("SELECT v.id, v.data_venda, v.valor_total, l.nome as nome_loja, l.cidade,
                              COUNT(iv.id) as total_itens
                       FROM Venda v
                       LEFT JOIN Loja l ON v.id_loja = l.id
                       LEFT JOIN ItemVenda iv ON v.id = iv.id_venda
                       WHERE v.id_cliente = ?
                       GROUP BY v.id
                       ORDER BY v.data_venda DESC");
$stmt->execute([$_SESSION['cliente_id']]);
$vendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Pedidos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="vizu_cliente.php">🛍️ Loja Online</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="vizu_cliente.php">Produtos</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="carrinho.php">🛒 Carrinho</a>
                    </li>
                    <li class="nav-item">
                        <span class="nav-link">Bem-vindo, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>!</span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Sair</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Meus Pedidos</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="vizu_cliente.php" class="btn btn-primary">Continuar Comprando</a>
            </div>
        </div>

        <?php if (empty($vendas)): ?>
            <div class="alert alert-info">
                <p>Você ainda não fez nenhum pedido.</p>
                <a href="vizu_cliente.php" class="btn btn-primary">Começar a Comprar</a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Pedido #</th>
                            <th>Data</th>
                            <th>Local de Entrega</th>
                            <th>Itens</th>
                            <th>Total</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($vendas as $venda): ?>
                        <tr>
                            <td><strong>#<?php echo str_pad($venda['id'], 6, '0', STR_PAD_LEFT); ?></strong></td>
                            <td><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></td>
                            <td><?php echo htmlspecialchars($venda['nome_loja']); ?> (<?php echo htmlspecialchars($venda['cidade']); ?>)</td>
                            <td><span class="badge bg-info"><?php echo $venda['total_itens']; ?> itens</span></td>
                            <td><strong>R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></strong></td>
                            <td>
                                <a href="detalhes_venda.php?venda=<?php echo $venda['id']; ?>" class="btn btn-sm btn-primary">Ver Detalhes</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <p>&copy; 2025 Loja Online. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
