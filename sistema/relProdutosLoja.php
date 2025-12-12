<?php 
    session_start();
    
    // Verificar se está logado
    if (!isset($_SESSION['cliente_id'])) {
        header("Location: login.php");
        exit;
    }
    
    include 'conexao.php';

    // Valores padrão
    $nome = isset($_POST['txtNome']) ? trim($_POST['txtNome']) : '';
    $cidade = isset($_POST['txtCidade']) ? trim($_POST['txtCidade']) : '';
    $ordenarPor = isset($_POST['ordenarPor']) ? $_POST['ordenarPor'] : 'data_lancamento'; // padrão: data

    // Validação simples do campo de ordenação
    $ordenarPor = in_array($ordenarPor, ['preco', 'data_lancamento']) ? $ordenarPor : 'data_lancamento';

    // Monta a query com condições dinâmicas
    $sqlFiltros = [];
    $params = [];

    if (!empty($nome)) {
        $sqlFiltros[] = "nomeprod LIKE ?";
        $params[] = '%' . $nome . '%';
    }

    if (!empty($cidade)) {
        $sqlFiltros[] = "cidade LIKE ?";
        $params[] = '%' . $cidade . '%';
    }

    $whereClause = !empty($sqlFiltros) ? 'WHERE ' . implode(' AND ', $sqlFiltros) : '';

    $sql = $pdo->prepare("SELECT * FROM viewProdutosLoja $whereClause ORDER BY $ordenarPor DESC");
    $sql->execute($params);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <title>Página Principal</title>
</head>
<body>
    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
    <h1 class="display-5 fw-bold">Produtos por Loja</h1>
    <span class="badge bg-secondary"><?= $sql->rowCount() ?> produtos encontrados</span>
</div>

        <form action="relProdutosLoja.php" method="POST" class="mb-4">
            <div class="row g-2">
                <div class="col-md-4">
                    <input type="text" name="txtNome" value="<?= htmlspecialchars($nome) ?>" 
                           placeholder="Buscar produto..." class="form-control">
                </div>
                <div class="col-md-3">
                    <input type="text" name="txtCidade" value="<?= htmlspecialchars($cidade) ?>" 
                           placeholder="Cidade..." class="form-control">
                </div>
                <div class="col-md-3">
                    <select name="ordenarPor" class="form-select">
                        <option value="data_lancamento" <?= $ordenarPor == 'data_lancamento' ? 'selected' : '' ?>>
                            Ordenar por Data
                        </option>
                        <option value="preco" <?= $ordenarPor == 'preco' ? 'selected' : '' ?>>
                            Ordenar por Preço
                        </option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" name="btnBuscar" class="btn btn-primary w-100">Buscar</button>
                </div>
            </div>
        </form>

        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nome</th>
                    <th>Descrição</th>
                    <th>Preço</th>
                    <th>Tipo</th>
                    <th>Categoria</th>
                    <th>Lançamento</th>
                    <th>Desconto</th>
                    <th>Quantidade</th>
                    <th>Cidade</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                    while($linha = $sql->fetch(PDO::FETCH_ASSOC)) {
                ?>
                <tr>
                    <td><?= htmlspecialchars($linha['nomeprod']) ?></td>
                    <td><?= htmlspecialchars($linha['descricao']) ?></td>
                    <td><?= number_format($linha['preco'], 2, ',', '.') ?></td>
                    <td><?= htmlspecialchars($linha['tipo']) ?></td>
                    <td><?= htmlspecialchars($linha['categoria']) ?></td>
                    <td><?= date('d/m/Y', strtotime($linha['data_lancamento'])) ?></td>
                    <td><?= htmlspecialchars($linha['desconto_usados']) ?></td>
                    <td><?= htmlspecialchars($linha['quantidade_disponivel']) ?></td>
                    <td><?= htmlspecialchars($linha['cidade']) ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>