<?php
session_start();

// Se já está logado, redirecionar
if (isset($_SESSION['cliente_id'])) {
    if ($_SESSION['cliente_tipo'] === 'admin') {
        header("Location: index.php");
    } else {
        header("Location: vizu_cliente.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Loja de Eletrônicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 400px;
            padding: 40px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-header h1 {
            font-size: 28px;
            color: #333;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .login-header p {
            color: #666;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 5px;
            border: 1px solid #ddd;
            padding: 10px 15px;
            margin-bottom: 15px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            background-color: #fff5f5;
        }

        .form-control.is-invalid:focus {
            border-color: #dc3545;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25);
        }
        
        .form-label {
            margin-bottom: 8px;
            color: #333;
            font-weight: 500;
            font-size: 14px;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            color: white;
            padding: 10px 15px;
            font-size: 16px;
            font-weight: 600;
            border-radius: 5px;
            width: 100%;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            margin-top: 10px;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(102, 126, 234, 0.4);
            color: white;
            text-decoration: none;
        }
        
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #f5c6cb;
            font-size: 14px;
            text-align: center;
        }
        
        .info-message {
            background-color: #d1ecf1;
            color: #0c5460;
            padding: 12px;
            border-radius: 5px;
            margin-bottom: 20px;
            border: 1px solid #bee5eb;
            font-size: 13px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🔐 Acesso Seguro</h1>
            <p>Loja de Eletrônicos Online</p>
        </div>

        <?php if (isset($_GET['erro'])): ?>
            <div class="error-message">
                ⚠️ E-mail ou senha inválidos. Tente novamente.
            </div>
        <?php endif; ?>

        <?php if (isset($_GET['logout'])): ?>
            <div class="info-message">
                ✓ Você foi desconectado com sucesso.
            </div>
        <?php endif; ?>

        <form action="autenticar.php" method="POST" novalidate id="form-login">
            <div class="mb-3">
                <label for="email" class="form-label">E-mail</label>
                <input 
                    type="email" 
                    class="form-control" 
                    id="email"
                    name="email" 
                    placeholder="seu.email@exemplo.com"
                    required
                    autocomplete="email"
                    onblur="validarEmail(this)"
                >
                <small class="text-danger d-none" id="erro-email">
                    Digite um e-mail válido
                </small>
            </div>

            <div class="mb-3">
                <label for="senha" class="form-label">Senha</label>
                <input 
                    type="password" 
                    class="form-control" 
                    id="senha"
                    name="senha" 
                    placeholder="Digite sua senha"
                    required
                    autocomplete="current-password"
                    minlength="1"
                >
                <small class="text-danger d-none" id="erro-senha">
                    Campo obrigatório
                </small>
            </div>

            <button type="submit" class="btn btn-login" id="btn-submit">
                🔓 Entrar
            </button>
        </form>

        <div class="info-message" style="margin-top: 20px; margin-bottom: 0;">
            <small>Para fins de demonstração, use:<br>
            <strong>Email:</strong> admin@loja.com | <strong>Senha:</strong> admin123<br>
            ou<br>
            <strong>Email:</strong> cliente@loja.com | <strong>Senha:</strong> cliente123
            </small>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        /**
         * Validação Frontend do Formulário de Login
         */

        // Validar email em tempo real
        function validarEmail(input) {
            const email = input.value.trim();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            const erroElement = document.getElementById('erro-email');
            
            if (email && !regex.test(email)) {
                input.classList.add('is-invalid');
                erroElement.classList.remove('d-none');
            } else {
                input.classList.remove('is-invalid');
                erroElement.classList.add('d-none');
            }
        }

        // Validar senha em tempo real
        function validarSenha(input) {
            const senha = input.value.trim();
            const erroElement = document.getElementById('erro-senha');
            
            if (input.form.classList.contains('was-validated') && !senha) {
                input.classList.add('is-invalid');
                erroElement.classList.remove('d-none');
            } else {
                input.classList.remove('is-invalid');
                erroElement.classList.add('d-none');
            }
        }

        // Validar formulário ao submeter
        document.getElementById('form-login').addEventListener('submit', function(e) {
            const email = document.getElementById('email').value.trim();
            const senha = document.getElementById('senha').value.trim();
            const regex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            
            let valido = true;

            // Validar email
            if (!email) {
                document.getElementById('email').classList.add('is-invalid');
                document.getElementById('erro-email').textContent = 'Campo obrigatório';
                document.getElementById('erro-email').classList.remove('d-none');
                valido = false;
            } else if (!regex.test(email)) {
                document.getElementById('email').classList.add('is-invalid');
                document.getElementById('erro-email').textContent = 'Digite um e-mail válido';
                document.getElementById('erro-email').classList.remove('d-none');
                valido = false;
            }

            // Validar senha
            if (!senha) {
                document.getElementById('senha').classList.add('is-invalid');
                document.getElementById('erro-senha').classList.remove('d-none');
                valido = false;
            }

            if (!valido) {
                e.preventDefault();
                this.classList.add('was-validated');
            }
        });

        // Remover validação visual ao digitar
        document.getElementById('email').addEventListener('input', function() {
            validarEmail(this);
        });

        document.getElementById('senha').addEventListener('input', function() {
            validarSenha(this);
        });
    </script>
</body>
</html>
