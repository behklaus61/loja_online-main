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
    header("Location: minhas_vendas.php");
    exit;
}

// Buscar venda
$stmt = $pdo->prepare("SELECT v.*, l.nome as nome_loja, l.cidade, l.telefone, l.rua, l.numero, l.bairro, l.cep
                       FROM Venda v 
                       LEFT JOIN Loja l ON v.id_loja = l.id 
                       WHERE v.id = ? AND v.id_cliente = ?");
$stmt->execute([$id_venda, $_SESSION['cliente_id']]);
$venda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$venda) {
    header("Location: minhas_vendas.php");
    exit;
}

// Buscar itens da venda
$stmt = $pdo->prepare("SELECT iv.*, p.nome, p.tipo, p.categoria FROM ItemVenda iv 
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
    <title>Detalhes do Pedido</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <div class="mb-4">
            <a href="minhas_vendas.php" class="btn btn-secondary">&larr; Voltar aos Pedidos</a>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <!-- Cabeçalho -->
                <div class="card mb-4">
                    <div class="card-body">
                        <h2 class="mb-3">Pedido #<?php echo str_pad($venda['id'], 6, '0', STR_PAD_LEFT); ?></h2>
                        <div class="row">
                            <div class="col-md-6">
                                <p><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($venda['data_venda'])); ?></p>
                                <p><strong>Status:</strong> <span class="badge bg-success">Confirmado</span></p>
                            </div>
                            <div class="col-md-6 text-end">
                                <p><strong>Total:</strong> <span class="fs-5 text-success">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></span></p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Itens -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">📦 Produtos</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table">
                                <thead class="table-light">
                                    <tr>
                                        <th>Produto</th>
                                        <th>Tipo</th>
                                        <th>Qtd</th>
                                        <th>Preço Unitário</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($itens as $item): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                        <td><span class="badge bg-info"><?php echo htmlspecialchars($item['tipo']); ?></span></td>
                                        <td><?php echo $item['quantidade']; ?></td>
                                        <td>R$ <?php echo number_format($item['preco_unitario'], 2, ',', '.'); ?></td>
                                        <td><strong>R$ <?php echo number_format($item['preco_unitario'] * $item['quantidade'], 2, ',', '.'); ?></strong></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Local de Entrega -->
                <div class="card mb-4">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">📍 Local de Retirada</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($venda['nome_loja']); ?></h6>
                        <p class="mb-1"><?php echo htmlspecialchars($venda['rua']); ?>, <?php echo $venda['numero']; ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($venda['bairro']); ?> - <?php echo htmlspecialchars($venda['cep']); ?></p>
                        <p class="mb-1"><?php echo htmlspecialchars($venda['cidade']); ?></p>
                        <p class="mb-0"><strong>Telefone:</strong> <?php echo htmlspecialchars($venda['telefone']); ?></p>
                    </div>
                </div>

                <!-- Resumo Financeiro -->
                <div class="card">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">💰 Resumo</h5>
                    </div>
                    <div class="card-body">
                        <?php 
                        $subtotal = 0;
                        foreach ($itens as $item) {
                            $subtotal += $item['preco_unitario'] * $item['quantidade'];
                        }
                        ?>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <strong>R$ <?php echo number_format($subtotal, 2, ',', '.'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Frete:</span>
                            <strong>Grátis</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Total:</h5>
                            <h5 class="text-success">R$ <?php echo number_format($venda['valor_total'], 2, ',', '.'); ?></h5>
                        </div>
                    </div>
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
