<?php
session_start();

// BLOQUEIO DE ACESSO: só entra se estiver logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

// carregar conexão
require_once __DIR__ . '/conexao.php';


// --- EXCLUIR ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $pdo->prepare("DELETE FROM Loja WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: lojas.php");
    exit;
}


// --- ADICIONAR ---
if (isset($_POST['acao']) && $_POST['acao'] === "add") {
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $rua = $_POST['rua'] ?? '';
    $numero = $_POST['numero'] !== '' ? intval($_POST['numero']) : null;
    $bairro = $_POST['bairro'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $cidade = $_POST['cidade'] ?? '';

    $stmt = $pdo->prepare("INSERT INTO Loja (nome, telefone, rua, numero, bairro, cep, complemento, cidade)
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$nome, $telefone, $rua, $numero, $bairro, $cep, $complemento, $cidade]);

    header("Location: lojas.php");
    exit;
}


// --- EDITAR ---
if (isset($_POST['acao']) && $_POST['acao'] === "edit") {
    $id = intval($_POST['id']);
    $nome = $_POST['nome'] ?? '';
    $telefone = $_POST['telefone'] ?? '';
    $rua = $_POST['rua'] ?? '';
    $numero = $_POST['numero'] !== '' ? intval($_POST['numero']) : null;
    $bairro = $_POST['bairro'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $complemento = $_POST['complemento'] ?? '';
    $cidade = $_POST['cidade'] ?? '';

    $stmt = $pdo->prepare("UPDATE Loja SET
                    nome = ?,
                    telefone = ?,
                    rua = ?,
                    numero = ?,
                    bairro = ?,
                    cep = ?,
                    complemento = ?,
                    cidade = ?
                  WHERE id = ?");
    $stmt->execute([$nome, $telefone, $rua, $numero, $bairro, $cep, $complemento, $cidade, $id]);

    header("Location: lojas.php");
    exit;
}

// Buscar todas as lojas
$stmt = $pdo->query("SELECT * FROM Loja ORDER BY id DESC");
$lojas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Lojas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container py-5">
        <div class="row mb-4">
            <div class="col-md-6">
                <h1>Gerenciar Lojas</h1>
            </div>
            <div class="col-md-6 text-end">
                <a href="index.php" class="btn btn-secondary">Voltar ao Painel</a>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAdicionar">+ Nova Loja</button>
            </div>
        </div>

        <!-- Tabela de Lojas -->
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Telefone</th>
                        <th>Endereço</th>
                        <th>Cidade</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($lojas)): ?>
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">Nenhuma loja cadastrada</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($lojas as $loja): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($loja['id']); ?></td>
                            <td><?php echo htmlspecialchars($loja['nome']); ?></td>
                            <td><?php echo htmlspecialchars($loja['telefone'] ?? '-'); ?></td>
                            <td><?php echo htmlspecialchars($loja['rua'] ?? '') . ', ' . htmlspecialchars($loja['numero'] ?? ''); ?></td>
                            <td><?php echo htmlspecialchars($loja['cidade'] ?? ''); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#modalEditar" 
                                    onclick="carregarEdicao(<?php echo htmlspecialchars(json_encode($loja)); ?>)">Editar</button>
                                <a href="?delete=<?php echo $loja['id']; ?>" class="btn btn-sm btn-danger" 
                                    onclick="return confirm('Tem certeza que deseja excluir esta loja?')">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Adicionar -->
    <div class="modal fade" id="modalAdicionar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nova Loja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="add">
                        <div class="mb-3">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rua</label>
                            <input type="text" name="rua" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número</label>
                                <input type="number" name="numero" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Salvar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="modalEditar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Editar Loja</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="acao" value="edit">
                        <input type="hidden" name="id" id="editId">
                        <div class="mb-3">
                            <label class="form-label">Nome *</label>
                            <input type="text" name="nome" id="editNome" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Telefone</label>
                            <input type="text" name="telefone" id="editTelefone" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rua</label>
                            <input type="text" name="rua" id="editRua" class="form-control">
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Número</label>
                                <input type="number" name="numero" id="editNumero" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Bairro</label>
                                <input type="text" name="bairro" id="editBairro" class="form-control">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">CEP</label>
                                <input type="text" name="cep" id="editCep" class="form-control">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Cidade</label>
                                <input type="text" name="cidade" id="editCidade" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Complemento</label>
                            <input type="text" name="complemento" id="editComplemento" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Atualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function carregarEdicao(loja) {
            document.getElementById('editId').value = loja.id;
            document.getElementById('editNome').value = loja.nome;
            document.getElementById('editTelefone').value = loja.telefone || '';
            document.getElementById('editRua').value = loja.rua || '';
            document.getElementById('editNumero').value = loja.numero || '';
            document.getElementById('editBairro').value = loja.bairro || '';
            document.getElementById('editCep').value = loja.cep || '';
            document.getElementById('editCidade').value = loja.cidade || '';
            document.getElementById('editComplemento').value = loja.complemento || '';
        }
    </script>
</body>
</html>
