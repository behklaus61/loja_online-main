# ✅ CHECKLIST - REQUISITOS DO PROJETO FINAL

## Professor: Davi Bernardo
## Disciplina: Tópicos Especiais de Programação
## Projeto: Sistema Loja Online - Amazon(as)

---

## ETAPA 1: MODELAGEM E CRIAÇÃO DE TABELAS ✅

### Tabela Cliente
- [x] **id** (PK, auto incremento)
- [x] **nome** (VARCHAR)
- [x] **email** (VARCHAR, único)
- [x] **telefone** (VARCHAR)
- [x] **data_cadastro** (DATE, padrão: data atual)
- [x] **senha_hash** (VARCHAR(255))
- [x] **tipo** (ENUM: admin, cliente) - Adicionado para melhor controle

**Status:** ✅ COMPLETO - Arquivo: `lojaEletronicos.sql` (linhas 185-196)

### Tabela Venda
- [x] **id** (PK, auto incremento)
- [x] **id_cliente** (FK → Cliente)
- [x] **id_loja** (FK → Loja)
- [x] **data_venda** (DATETIME, padrão: agora)
- [x] **valor_total** (DECIMAL, calculado)

**Status:** ✅ COMPLETO - Arquivo: `lojaEletronicos.sql` (linhas 214-227)

### Tabela ItemVenda
- [x] **id** (PK, auto incremento)
- [x] **id_venda** (FK)
- [x] **id_produto** (FK)
- [x] **quantidade** (INT)
- [x] **preco_unitario** (DECIMAL - gravado no momento da venda)

**Status:** ✅ COMPLETO - Arquivo: `lojaEletronicos.sql` (linhas 228-240)

### Tabela CarrinhoTemporario
- [x] **id** (PK, auto incremento)
- [x] **id_cliente** (FK)
- [x] **id_produto** (FK)
- [x] **id_loja** (FK) - Adicionado para rastrear origem do produto
- [x] **quantidade** (INT)
- [x] **data_adicao** (TIMESTAMP)

**Status:** ✅ COMPLETO - Arquivo: `lojaEletronicos.sql` (linhas 248-271)

---

## ETAPA 2: CARRINHO DE COMPRAS ✅

### 2.1 Estrutura de Dados
- [x] Tabela CarrinhoTemporario criada e funcional
- [x] Carrinho é temporário (antes do login ou finalização)
- [x] Registro único (id_cliente, id_produto, id_loja)

**Status:** ✅ COMPLETO

### 2.2 Operações no Carrinho

#### Adicionar Produto
- [x] Arquivo: `carrinho.php` (linhas 17-56)
- [x] Validação de estoque antes de adicionar
- [x] Verifica se produto já existe (quantidade se soma)

#### Remover Produto
- [x] Arquivo: `carrinho.php` (linhas 62-67)
- [x] DELETE com segurança (id_cliente)

#### Atualizar Quantidade
- [x] Arquivo: `carrinho.php` (linhas 73-85)
- [x] Validação de quantidade (remove se < 1)

### 2.3 Layout da Página do Carrinho

Tabela com colunas exigidas:
- [x] **Produto** - Exibido com nome
- [x] **Categoria** - Exibido com badge
- [x] **Loja** - Exibido com nome e cidade
- [x] **Qtd** (Quantidade) - Com input para editar
- [x] **Preço** - Preço original do produto
- [x] **C/ Desc** (Com Desconto) - Preço - desconto_usados
- [x] **Subtotal** - quantidade × preço_com_desconto

**Arquivo:** `carrinho.php` (linhas 190-230)

Informações Resumidas:
- [x] **Total de Itens** - Exibido abaixo da tabela
- [x] **Valor Total da Compra** - Soma de todos os subtotais
- [x] **Botão Finalizar Compra** - Habilitado apenas se houver estoque

**Arquivo:** `carrinho.php` (linhas 244-256)

### 2.4 Transação SQL ao Finalizar Compra

Execução em `checkout.php`:

