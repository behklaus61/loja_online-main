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

$id_venda = intval($_GET['venda'] ?? 0);

if ($id_venda <= 0) {
    header("Location: vizu_cliente.php");
    exit;
}

// Buscar venda
$stmt = $pdo->prepare("SELECT v.*, l.nome as nome_loja, l.cidade, l.telefone 
                       FROM Venda v 
                       LEFT JOIN Loja l ON v.id_loja = l.id 
                       WHERE v.id = ? AND v.id_cliente = ?");
$stmt->execute([$id_venda, $_SESSION['cliente_id']]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    header("Location: vizu_cliente.php");
    exit;
}

// Buscar itens da venda
$stmt = $pdo->prepare("SELECT iv.*, p.nome FROM ItemVenda iv 
                       LEFT JOIN Produto p ON iv.id_produto = p.id 
                       WHERE iv.id_venda = ?");
$stmt->execute([$id_venda]);
$itens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedido Confirmado</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .check-mark {
            font-size: 4rem;
            color: #28a745;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="vizu_cliente.php">🛍️ Loja Online</a>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Confirmação -->
                <div class="card mb-4 border-success">
                    <div class="card-body text-center py-5">
                        <div class="check-mark mb-3">✓</div>
                        <h1 class="text-success mb-3">Pedido Confirmado!</h1>
                        <p class="text-muted fs-5">Seu pedido foi processado com sucesso.</p>
                        <h4 class="mt-4">Número do Pedido: <strong>#<?php echo str_pad($venda['id'], 6, '0', STR_PAD_LEFT); ?></strong></h4>
                    </div>
                </div>

                <!-- Detalhes da Entrega -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">📍 Local de Retirada</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($venda['nome_loja']); ?></h6>
                        <p class="mb-1">
                            <strong>Cidade:</strong> <?php echo htmlspecialchars($venda['cidade']); ?>
                        </p>
                        <p class="mb-1">
                            <strong>Telefone:</strong> <?php echo htmlspecialchars($venda['telefone']); ?>
                        </p>
                        <p class="text-muted small mt-2">Entre em contato com a loja para confirmar a disponibilidade dos itens.</p>
                    </div>
                </div>

                <!-- Itens do Pedido -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">📦 Itens do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Quantidade</th>
                                        <th>Preço Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">💰 Resumo Financeiro</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-6 text-start">
                                <p>Data do Pedido:</p>
                                <p>Total:</p>
                            </div>
                            <div class="col-6 text-end">
                                <p><strong><?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></strong></p>
                                <p><strong class="text-success fs-5">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></strong></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Próximas Ações -->
                <div class="d-grid gap-2">
                    <a href="vizu_cliente.php" class="btn btn-primary btn-lg">Continuar Comprando</a>
                    <a href="minhas_vendas.php" class="btn btn-outline-primary">Meus Pedidos</a>
                </div>
            </div>
        </div>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <p>&copy; 2025 Loja Online. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
