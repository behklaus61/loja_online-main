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

// --- ADICIONAR PRODUTO AO CARRINHO ---
if (isset($_POST['adicionar'])) {
    $produto_id = intval($_POST['produto_id']);
    $loja_id = intval($_POST['loja_id'] ?? 0);
    $quantidade = intval($_POST['quantidade'] ?? 1);
    
    if ($quantidade < 1) $quantidade = 1;
    
    // Buscar produto
    $stmt = $pdo->prepare("SELECT * FROM Produto WHERE id = ?");
    $stmt->execute([$produto_id]);
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($produto && $loja_id > 0) {
        // Verificar estoque
        $stmt = $pdo->prepare("SELECT quantidade_disponivel FROM Estoque WHERE id_produto = ? AND id_loja = ?");
        $stmt->execute([$produto_id, $loja_id]);
        $estoque = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($estoque && $estoque['quantidade_disponivel'] > 0) {
            // Verificar se já existe no carrinho (mesmo produto, mesma loja)
            $stmt = $pdo->prepare("SELECT id, quantidade FROM CarrinhoTemporario 
                                   WHERE id_cliente = ? AND id_produto = ? AND id_loja = ?");
            $stmt->execute([$_SESSION['cliente_id'], $produto_id, $loja_id]);
            $item_carrinho = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($item_carrinho) {
                // Atualizar quantidade
                $nova_quantidade = $item_carrinho['quantidade'] + $quantidade;
                $stmt = $pdo->prepare("UPDATE CarrinhoTemporario SET quantidade = ? WHERE id = ?");
                $stmt->execute([$nova_quantidade, $item_carrinho['id']]);
            } else {
                // Inserir novo item
                $stmt = $pdo->prepare("INSERT INTO CarrinhoTemporario (id_cliente, id_produto, id_loja, quantidade) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$_SESSION['cliente_id'], $produto_id, $loja_id, $quantidade]);
            }
        }
    }
    
    header("Location: carrinho.php");
    exit;
}

// --- REMOVER PRODUTO DO CARRINHO ---
if (isset($_GET['remover'])) {
    $carrinho_id = intval($_GET['remover']);
    $stmt = $pdo->prepare("DELETE FROM CarrinhoTemporario WHERE id = ? AND id_cliente = ?");
    $stmt->execute([$carrinho_id, $_SESSION['cliente_id']]);
    header("Location: carrinho.php");
    exit;
}

// --- ATUALIZAR QUANTIDADE ---
if (isset($_POST['atualizar_quantidade'])) {
    $carrinho_id = intval($_POST['carrinho_id']);
    $quantidade = intval($_POST['quantidade']);
    
    if ($quantidade < 1) {
        // Se quantidade é 0 ou negativa, remover
        $stmt = $pdo->prepare("DELETE FROM CarrinhoTemporario WHERE id = ? AND id_cliente = ?");
        $stmt->execute([$carrinho_id, $_SESSION['cliente_id']]);
    } else {
        // Atualizar quantidade
        $stmt = $pdo->prepare("UPDATE CarrinhoTemporario SET quantidade = ? WHERE id = ? AND id_cliente = ?");
        $stmt->execute([$quantidade, $carrinho_id, $_SESSION['cliente_id']]);
    }
    
    header("Location: carrinho.php");
    exit;
}

// Buscar carrinho do cliente com todos os detalhes
// Consulta otimizada com JOINs entre Produto, Estoque e Loja
// Inclui cálculo de preço com desconto e validação de estoque
$stmt = $pdo->prepare("SELECT ct.id, 
                              ct.id_produto, 
                              ct.id_loja, 
                              ct.quantidade,
                              p.nome as produto_nome, 
                              p.categoria,
                              p.preco,
                              p.desconto_usados,
                              (p.preco - p.desconto_usados) as preco_com_desconto,
                              (ct.quantidade * (p.preco - p.desconto_usados)) as subtotal,
                              l.nome as loja_nome, 
                              l.cidade,
                              e.quantidade_disponivel as estoque_disponivel
                       FROM CarrinhoTemporario ct 
                       INNER JOIN Produto p ON ct.id_produto = p.id 
                       INNER JOIN Loja l ON ct.id_loja = l.id
                       INNER JOIN Estoque e ON ct.id_produto = e.id_produto 
                                              AND ct.id_loja = e.id_loja
                       WHERE ct.id_cliente = ? 
                       AND e.quantidade_disponivel > 0
                       ORDER BY ct.data_adicao DESC");
$stmt->execute([$_SESSION['cliente_id']]);
$itens_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular totais
$total = 0;
$total_itens = 0;
$estoque_insuficiente = false;

foreach ($itens_carrinho as $item) {
    $total_itens += $item['quantidade'];
    $total += $item['subtotal'];
    
    // Verificar se estoque é insuficiente para a quantidade no carrinho
    if ($item['estoque_disponivel'] < $item['quantidade']) {
        $estoque_insuficiente = true;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrinho de Compras</title>
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
                        <a class="nav-link active" href="carrinho.php">🛒 Carrinho (<?php echo count($itens_carrinho); ?>)</a>
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
        <h1 class="mb-4">Carrinho de Compras</h1>

        <?php if (empty($itens_carrinho)): ?>
            <div class="alert alert-info">
                <p>Seu carrinho está vazio.</p>
                <a href="vizu_cliente.php" class="btn btn-primary">Continuar Comprando</a>
            </div>
        <?php else: ?>
            <?php if ($estoque_insuficiente): ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Aviso:</strong> Alguns itens não têm quantidade suficiente em estoque na loja selecionada.
                </div>
            <?php endif; ?>

            <div class="table-wrapper mb-4">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Produto</th>
                                <th>Categoria</th>
                                <th>Loja</th>
                                <th class="text-center">Qtd</th>
                                <th class="text-end">Preço</th>
                                <th class="text-end">C/ Desc</th>
                                <th class="text-end">Subtotal</th>
                                <th class="text-center">Ações</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itens_carrinho as $item): 
                                // Verificar se estoque é insuficiente
                                $estoque_ok = $item['estoque_disponivel'] >= $item['quantidade'];
                            ?>
                            <tr class="<?php echo !$estoque_ok ? 'table-warning' : ''; ?>">
                                <td>
                                    <strong><?php echo htmlspecialchars($item['produto_nome']); ?></strong>
                                    <?php if (!$estoque_ok): ?>
                                        <br><small class="text-danger">⚠️ Estoque insuficiente (disponível: <?php echo $item['estoque_disponivel']; ?>)</small>
                                    <?php endif; ?>
                                </td>
                                <td><span class="badge bg-info"><?php echo htmlspecialchars($item['categoria']); ?></span></td>
                                <td><?php echo htmlspecialchars($item['loja_nome']); ?> - <?php echo htmlspecialchars($item['cidade']); ?></td>
                                <td class="text-center">
                                    <form method="POST" class="d-inline" style="width: 80px;">
                                        <input type="hidden" name="carrinho_id" value="<?php echo $item['id']; ?>">
                                        <input type="hidden" name="atualizar_quantidade" value="1">
                                        <input type="number" name="quantidade" value="<?php echo $item['quantidade']; ?>" min="1" max="999" class="form-control form-control-sm" onchange="this.form.submit()">
                                    </form>
                                </td>
                                <td class="text-end">R$ <?php echo number_format($item['preco'], 2, ',', '.'); ?></td>
                                <td class="text-end">
                                    <?php if ($item['desconto_usados'] > 0): ?>
                                        <strong>R$ <?php echo number_format($item['preco_com_desconto'], 2, ',', '.'); ?></strong>
                                        <br><small class="text-success">-R$ <?php echo number_format($item['desconto_usados'], 2, ',', '.'); ?></small>
                                    <?php else: ?>
                                        <strong>R$ <?php echo number_format($item['preco_com_desconto'], 2, ',', '.'); ?></strong>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end"><strong>R$ <?php echo number_format($item['subtotal'], 2, ',', '.'); ?></strong></td>
                                <td class="text-center">
                                    <a href="?remover=<?php echo $item['id']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Remover do carrinho?')">Remover</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Resumo -->
                <div class="row mt-4 pt-3 border-top">
                    <div class="col-md-8"></div>
                    <div class="col-md-4">
                        <div class="mb-2">
                            <h6>Total de Itens: <strong><?php echo $total_itens; ?></strong></h6>
                        </div>
                        <div class="mb-3">
                            <h5>Valor Total: <strong class="text-success">R$ <?php echo number_format($total, 2, ',', '.'); ?></strong></h5>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botões de Ação -->
            <div class="d-flex gap-2 justify-content-between">
                <a href="vizu_cliente.php" class="btn btn-secondary">Continuar Comprando</a>
                <form method="POST" action="checkout.php" class="w-auto">
                    <button type="submit" class="btn btn-success btn-lg" <?php echo $estoque_insuficiente ? 'disabled' : ''; ?>>
                        🛒 Finalizar Compra
                    </button>
                </form>
            </div>

            <?php if ($estoque_insuficiente): ?>
                <div class="alert alert-danger mt-3">
                    <strong>Não é possível finalizar a compra:</strong> Alguns itens não têm quantidade suficiente em estoque na loja. 
                    Por favor, ajuste as quantidades ou remova os itens com estoque insuficiente.
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <p>&copy; 2025 Loja Online. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
