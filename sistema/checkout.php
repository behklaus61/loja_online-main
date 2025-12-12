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

// Buscar carrinho do cliente
$stmt = $pdo->prepare("SELECT ct.id_produto, ct.id_loja, ct.quantidade, p.nome, p.preco, p.desconto_usados 
                       FROM CarrinhoTemporario ct 
                       INNER JOIN Produto p ON ct.id_produto = p.id 
                       WHERE ct.id_cliente = ?");
$stmt->execute([$_SESSION['cliente_id']]);
$itens_carrinho = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Se carrinho está vazio, redirecionar
if (empty($itens_carrinho)) {
    header("Location: carrinho.php");
    exit;
}

// --- PROCESSAR VENDA ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_loja = intval($_POST['loja'] ?? 0);
    
    if ($id_loja <= 0) {
        $erro = "Selecione uma loja para entrega.";
    } else {
        try {
            // Iniciar transação
            $pdo->beginTransaction();
            
            // VALIDAR ESTOQUE - Verificar disponibilidade ANTES de qualquer INSERT
            foreach ($itens_carrinho as $item) {
                $stmt = $pdo->prepare("SELECT quantidade_disponivel FROM Estoque 
                                       WHERE id_produto = ? AND id_loja = ? 
                                       AND quantidade_disponivel >= ?");
                $stmt->execute([$item['id_produto'], $item['id_loja'], $item['quantidade']]);
                $estoque = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$estoque) {
                    throw new Exception("Produto '{$item['nome']}' não tem quantidade suficiente na loja selecionada.");
                }
            }
            
            // Calcular total
            $total = 0;
            foreach ($itens_carrinho as $item) {
                $preco_final = $item['preco'] - $item['desconto_usados'];
                $total += $preco_final * $item['quantidade'];
            }
            
            // Inserir venda
            $stmt = $pdo->prepare("INSERT INTO Venda (id_cliente, id_loja, valor_total) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['cliente_id'], $id_loja, $total]);
            $id_venda = $pdo->lastInsertId();
            
            // Inserir itens da venda E atualizar estoque (apenas após validação bem-sucedida)
            foreach ($itens_carrinho as $item) {
                $preco_final = $item['preco'] - $item['desconto_usados'];
                $stmt = $pdo->prepare("INSERT INTO ItemVenda (id_venda, id_produto, quantidade, preco_unitario) 
                                       VALUES (?, ?, ?, ?)");
                $stmt->execute([$id_venda, $item['id_produto'], $item['quantidade'], $preco_final]);
                
                // Atualizar estoque da loja onde o produto foi adicionado ao carrinho
                // Usa quantidade_disponível >= quantidade para garantir disponibilidade
                $stmt = $pdo->prepare("UPDATE Estoque SET quantidade_disponivel = quantidade_disponivel - ? 
                                       WHERE id_produto = ? AND id_loja = ? 
                                       AND quantidade_disponivel >= ?");
                $resultado = $stmt->execute([$item['quantidade'], $item['id_produto'], $item['id_loja'], $item['quantidade']]);
                
                if ($stmt->rowCount() === 0) {
                    throw new Exception("Falha ao atualizar estoque do produto '{$item['nome']}'. Quantidade insuficiente.");
                }
            }
            
            // Limpar carrinho do banco de dados
            $stmt = $pdo->prepare("DELETE FROM CarrinhoTemporario WHERE id_cliente = ?");
            $stmt->execute([$_SESSION['cliente_id']]);
            
            // Confirmar transação
            $pdo->commit();
            
            // Redirecionar para confirmação
            header("Location: confirmacao.php?venda=" . $id_venda);
            exit;
            
        } catch (Exception $e) {
            $pdo->rollBack();
            $erro = "Erro ao processar pedido: " . $e->getMessage();
        }
    }
}

// Buscar lojas
$stmt = $pdo->query("SELECT * FROM Loja ORDER BY cidade, nome");
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calcular total
$total = 0;
foreach ($itens_carrinho as $item) {
    $preco_final = $item['preco'] - $item['desconto_usados'];
    $total += $preco_final * $item['quantidade'];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="vizu_cliente.php">🛍️ Loja Online</a>
            <div class="navbar-collapse">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="nav-link">Bem-vindo, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>!</span>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row">
            <div class="col-lg-8">
                <h1 class="mb-4">Finalizar Compra</h1>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger"><?php echo htmlspecialchars($erro); ?></div>
                <?php endif; ?>

                <form method="POST" class="needs-validation">
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0">Selecione o Local de Entrega</h5>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <label for="loja" class="form-label">Loja para Retirada *</label>
                                <select name="loja" id="loja" class="form-select" required>
                                    <option value="">-- Selecione uma loja --</option>
                                    <?php foreach ($lojas as $loja): ?>
                                        <option value="<?php echo $loja['id']; ?>">
                                            <?php echo htmlspecialchars($loja['nome']); ?> 
                                            (<?php echo htmlspecialchars($loja['cidade']); ?>) 
                                            - <?php echo htmlspecialchars($loja['telefone']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <small class="text-muted d-block mt-2">Você poderá retirar seu pedido na loja selecionada.</small>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-dark text-light">
                            <h5 class="mb-0">Resumo dos Itens</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Produto</th>
                                            <th>Qtd</th>
                                            <th>Preço</th>
                                            <th>Subtotal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($itens_carrinho as $item): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($item['nome']); ?></td>
                                            <td><?php echo $item['quantidade']; ?></td>
                                            <td>R$ <?php echo number_format($item['preco'] - $item['desconto_usados'], 2, ',', '.'); ?></td>
                                            <td>R$ <?php echo number_format(($item['preco'] - $item['desconto_usados']) * $item['quantidade'], 2, ',', '.'); ?></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-2">
                        <a href="carrinho.php" class="btn btn-secondary">Voltar ao Carrinho</a>
                        <button type="submit" class="btn btn-success btn-lg flex-grow-1">Confirmar Pedido</button>
                    </div>
                </form>
            </div>

            <!-- Resumo do Pedido -->
            <div class="col-lg-4">
                <div class="card sticky-top" style="top: 20px;">
                    <div class="card-header bg-dark text-light">
                        <h5 class="mb-0">Total do Pedido</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-3">
                            <span>Subtotal:</span>
                            <strong>R$ <?php echo number_format($total, 2, ',', '.'); ?></strong>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Frete:</span>
                            <strong>Grátis</strong>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between">
                            <h5>Total:</h5>
                            <h5>R$ <?php echo number_format($total, 2, ',', '.'); ?></h5>
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
