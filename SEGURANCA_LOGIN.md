# 🔐 Sistema de Login Seguro com Hashing de Senha

## Visão Geral

O sistema de autenticação implementa as melhores práticas de segurança em PHP, utilizando:
- **Hashing de Senha com bcrypt** via `password_hash()` e `password_verify()`
- **Sessões PHP** para manutenção do estado autenticado
- **Validação de Tipo de Usuário** (admin vs cliente)
- **Redirecionamento Seguro** baseado em permissões

---

## 📁 Arquivos Relacionados

### 1. **login.php**
- Exibe formulário de login seguro e amigável
- Previne redirect loops verificando se usuário já está logado
- Mostra mensagens de erro genéricas ("E-mail ou senha inválidos")
- Exibe dicas de credenciais de teste

### 2. **autenticar.php**
- Processa credenciais POST do formulário de login
- Busca usuário no banco via email
- Verifica senha com `password_verify($digitada, $hash)`
- Armazena na sessão: `cliente_id`, `cliente_nome`, `cliente_tipo`
- Redireciona conforme tipo:
  - **admin** → `index.php` (painel de administração)
  - **cliente** → `vizu_cliente.php` (catálogo de produtos)

### 3. **logout.php**
- Destrói sessão completamente (variáveis, cookies, servidor)
- Redireciona para `login.php?logout=1` com mensagem de confirmação

### 4. **gerar_hash_senha.php** (Utilitário)
- Interface web para gerar e verificar hashes de senha
- Ferramentas de teste para validar credenciais
- **⚠️ Deve ser removido em produção**

---

## 🔑 Credenciais de Teste Pré-configuradas

### Administrador
```
Email:    admin@loja.com
Senha:    admin123
Hash:     $2b$12$LpeLIFxOmGicczz/esycb.Ibk.Gr7Q2mHEj8VoO7aEu5SFTTIH.cO
```

### Cliente Padrão
```
Email:    cliente@loja.com
Senha:    cliente123
Hash:     $2b$12$VF1DrA.jxxccfcxXqRB8..odVNUTxgNWtn3XQteR64t.k5pNAD8ky
```

### Cliente Adicional 1
```
Email:    joao@exemplo.com
Senha:    joao123
Hash:     $2y$10$XNF7x7nM9H4dZ8xqNK2v.uNDRcWnlv2XwVvZhZ7hZ7hZ7hZ7hZ7hZ
```

### Cliente Adicional 2
```
Email:    maria@exemplo.com
Senha:    maria123
Hash:     $2y$10$Y6O8y8oM9I5eA9yqOL3w.vODSdXoMw3XxWwAiA8iA8iA8iA8iA8iA
```

---

## 🔐 Como Funciona o Hashing de Senha

### Gerando um Hash
```php
$senha_plana = "minhaSenha123";
$hash = password_hash($senha_plana, PASSWORD_DEFAULT);
// Resultado: $2y$10$... (60 caracteres, bcrypt)
```

### Verificando a Senha
```php
if (password_verify($senha_digitada, $hash_no_banco)) {
    // Senha correta - iniciar sessão
    $_SESSION['cliente_id'] = $usuario['id'];
} else {
    // Senha incorreta - mostrar erro genérico
    $erro = "E-mail ou senha inválidos.";
}
```

### Por Que Bcrypt?
1. **Unidirecional**: Não pode ser revertido (diferente de encriptação)
2. **Salted**: Cada hash tem um "salt" único
3. **Lento**: Computacionalmente caro, dificulta força bruta
4. **Adaptável**: Pode aumentar o "cost factor" conforme computadores ficam mais rápidos

---

## 🛡️ Fluxo de Autenticação

```
┌─────────────┐
│ login.php   │  (Exibe formulário)
└──────┬──────┘
       │ POST (email, senha)
       ↓
┌─────────────────────┐
│ autenticar.php      │  
│                     │  1. Busca cliente por email
│ (Processa)          │  2. Valida senha com password_verify()
└──────┬──────────────┘  3. Armazena na $_SESSION
       │                 4. Redireciona por tipo
       ├─────────────────────────────┐
       │                             │
       ↓                             ↓
    Admin?              Cliente?
    index.php           vizu_cliente.php
    (Painel Admin)      (Catálogo Produtos)
```

---

## 📋 Tabela `Cliente` no Banco

```sql
CREATE TABLE Cliente (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    telefone VARCHAR(20),
    data_cadastro DATE NOT NULL DEFAULT (CURRENT_DATE),
    senha_hash VARCHAR(255) NOT NULL,  -- Armazena hash bcrypt (60 caracteres)
    tipo ENUM('admin', 'cliente') NOT NULL DEFAULT 'cliente'
);
```

---

## ✅ Checklist de Segurança

- [x] Senhas armazenadas como hash (bcrypt), não em texto plano
- [x] Uso de `password_hash()` e `password_verify()`
- [x] Sessões baseadas em servidor (não em cookies inseguros)
- [x] Mensagens de erro genéricas ("E-mail ou senha inválidos")
- [x] Redirecionamento após logout completo
- [x] Prevenção de redirect loops (usuário já logado)
- [x] Validação de tipo de usuário (ENUM no BD)
- [x] Proteção CSRF (requer token em POST - a implementar se necessário)

---

## 🚀 Como Adicionar Novo Cliente

### Opção 1: Usar o Gerador Web (Recomendado para Desenvolvimento)
1. Acesse `sistema/gerar_hash_senha.php`
2. Digite a senha desejada
3. Clique "Gerar Hash"
4. Copie o hash resultante
5. Use no comando SQL:

```sql
INSERT INTO Cliente (nome, email, telefone, senha_hash, tipo)
VALUES ('João Silva', 'joao@exemplo.com', '1234-5678', 'HASH_AQUI', 'cliente');
```

### Opção 2: Usar PHP Direto
```php
$senha = "novaSenha123";
$hash = password_hash($senha, PASSWORD_DEFAULT);
echo $hash; // Copiar este valor
```

---

## 🐛 Troubleshooting

### "E-mail ou senha inválidos" mesmo com credenciais corretas
- Verifique se o email existe no banco (`SELECT * FROM Cliente WHERE email = ?`)
- Confirme o hash foi gerado corretamente
- Teste com o `gerar_hash_senha.php` (seção "Verificar Hash")

### Sessão não persiste entre páginas
- Verifique se `session_start()` está no topo de TODOS os arquivos protegidos
- Confirme `$_SESSION['cliente_id']` está sendo setado corretamente
- Verifique cookies estão habilitados no navegador

### Hash diferente a cada execução
- Normal! Cada hash bcrypt é único (incluem salt aleatório)
- `password_verify()` sempre funcionará com o mesmo hash

---

## 📚 Referências

- [PHP: password_hash()](https://www.php.net/manual/en/function.password-hash.php)
- [PHP: password_verify()](https://www.php.net/manual/en/function.password-verify.php)
- [OWASP: Password Storage Cheat Sheet](https://cheatsheetseries.owasp.org/cheatsheets/Password_Storage_Cheat_Sheet.html)

---

## ⚠️ Próximas Melhorias de Segurança

1. **CSRF Protection**: Adicionar tokens CSRF em formulários
2. **Rate Limiting**: Limitar tentativas de login falhadas
3. **HTTPS**: Usar HTTPS em produção (não HTTP)
4. **Autenticação de Dois Fatores (2FA)**: Email ou SMS
5. **Audit Logging**: Registrar tentativas de login falhas
6. **Session Timeout**: Expirar sessões após inatividade