#### 1. Validação de Estoque (ANTES de qualquer INSERT)
- [x] Query: `SELECT quantidade_disponivel >= ? FROM Estoque WHERE id_produto = ? AND id_loja = ?`
- [x] Se falhar para qualquer item, não processa venda
- [x] Implementado em `checkout.php` (linhas 43-52)

#### 2. INSERT INTO Venda
- [x] Insere registro com (id_cliente, id_loja, valor_total)
- [x] Implementado em `checkout.php` (linha 59)

#### 3. INSERT INTO ItemVenda
- [x] Um INSERT por item do carrinho
- [x] Inclui (id_venda, id_produto, quantidade, preco_unitario)
- [x] preco_unitario é FIXADO no momento (não varia depois)
- [x] Implementado em `checkout.php` (linhas 64-68)

#### 4. UPDATE Estoque
- [x] Reduz quantidade_disponivel para cada item
- [x] Formula: `quantidade_disponivel = quantidade_disponivel - ?`
- [x] Implementado em `checkout.php` (linhas 70-75)

#### 5. DELETE CarrinhoTemporario
- [x] Limpa carrinho após venda bem-sucedida
- [x] Implementado em `checkout.php` (linhas 77-79)

#### 6. Transação ACID
- [x] `beginTransaction()` no início
- [x] `commit()` ao final
- [x] `rollBack()` em caso de erro
- [x] Implementado em `checkout.php` (linhas 37, 90, 97)

**Status:** ✅ COMPLETO - Arquivo: `checkout.php`

### 2.5 Consulta Principal do Carrinho

Uma única consulta SQL com:
- [x] **JOINs**: CarrinhoTemporario → Produto → Loja → Estoque
- [x] **Cálculo de Preço com Desconto**: `(preco - desconto_usados) as preco_com_desconto`
- [x] **Cálculo de Subtotal**: `(quantidade × preco_com_desconto) as subtotal`
- [x] **Filtro de Estoque**: `AND e.quantidade_disponivel > 0`

**Query em `carrinho.php` (linhas 89-110):**
```sql
SELECT ct.id, ct.id_produto, ct.id_loja, ct.quantidade,
       p.nome as produto_nome, p.categoria,
       p.preco, p.desconto_usados,
       (p.preco - p.desconto_usados) as preco_com_desconto,
       (ct.quantidade * (p.preco - p.desconto_usados)) as subtotal,
       l.nome as loja_nome, l.cidade,
       e.quantidade_disponivel as estoque_disponivel
FROM CarrinhoTemporario ct 
INNER JOIN Produto p ON ct.id_produto = p.id 
INNER JOIN Loja l ON ct.id_loja = l.id
INNER JOIN Estoque e ON ct.id_produto = e.id_produto 
                       AND ct.id_loja = e.id_loja
WHERE ct.id_cliente = ? 
AND e.quantidade_disponivel > 0
ORDER BY ct.data_adicao DESC
```

**Status:** ✅ COMPLETO

---

## ETAPA 3: TELA DE LOGIN SEGURA COM HASHING ✅

### 3.1 Tela de Login (Frontend)
- [x] Campo **email** (tipo email)
- [x] Campo **senha** (tipo password)
- [x] Botão **"Entrar"**
- [x] Mensagem de erro genérica: "E-mail ou senha inválidos."

**Design Melhorado:**
- [x] Bootstrap 5.3.8
- [x] Gradiente de cores profissional
- [x] Responsivo para mobile
- [x] Validação em tempo real (frontend)

**Arquivo:** `login.php`
**Status:** ✅ COMPLETO

### 3.2 Validação Frontend
- [x] Email valida formato ao sair do campo
- [x] Senha valida se está vazia
- [x] Feedback visual com `.is-invalid`
- [x] Mensagens de erro específicas

**Arquivo:** `login.php` (linhas 210-271)
**Status:** ✅ COMPLETO

### 3.3 Cadastro de Senha
Clientes com senhas hasheadas usando `password_hash()`:

1. **Administrador**
   - Email: `admin@loja.com`
   - Senha: `admin123`
   - Hash: `$2b$12$LpeLIFxOmGicczz/esycb.Ibk.Gr7Q2mHEj8VoO7aEu5SFTTIH.cO`

