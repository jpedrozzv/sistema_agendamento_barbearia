<?php
include("conexao.php");
session_start();

// Só admin pode acessar
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}

// Remover serviço
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);
    if ($conn->query("DELETE FROM Servico WHERE id_servico = $id")) {
        header("Location: listar_servicos.php?msg=remove_ok");
        exit;
    } else {
        header("Location: listar_servicos.php?msg=remove_erro");
        exit;
    }
}

// Editar serviço
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_servico']);
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $duracao = $_POST['duracao'];

    $sql = "UPDATE Servico SET descricao='$descricao', preco='$preco', duracao='$duracao' WHERE id_servico=$id";
    if ($conn->query($sql)) {
        header("Location: listar_servicos.php?msg=edit_ok");
        exit;
    } else {
        header("Location: listar_servicos.php?msg=edit_erro");
        exit;
    }
}

// Adicionar novo serviço
if (isset($_POST['adicionar'])) {
    $descricao = $_POST['descricao'];
    $preco = $_POST['preco'];
    $duracao = $_POST['duracao'];

    $sql = "INSERT INTO Servico (descricao, preco, duracao) VALUES ('$descricao', '$preco', '$duracao')";
    if ($conn->query($sql)) {
        header("Location: listar_servicos.php?msg=add_ok");
        exit;
    } else {
        header("Location: listar_servicos.php?msg=add_erro");
        exit;
    }
}

// Buscar serviços
$result = $conn->query("SELECT * FROM Servico ORDER BY id_servico");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Serviços</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barbearia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>✂️ Gerenciar Serviços</h2>

  <!-- Mensagens -->
  <?php
  if (isset($_GET['msg'])) {
      switch ($_GET['msg']) {
          case 'add_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3">✅ Serviço adicionado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
          case 'edit_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3">✅ Serviço atualizado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
          case 'remove_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3">🗑️ Serviço removido com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
          case 'add_erro':
          case 'edit_erro':
          case 'remove_erro':
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3">❌ Ocorreu um erro. Tente novamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
      }
  }
  ?>

  <!-- Formulário de novo serviço -->
  <div class="card shadow-sm p-3 mb-4">
    <h5>Adicionar Novo Serviço</h5>
    <form method="POST" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="descricao" class="form-control" placeholder="Descrição" required>
      </div>
      <div class="col-md-3">
        <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço (R$)" required>
      </div>
      <div class="col-md-3">
        <input type="number" name="duracao" class="form-control" placeholder="Duração (min)" required>
      </div>
      <div class="col-md-2">
        <button type="submit" name="adicionar" class="btn btn-success w-100">Adicionar</button>
      </div>
    </form>
  </div>

  <!-- Tabela -->
  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Descrição</th>
          <th>Preço</th>
          <th>Duração</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id_servico'] ?></td>
            <td><?= $row['descricao'] ?></td>
            <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
            <td><?= $row['duracao'] ?> min</td>
            <td class="text-center">
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?= $row['id_servico'] ?>"><i class="bi bi-pencil"></i></button>
              <a href="?remover=<?= $row['id_servico'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover este serviço?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>

          <!-- Modal Editar -->
          <div class="modal fade" id="editarModal<?= $row['id_servico'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar Serviço</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_servico" value="<?= $row['id_servico'] ?>">
                    <div class="mb-3">
                      <label class="form-label">Descrição</label>
                      <input type="text" name="descricao" class="form-control" value="<?= $row['descricao'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Preço</label>
                      <input type="number" step="0.01" name="preco" class="form-control" value="<?= $row['preco'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Duração (min)</label>
                      <input type="number" name="duracao" class="form-control" value="<?= $row['duracao'] ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="editar" value="1" class="btn btn-success">Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">Nenhum serviço cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
