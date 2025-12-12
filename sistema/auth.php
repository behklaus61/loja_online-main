<?php
session_start();
require 'conexao.php';

$email = $_POST['email'] ?? '';
$senha = $_POST['senha'] ?? '';

// Busca usuário na tabela Cliente
$sql = $pdo->prepare("SELECT * FROM Cliente WHERE email = ?");
$sql->execute([$email]);
$user = $sql->fetch(PDO::FETCH_ASSOC);

// Verifica senha com bcrypt
if ($user && !password_verify($senha, $user['senha_hash'])) {
    $user = null;
}

if ($user) {
    // Guardar dados na sessão
    $_SESSION['user'] = [
        'id' => $user['id'],
        'nome' => $user['nome']
    ];

    // Redireciona para o painel
    header("Location: painel.php");
    exit;
}

// Falhou → volta pro login
header("Location: login.php?erro=1");
exit;