2. **Cliente Teste**
   - Email: `cliente@loja.com`
   - Senha: `cliente123`
   - Hash: `$2b$12$VF1DrA.jxxccfcxXqRB8..odVNUTxgNWtn3XQteR64t.k5pNAD8ky`

3. **João Silva**
   - Email: `joao@exemplo.com`
   - Senha: `joao123`

4. **Maria Santos**
   - Email: `maria@exemplo.com`
   - Senha: `maria123`

**Arquivo:** `lojaEletronicos.sql` (linhas 197-200)
**Status:** ✅ COMPLETO - 4 clientes cadastrados (2 exigidos)

### 3.4 Verificação de Senha com password_verify()

**Arquivo:** `autenticar.php`

```php
if ($cliente && password_verify($senha_digitada, $cliente['senha_hash'])) {
    // Login bem-sucedido → inicia sessão
    $_SESSION['cliente_id'] = $cliente['id'];
    $_SESSION['cliente_nome'] = $cliente['nome'];
    $_SESSION['cliente_email'] = $cliente['email'];
    $_SESSION['cliente_tipo'] = $cliente['tipo'];
    header("Location: " . ($cliente['tipo'] === 'admin' ? 'index.php' : 'vizu_cliente.php'));
    exit;
} else {
    // Erro genérico
    error_log("Tentativa de login falha para email: " . $email);
    header("Location: login.php?erro=1");
    exit;
}
```

**Status:** ✅ COMPLETO

### 3.5 Validação no Backend (PHP)

Em `autenticar.php`:

#### Validar Entrada
- [x] Campos não vazios
- [x] Email válido (filter_var com FILTER_VALIDATE_EMAIL)
- [x] Comprimento mínimo de senha

**Linhas:** 16-26

#### Prepared Statement Seguro
- [x] Query: `SELECT id, nome, email, senha_hash, tipo FROM Cliente WHERE email = ?`
- [x] Placeholder `?` previne SQL injection
- [x] Seleciona apenas campos necessários

**Arquivo:** `autenticar.php` (linhas 28-35)
**Status:** ✅ COMPLETO

### 3.6 Funcionalidades Adicionais (Segurança)

- [x] **Redirecionamento por Tipo**: Admin → `index.php`, Cliente → `vizu_cliente.php`
- [x] **Logout Completo**: Destrói sessão, cookies, servidor
- [x] **Mensagens Genéricas**: Não revela se email existe
- [x] **Logging**: Registra tentativas de login (error_log)
- [x] **Ferramenta Utilitária**: `gerar_hash_senha.php` para testar hashes

**Status:** ✅ COMPLETO

---

## RESUMO FINAL

| Requisito | Status | Arquivo |
|-----------|--------|---------|
| Tabela Cliente | ✅ | lojaEletronicos.sql |
| Tabela Venda | ✅ | lojaEletronicos.sql |
| Tabela ItemVenda | ✅ | lojaEletronicos.sql |
| Tabela CarrinhoTemporario | ✅ | lojaEletronicos.sql |
| Carrinho (Estrutura) | ✅ | carrinho.php |
| Carrinho (Layout) | ✅ | carrinho.php |
| Finalizar Compra | ✅ | checkout.php |
| Validação Estoque | ✅ | checkout.php |
| Transação SQL | ✅ | checkout.php |
| Consulta Principal Carrinho | ✅ | carrinho.php |
| Tela Login (Frontend) | ✅ | login.php |
| Validação Frontend | ✅ | login.php |
| Cadastro de Senhas | ✅ | lojaEletronicos.sql |
| password_verify() | ✅ | autenticar.php |
| Validação Backend | ✅ | autenticar.php |
| Prepared Statements | ✅ | autenticar.php |

---

## 🎓 CONCLUSÃO

**TODOS OS REQUISITOS DO PROFESSOR FORAM IMPLEMENTADOS E ESTÃO FUNCIONAIS.**

O sistema está pronto para ser submetido e avaliado.

---

**Data:** 12 de Dezembro de 2025  
**Status:** ✅ PRONTO PARA ENTREGA
