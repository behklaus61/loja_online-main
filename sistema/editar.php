<?php 
include 'conexao.php';

$id = $_POST['btnEditar'];

// Buscar o produto
$sql = $pdo->prepare("SELECT * FROM Produto WHERE id = ?");
$sql->execute([$id]);
$linha = $sql->fetch(PDO::FETCH_ASSOC);

// Buscar ENUM do campo tipo
$sql = $pdo->prepare("
    SELECT COLUMN_TYPE
    FROM INFORMATION_SCHEMA.COLUMNS
    WHERE TABLE_SCHEMA = ?
    AND TABLE_NAME = 'produto'
    AND COLUMN_NAME = 'tipo'
");
$sql->execute([$banco]);
$colunaTipo = $sql->fetchColumn();

// Extrair valores do ENUM
// Exemplo: enum('novo','usado','reformado')
$colunaTipo = str_replace(["enum(", ")", "'"], "", $colunaTipo);
$opcoesTipo = explode(",", $colunaTipo);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <title>Editar Produto</title>
</head>
<body>
    <div class="container my-4">
        <h1>Editar o produto: <?php echo htmlspecialchars($linha['nome']); ?></h1>
        

        <form action="atualizar.php" method="POST">

            <input type="hidden" name="id" value="<?php echo $linha['id']; ?>">

            <input type="text" name="nome" 
                value="<?php echo $linha['nome']; ?>" 
                class="form-control mb-2">

            <input type="text" name="descricao"
                value="<?php echo $linha['descricao']; ?>" 
                class="form-control mb-2">

            <input type="text" name="preco"
                value="<?php echo $linha['preco']; ?>" 
                class="form-control mb-2">

            <!-- SELECT VÁLIDO DO ENUM -->
            <select name="tipo" class="form-select mb-2">
                <option disabled selected>Selecione um tipo...</option>

                <?php foreach ($opcoesTipo as $tipo): ?>
                    <option value="<?php echo $tipo; ?>"
                        <?php echo ($linha['tipo'] == $tipo) ? 'selected' : ''; ?>>
                        <?php echo ucfirst($tipo); ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="text" name="categoria"
                value="<?php echo $linha['categoria']; ?>" 
                class="form-control mb-2">

            <input type="date" name="data"
                value="<?php echo $linha['data_lancamento']; ?>" 
                class="form-control mb-2">

            <input type="text" name="desconto"
                value="<?php echo $linha['desconto_usados']; ?>" 
                class="form-control mb-2">

            <input type="submit" name="btnSalvar" value="Salvar"
                class="btn btn-primary mt-2">
        </form>
    </div>

</body>
</html>