# 🛍️ Projeto 2: Loja Online

**Disciplina:** Banco de Dados  
**Curso:** Técnico em Desenvolvimento de Sistemas  
**Aluno(a):** [Seu Nome]  
**Turma:** [Sua Turma]  
**Professor:** [Nome do Professor]
**Data de Entrega:** [dd/mm/aaaa]  

---

## 🎯 Objetivo
Desenvolver uma aplicação simples de loja online que integra:
- Um banco de dados relacional (**MySQL**) para lojas, produtos e características.
- Um banco NoSQL (**MongoDB Atlas**) para armazenar **características flexíveis dos produtos** (como cor, tamanho, voltagem, material, etc.) e avaliações.
- Uma interface web em **PHP** que exibe produtos e seus estoques dinâmicos.

Este projeto demonstra como usar diferentes tipos de bancos de dados conforme a natureza dos dados.

---

## 🛠️ Tecnologias Utilizadas
- 💾 **MySQL** – Para dados estruturados e transacionais
- 📦 **MongoDB Atlas** – Para dados flexíveis (avaliações, comentários)
- 💬 **PHP** – Integração e exibição na web
- 🔗 **GitHub** – Versionamento de código
- 🖥️ **XAMPP** – Ambiente local para execução

---

## 🗄️ Banco de Dados Relacional (MySQL)

### Banco: `loja_online`

### Estrutura das Tabelas
```sql
-- Tabela: produto
id (INT, PK), nome (VARCHAR), descricao (VARCHAR), preco (DECIMAL), tipo (ENUM: Novo, Usado, Liquidacao, Promocao, Outros), categoria (SET: Eletronico, Telefonia, Informatica, Eletrodomesticos, Acessorios, Outros), data_de_lancamento (DATE), desconto (DECIMAL)

-- Tabela: caracteristica
id (INT, PK), nome (VARCHAR), descricao (VARCHAR)

-- Tabela: produto_caracteristica
id (INT, PK), id_prodtudo (INT), id_caracteristica (INT)

-- Tabela: loja
id (INT, PK), nome (VARCHAR), telefone (VARCHAR), rua (VARCHAR), numero (INT), bairro (VARCHAR), cep (VARCHAR), complemento (VARCHAR), cidade(VARCHAR)

-- Tabela: estoque
id (INT, PK), id_produto (INT), id_loja (INT), quantidade_disponivel (INT)

---

## 💻 Aplicação PHP
### Funcionalidades
- Recebe o id do aluno pela URL (ex: painel_aluno.php?id=1)
- Busca os dados principais no MySQL
- Busca o histórico no MongoDB
- Exibe tudo integrado em uma página web

### Como Executar
- Inicie o Apache e MySQL no XAMPP.
- Coloque os arquivos de loja_online na pasta htdocs.
- Acesse no navegador: http://localhost/loja_online
