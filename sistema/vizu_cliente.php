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

// Contar itens do carrinho
$stmt = $pdo->prepare("SELECT COUNT(*) as total FROM CarrinhoTemporario WHERE id_cliente = ?");
$stmt->execute([$_SESSION['cliente_id']]);
$count_carrinho = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

// Buscar produtos para o cliente visualizar
$stmt = $pdo->query("SELECT * FROM Produto ORDER BY data_lancamento DESC");
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loja Online - Cliente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .product-card {
            transition: transform 0.2s, box-shadow 0.2s;
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,0.15);
        }
        .price {
            font-size: 1.5rem;
            color: #28a745;
            font-weight: bold;
        }
        .discount {
            color: #dc3545;
            text-decoration: line-through;
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
                        <a class="nav-link" href="carrinho.php">🛒 Carrinho (<?php echo $count_carrinho; ?>)</a>
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
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="text-center mb-4">Catálogo de Produtos</h1>
                <p class="text-center text-muted">Confira nossos produtos disponíveis</p>
            </div>
        </div>

        <!-- Produtos -->
        <div class="row g-4">
            <?php if (empty($produtos)): ?>
                <div class="col-12">
                    <div class="alert alert-info text-center" role="alert">
                        Nenhum produto disponível no momento.
                    </div>
                </div>
            <?php else: ?>
                <?php foreach ($produtos as $produto): ?>
                <div class="col-12 col-md-6 col-lg-4">
                    <div class="card product-card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo htmlspecialchars($produto['nome']); ?></h5>
                            <p class="card-text text-muted"><?php echo htmlspecialchars(substr($produto['descricao'], 0, 100)) . '...'; ?></p>
                            
                            <div class="mb-3">
                                <span class="badge bg-info"><?php echo htmlspecialchars($produto['tipo']); ?></span>
                                <span class="badge bg-secondary"><?php echo htmlspecialchars($produto['categoria']); ?></span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <div>
                                    <?php if ($produto['desconto_usados'] > 0): ?>
                                        <span class="discount">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span><br>
                                        <span class="price">R$ <?php echo number_format($produto['preco'] - $produto['desconto_usados'], 2, ',', '.'); ?></span>
                                    <?php else: ?>
                                        <span class="price">R$ <?php echo number_format($produto['preco'], 2, ',', '.'); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <p class="text-muted small">Lançamento: <?php echo date('d/m/Y', strtotime($produto['data_lancamento'])); ?></p>
                            
                            <form method="POST" action="carrinho.php" class="mt-3">
                                <input type="hidden" name="produto_id" value="<?php echo $produto['id']; ?>">
                                <input type="hidden" name="adicionar" value="1">
                                
                                <div class="mb-2">
                                    <label class="form-label small">Escolha a loja:</label>
                                    <select name="loja_id" class="form-select form-select-sm" required>
                                        <option value="">-- Selecione uma loja --</option>
                                        <?php 
                                        // Buscar lojas que têm este produto em estoque
                                        $stmt = $pdo->prepare("SELECT DISTINCT l.id, l.nome, l.cidade, e.quantidade_disponivel
                                                               FROM Loja l
                                                               INNER JOIN Estoque e ON l.id = e.id_loja
                                                               WHERE e.id_produto = ? AND e.quantidade_disponivel > 0
                                                               ORDER BY l.nome");
                                        $stmt->execute([$produto['id']]);
                                        $lojas_produto = $stmt->fetchAll(PDO::FETCH_ASSOC);
                                        
                                        foreach ($lojas_produto as $loja):
                                        ?>
                                            <option value="<?php echo $loja['id']; ?>">
                                                <?php echo htmlspecialchars($loja['nome']); ?> - <?php echo htmlspecialchars($loja['cidade']); ?>
                                                (<?php echo $loja['quantidade_disponivel']; ?> em estoque)
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="input-group">
                                    <input type="number" name="quantidade" value="1" min="1" max="999" class="form-control" placeholder="Qtd">
                                    <button class="btn btn-primary" type="submit" <?php echo empty($lojas_produto) ? 'disabled' : ''; ?>>
                                        Adicionar ao Carrinho
                                    </button>
                                </div>
                                
                                <?php if (empty($lojas_produto)): ?>
                                    <small class="text-danger d-block mt-2">Produto não disponível em nenhuma loja</small>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <footer class="bg-dark text-light text-center py-4 mt-5">
        <p>&copy; 2025 Loja Online. Todos os direitos reservados.</p>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
