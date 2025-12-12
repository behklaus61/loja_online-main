<?php
/**
 * Utilitário para Gerar Hashes de Senha
 * Use este script para gerar senhas hasheadas de forma segura
 * 
 * INSTRUÇÕES:
 * 1. Digite a senha desejada no campo abaixo
 * 2. Clique em "Gerar Hash"
 * 3. Copie o hash resultante
 * 4. Use no comando INSERT para adicionar/atualizar clientes
 * 
 * SEGURANÇA:
 * - As senhas são hasheadas usando PASSWORD_DEFAULT (bcrypt)
 * - Nunca armazene senhas em texto plano no banco de dados
 * - Este arquivo deve ser removido após uso em produção
 */

$hash_gerado = null;
$erro = null;
$senha_original = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = $_POST['senha'] ?? '';
    $acao = $_POST['acao'] ?? '';
    
    if ($acao === 'gerar') {
        if (strlen($senha_digitada) < 6) {
            $erro = "⚠️ A senha deve ter no mínimo 6 caracteres.";
        } else {
            $hash_gerado = password_hash($senha_digitada, PASSWORD_DEFAULT);
            $senha_original = $senha_digitada;
        }
    } elseif ($acao === 'verificar') {
        $hash_para_verificar = $_POST['hash'] ?? '';
        if (empty($hash_para_verificar) || empty($senha_digitada)) {
            $erro = "⚠️ Digite a senha e o hash para verificar.";
        } else {
            if (password_verify($senha_digitada, $hash_para_verificar)) {
                $hash_gerado = "✅ Senha correta! O hash foi validado com sucesso.";
            } else {
                $hash_gerado = "❌ Senha incorreta! O hash não corresponde à senha fornecida.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerador de Hash de Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .container {
            max-width: 600px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            padding: 40px;
            margin-top: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #667eea;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #333;
            font-weight: 600;
            margin-bottom: 5px;
        }
        
        .header p {
            color: #666;
            font-size: 14px;
        }
        
        .section {
            margin-bottom: 30px;
        }
        
        .section h3 {
            color: #333;
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }
        
        .section h3 .icon {
            font-size: 24px;
            margin-right: 10px;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .btn-group-vertical {
            gap: 10px;
        }
        
        .btn {
            border-radius: 5px;
            font-weight: 600;
            padding: 10px 20px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #5568d3 0%, #6a4090 100%);
            color: white;
        }
        
        .btn-secondary {
            background: #6c757d;
            border: none;
            color: white;
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            color: white;
        }
        
        .alert {
            border-radius: 5px;
            margin-bottom: 20px;
        }
        
        .hash-output {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 5px;
            padding: 15px;
            margin-top: 15px;
            font-family: 'Courier New', monospace;
            font-size: 12px;
            word-break: break-all;
            color: #333;
        }
        
        .hash-output.success {
            background: #d4edda;
            border-color: #28a745;
            color: #155724;
        }
        
        .hash-output.error {
            background: #f8d7da;
            border-color: #dc3545;
            color: #721c24;
        }
        
        .copy-btn {
            margin-top: 10px;
        }
        
        .info-box {
            background: #e7f3ff;
            border-left: 4px solid #2196F3;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
            font-size: 13px;
            color: #1565c0;
        }
        
        .info-box strong {
            display: block;
            margin-bottom: 8px;
        }
        
        .example-table {
            width: 100%;
            font-size: 13px;
            margin-top: 10px;
        }
        
        .example-table td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        
        .example-table th {
            background: #667eea;
            color: white;
            padding: 8px;
            text-align: left;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Gerador de Hash de Senha</h1>
            <p>Ferramenta para gerar e verificar senhas criptografadas com bcrypt (PASSWORD_DEFAULT)</p>
        </div>

        <!-- Seção 1: Gerar Hash -->
        <div class="section">
            <h3>
                <span class="icon">🔑</span>
                Gerar Novo Hash
            </h3>
            
            <form method="POST" class="mb-3">
                <div class="mb-3">
                    <label for="senha" class="form-label">Senha Desejada</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="senha"
                        name="senha" 
                        placeholder="Mínimo 6 caracteres"
                        required
                    >
                    <small class="text-muted">Dica: Use uma mistura de maiúsculas, minúsculas, números e símbolos</small>
                </div>
                
                <button type="submit" name="acao" value="gerar" class="btn btn-primary w-100">
                    🔐 Gerar Hash
                </button>
            </form>

            <?php if ($hash_gerado && $_POST['acao'] === 'gerar'): ?>
                <div class="alert alert-success">
                    ✅ Hash gerado com sucesso!
                </div>
                
                <div class="hash-output success">
                    <strong>Senha:</strong> <?php echo htmlspecialchars($senha_original); ?><br><br>
                    <strong>Hash (copie para o banco de dados):</strong><br>
                    <?php echo htmlspecialchars($hash_gerado); ?>
                </div>
                
                <button type="button" class="btn btn-secondary w-100 copy-btn" onclick="copiarHash('<?php echo htmlspecialchars(addslashes($hash_gerado)); ?>')">
                    📋 Copiar Hash
                </button>
                
                <div class="alert alert-info mt-3">
                    <strong>Como usar:</strong>
                    <pre style="margin: 10px 0; background: #f8f9fa; padding: 10px; border-radius: 4px;">INSERT INTO Cliente (nome, email, telefone, senha_hash, tipo)
VALUES ('Nome', 'email@exemplo.com', '0000-0000', '<?php echo htmlspecialchars($hash_gerado); ?>', 'cliente');</pre>
                </div>
            <?php endif; ?>
        </div>

        <hr>

        <!-- Seção 2: Verificar Hash -->
        <div class="section">
            <h3>
                <span class="icon">✔️</span>
                Verificar Hash Existente
            </h3>
            
            <form method="POST" class="mb-3">
                <div class="mb-3">
                    <label for="hash" class="form-label">Hash a Verificar</label>
                    <textarea 
                        class="form-control" 
                        id="hash"
                        name="hash" 
                        rows="3"
                        placeholder="Cole um hash bcrypt aqui"
                        required
                    ></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="senha_verificar" class="form-label">Senha para Testar</label>
                    <input 
                        type="password" 
                        class="form-control" 
                        id="senha_verificar"
                        name="senha" 
                        placeholder="Digite a senha a ser testada"
                        required
                    >
                </div>
                
                <button type="submit" name="acao" value="verificar" class="btn btn-primary w-100">
                    ✓ Verificar Correspondência
                </button>
            </form>

            <?php if ($hash_gerado && $_POST['acao'] === 'verificar'): ?>
                <div class="hash-output <?php echo strpos($hash_gerado, '✅') !== false ? 'success' : 'error'; ?>">
                    <?php echo $hash_gerado; ?>
                </div>
            <?php endif; ?>
        </div>

        <hr>

        <!-- Seção 3: Senhas de Teste -->
        <div class="section">
            <h3>
                <span class="icon">📋</span>
                Senhas de Teste Pré-geradas
            </h3>
            
            <div class="info-box">
                <strong>Use estas credenciais para testar o sistema:</strong>
            </div>
            
            <table class="example-table">
                <thead>
                    <tr>
                        <th>Email</th>
                        <th>Senha</th>
                        <th>Tipo</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code>admin@loja.com</code></td>
                        <td><code>admin123</code></td>
                        <td><span class="badge bg-danger">Admin</span></td>
                    </tr>
                    <tr>
                        <td><code>cliente@loja.com</code></td>
                        <td><code>cliente123</code></td>
                        <td><span class="badge bg-success">Cliente</span></td>
                    </tr>
                    <tr>
                        <td><code>joao@exemplo.com</code></td>
                        <td><code>joao123</code></td>
                        <td><span class="badge bg-success">Cliente</span></td>
                    </tr>
                    <tr>
                        <td><code>maria@exemplo.com</code></td>
                        <td><code>maria123</code></td>
                        <td><span class="badge bg-success">Cliente</span></td>
                    </tr>
                </tbody>
            </table>

            <div class="alert alert-warning mt-3">
                <strong>⚠️ Importante:</strong> Este arquivo é apenas para fins de desenvolvimento e teste. 
                Em um ambiente de produção, remova este arquivo do servidor.
            </div>
        </div>

        <!-- Seção 4: Informações de Segurança -->
        <div class="section">
            <h3>
                <span class="icon">🛡️</span>
                Informações de Segurança
            </h3>
            
            <div class="info-box">
                <strong>Password Hashing em PHP:</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li><code>password_hash($senha, PASSWORD_DEFAULT)</code> - Gera o hash (usa bcrypt)</li>
                    <li><code>password_verify($digitada, $hash)</code> - Verifica se a senha corresponde ao hash</li>
                    <li><code>PASSWORD_DEFAULT</code> - Algoritmo mais seguro disponível (atualmente bcrypt)</li>
                </ul>
            </div>

            <div class="alert alert-info">
                <strong>Por que usar hashing?</strong>
                <ul style="margin: 10px 0 0 0; padding-left: 20px;">
                    <li>Senhas em texto plano são inseguras</li>
                    <li>Hashing é unidirecional - não pode ser revertido</li>
                    <li>Mesmo que o banco seja comprometido, as senhas estão protegidas</li>
                    <li>Bcrypt é computacionalmente lento, dificultando ataques de força bruta</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        function copiarHash(hash) {
            // Criar textarea temporário
            const textarea = document.createElement('textarea');
            textarea.value = hash;
            document.body.appendChild(textarea);
            
            // Selecionar e copiar
            textarea.select();
            document.execCommand('copy');
            
            // Remover textarea
            document.body.removeChild(textarea);
            
            // Feedback ao usuário
            const btn = event.target;
            const textoOriginal = btn.textContent;
            btn.textContent = '✅ Copiado!';
            btn.disabled = true;
            
            setTimeout(() => {
                btn.textContent = textoOriginal;
                btn.disabled = false;
            }, 2000);
        }
    </script>
</body>
</html>
