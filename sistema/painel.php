<?php
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Painel</title>
</head>
<body>

<h2>Bem-vindo, <?php echo htmlspecialchars($_SESSION['cliente_nome']); ?>!</h2>

<p>Login realizado com sucesso.</p>

</body>
</html>
