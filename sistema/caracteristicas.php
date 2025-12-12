<?php
session_start();

// Verificar se está logado
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

require_once 'conexao.php';

/* ------------------------- ADICIONAR ------------------------- */
if (isset($_POST['acao']) && $_POST['acao'] === 'add') {
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    $sql = $pdo->prepare("INSERT INTO Caracteristica (nome, descricao) VALUES (?, ?)");
    $sql->execute([$nome, $descricao]);
}

/* ------------------------- EDITAR ---------------------------- */
if (isset($_POST['acao']) && $_POST['acao'] === 'edit') {
    $id = $_POST['id'];
    $nome = trim($_POST['nome']);
    $descricao = trim($_POST['descricao']);

    $sql = $pdo->prepare("UPDATE Caracteristica SET nome = ?, descricao = ? WHERE id = ?");
    $sql->execute([$nome, $descricao, $id]);
}

/* ------------------------- EXCLUIR --------------------------- */
if (isset($_GET['del'])) {
    $id = $_GET['del'];

    // Apaga primeiro na tabela intermediária
    $pdo->prepare("DELETE FROM Produto_Caracteristica WHERE id_caracteristica = ?")->execute([$id]);

    // Agora apaga a característica
    $pdo->prepare("DELETE FROM Caracteristica WHERE id = ?")->execute([$id]);
}

/* ------------------------- LISTAGEM -------------------------- */
$lista = $pdo->query("SELECT * FROM Caracteristica ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
<meta charset="UTF-8">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
<title>Características</title>
</head>
<body class="container py-4">

<h2 class="mb-4">Características</h2>

<!-- FORM DE ADICIONAR -->
<div class="card p-3 mb-4">
    <h4>Adicionar Característica</h4>

    <form method="POST">
        <input type="hidden" name="acao" value="add">

        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" required>

        <label class="form-label mt-3">Descrição</label>
        <textarea name="descricao" class="form-control"></textarea>

        <button class="btn btn-primary mt-3">Salvar</button>
    </form>
</div>

<!-- LISTAGEM -->
<table class="table table-striped">
    <thead class="table-dark">
        <tr>
            <th width="50">ID</th>
            <th>Nome</th>
            <th>Descrição</th>
            <th width="180">Ações</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($lista as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['nome']) ?></td>
            <td><?= nl2br(htmlspecialchars($c['descricao'])) ?></td>

            <td>

                <!-- EDITAR -->
                <button class="btn btn-warning btn-sm"
                        data-bs-toggle="modal"
                        data-bs-target="#editModal<?= $c['id'] ?>">
                    Editar
                </button>

                <!-- EXCLUIR -->
                <a href="?del=<?= $c['id'] ?>"
                   class="btn btn-danger btn-sm"
                   onclick="return confirm('Excluir esta característica?')">
                   Excluir
                </a>

            </td>
        </tr>

        <!-- MODAL DE EDIÇÃO -->
        <div class="modal fade" id="editModal<?= $c['id'] ?>" tabindex="-1">
          <div class="modal-dialog">
            <div class="modal-content">

              <div class="modal-header">
                <h5 class="modal-title">Editar Característica</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">

                <form method="POST">
                    <input type="hidden" name="acao" value="edit">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">

                    <label class="form-label">Nome</label>
                    <input type="text" name="nome" class="form-control"
                        value="<?= htmlspecialchars($c['nome']) ?>" required>

                    <label class="form-label mt-3">Descrição</label>
                    <textarea name="descricao" class="form-control"><?= htmlspecialchars($c['descricao']) ?></textarea>

                    <button class="btn btn-primary mt-3">Salvar Alterações</button>
                </form>

              </div>

            </div>
          </div>
        </div>

        <?php endforeach; ?>
    </tbody>
</table>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
