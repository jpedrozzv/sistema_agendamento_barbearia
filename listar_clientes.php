<?php
include("conexao.php");

session_start();
// Somente admin pode acessar
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}

// Buscar clientes
$result = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente ASC");


// --- Remover cliente ---
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);

    // Apagar agendamentos vinculados (por causa da foreign key)
    $conn->query("DELETE FROM Agendamento WHERE id_cliente = $id");

    // Depois apagar o cliente
    if ($conn->query("DELETE FROM Cliente WHERE id_cliente = $id") === TRUE) {
        $msg = "✅ Cliente removido com sucesso!";
    } else {
        $msg = "❌ Erro ao remover cliente: " . $conn->error;
    }
}

// --- Editar cliente ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_cliente']);
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];

    $sql = "UPDATE Cliente 
            SET nome='$nome', telefone='$telefone', email='$email' 
            WHERE id_cliente=$id";

    if ($conn->query($sql) === TRUE) {
        $msg = "✅ Cliente atualizado com sucesso!";
    } else {
        $msg = "❌ Erro ao atualizar cliente: " . $conn->error;
    }
}

// Buscar clientes
$result = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Lista de Clientes</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2>Lista de Clientes</h2>

  <?php if (isset($msg)): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>Email</th>
          <th style="width:120px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id_cliente'] ?></td>
            <td><?= $row['nome'] ?></td>
            <td><?= $row['telefone'] ?></td>
            <td><?= $row['email'] ?></td>
            <td class="text-center">
              <!-- Botão Editar (ícone lápis) -->
              <button class="btn btn-sm btn-warning" 
                      data-bs-toggle="modal" 
                      data-bs-target="#editarModal<?= $row['id_cliente'] ?>">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Modal de edição -->
              <div class="modal fade" id="editarModal<?= $row['id_cliente'] ?>" tabindex="-1">
                <div class="modal-dialog">
                  <div class="modal-content">
                    <form method="POST">
                      <div class="modal-header">
                        <h5 class="modal-title">Editar Cliente</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <input type="hidden" name="id_cliente" value="<?= $row['id_cliente'] ?>">
                        <div class="mb-3">
                          <label class="form-label">Nome</label>
                          <input type="text" name="nome" class="form-control" value="<?= $row['nome'] ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Telefone</label>
                          <input type="text" name="telefone" class="form-control" value="<?= $row['telefone'] ?>" required>
                        </div>
                        <div class="mb-3">
                          <label class="form-label">Email</label>
                          <input type="email" name="email" class="form-control" value="<?= $row['email'] ?>">
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

              <!-- Botão Remover (ícone lixeira) -->
              <a href="?remover=<?= $row['id_cliente'] ?>" 
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Tem certeza que deseja remover este cliente?')">
                 <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">Nenhum cliente cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
