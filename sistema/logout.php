<?php
session_start();

// Limpa todas as variáveis da sessão
$_SESSION = array();

// Mata o cookie de sessão se existir
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destrói a sessão no servidor
session_destroy();

// Redireciona para login com mensagem de logout bem-sucedido
header("Location: login.php?logout=1");
exit;
