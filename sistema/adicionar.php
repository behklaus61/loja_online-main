<?php 
    include 'conexao.php';

    $nome = $_POST['txtNome'];
    $descricao = $_POST['txtDescricao'];
    $preco = $_POST['txtPreco'];
    $tipo = $_POST['txtTipo'];
    $categoria = $_POST['txtCategoria'];
    $data = $_POST['txtData'];
    $desconto = $_POST['txtDesconto'];

    $sql = $pdo->prepare("INSERT INTO Produto (nome, descricao, 
    preco, tipo, categoria, data_lancamento, desconto_usados)
    VALUES (?, ?, ?)");

    $sql->execute([$nome, $descricao, $preco, $tipo, $categoria, $data, $desconto]);

    header("Location: produtos.php");
    exit;
?>