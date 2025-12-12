<?php
session_start();
require_once 'conexao.php';

/**
 * Script de Autenticação Segura
 * Valida entrada, busca cliente, verifica senha
 */

// ===== VALIDAÇÃO DE ENTRADA =====

// Obter e limpar dados do formulário
$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

// 1. Validar se campos não estão vazios
if ($email === '' || $senha === '') {
    header("Location: login.php?erro=1");
    exit;
}

// 2. Validar formato de email (validação básica)
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header("Location: login.php?erro=1");
    exit;
}

// 3. Validar comprimento mínimo da senha (segurança)
if (strlen($senha) < 1) {
    header("Location: login.php?erro=1");
    exit;
}

// ===== BUSCAR CLIENTE NO BANCO =====

// Usar prepared statement para evitar SQL injection
$stmt = $pdo->prepare("SELECT id, nome, email, senha_hash, tipo FROM Cliente WHERE email = ?");

if (!$stmt) {
    header("Location: login.php?erro=1");
    exit;
}

$stmt->execute([$email]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

// ===== VALIDAR CREDENCIAIS =====

// Se cliente não existe ou senha não corresponde, rejeitar
// IMPORTANTE: Usar mensagem genérica para não revelar se email existe
if (!$cliente || !password_verify($senha, $cliente['senha_hash'])) {
    // Log da tentativa falha (opcional - útil para segurança)
    error_log("Tentativa de login falha para email: " . $email . " em " . date('Y-m-d H:i:s'));
    
    header("Location: login.php?erro=1");
    exit;
}

// ===== AUTENTICAÇÃO BEM-SUCEDIDA =====

// Inicializar sessão com dados do cliente
$_SESSION['cliente_id'] = $cliente['id'];
$_SESSION['cliente_nome'] = $cliente['nome'];
$_SESSION['cliente_email'] = $cliente['email'];
$_SESSION['cliente_tipo'] = $cliente['tipo'];
$_SESSION['login_time'] = time(); // Para possível timeout de sessão

// Log de login bem-sucedido (opcional - útil para auditoria)
error_log("Login bem-sucedido para: " . $cliente['email'] . " (" . $cliente['tipo'] . ") em " . date('Y-m-d H:i:s'));

// ===== REDIRECIONAR CONFORME TIPO DE USUÁRIO =====

switch ($cliente['tipo']) {
    case 'admin':
        // Administrador → Painel de Administração
        header("Location: index.php");
        break;
    
    case 'cliente':
        // Cliente → Catálogo de Produtos
        header("Location: vizu_cliente.php");
        break;
    
    default:
        // Tipo desconhecido (segurança)
        session_destroy();
        header("Location: login.php?erro=1");
        break;
}

exit;
?>
