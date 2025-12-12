<?php
    include 'conexao.php';
    $id = $_POST['id'];
    $nome = $_POST['nome'];
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $tipo = $_POST['tipo'];
    $categoria = $_POST['categoria'];
    $data = $_POST['data'];
    $desconto = $_POST['desconto'];

    $sql = $pdo->prepare("UPDATE Produto SET nome = ?, descricao = ?, 
    preco = ?, tipo = ?, categoria = ?, data_lancamento = ?, 
    desconto_usados = ? WHERE id = ?");
    $sql->execute([$nome, $descricao, $preco, $tipo, $categoria, $data, $desconto, $id]);

    header("Location: produtos.php");
    exit;
?>