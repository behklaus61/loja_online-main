<?php 
    session_start();
    
    // Verificar se está logado
    if (!isset($_SESSION['cliente_id'])) {
        header("Location: login.php");
        exit;
    }
    
    include 'conexao.php';
    $cidade = $_POST['txtCidade'];
    $sql = $pdo->prepare("SELECT * FROM viewProdutosLoja WHERE cidade LIKE ?");
    $sql->execute(['%'.$cidade.'%']);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css">
    <title>Página Principal</title>
</head>

<body>

    <div class="container">
        <h1>Página Principal</h1>
        <form action="produtosLoja.php" method="POST">

        <input type="text" placeholder="Buscar produto..." class="form-control mb-3">
        <input type="text" name="txtCidade">
        <input type="submit" value="Buscar" name="btnBuscar" class="btn btn-primary mb-3">
        <table class="table">
            <thead>
                <tr>
                    <th scope="col">Nome</th>
                    <th scope="col">Descrição</th>
                    <th scope="col">Preço</th>
                    <th scope="col">Tipo</th>
                    <th scope="col">Categoria</th>
                    <th scope="col">Data de Lançamento</th>
                    <th scope="col">Desconto</th>
                    <th scope="col">Quantidade</th>
                    <th scope="col">Cidade</th>
                </tr>
            </thead>
            <tbody>
            <?php 
                while($linha = $sql->fetch(PDO::FETCH_ASSOC)){
            ?>
                <tr>
                    <td><?php echo $linha['nomeprod']?></td>
                    <td><?php echo $linha['descricao'] ?></td>
                    <td><?php echo $linha['preco'] ?></td>
                    <td><?php echo $linha['tipo'] ?></td>
                    <td><?php echo $linha['categoria'] ?></td>
                    <td><?php 
                        $partes = explode('-', $linha['data_lancamento']);
                        $data = "".$partes[2]."/".$partes[1]."/".$partes[0];
                        echo $data ?>
                    </td>
                    <td><?php echo $linha['desconto_usados'] ?></td>
                    <td><?php echo $linha['quantidade_disponivel'] ?></td>
                    <td><?php echo $linha['cidade'] ?></td>
                </tr>
            <?php } ?>
            </tbody>
        </table>
        </form>
    </div>
</body>

</html>