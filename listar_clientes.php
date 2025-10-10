<?php
include("conexao.php");
session_start();

// Somente admin pode acessar
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}

// --- Remover cliente ---
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);

    // Apagar agendamentos vinculados
    $conn->query("DELETE FROM Agendamento WHERE id_cliente = $id");

    // Depois apagar o cliente
    if ($conn->query("DELETE FROM Cliente WHERE id_cliente = $id") === TRUE) {
        header("Location: listar_clientes.php?msg=remove_ok");
        exit;
    } else {
        header("Location: listar_clientes.php?msg=remove_erro");
        exit;
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
        header("Location: listar_clientes.php?msg=edit_ok");
        exit;
    } else {
        header("Location: listar_clientes.php?msg=edit_erro");
        exit;
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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>

<body class="bg-light">

<div class="container mt-4">
  <h2>Lista de Clientes</h2>

  <!-- Mensagens Bootstrap -->
  <?php
  if (isset($_GET['msg'])) {
      switch ($_GET['msg']) {
          case 'senha_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                      ✅ Senha redefinida com sucesso! Nova senha: <strong>1234</strong>
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
          case 'senha_erro':
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                      ❌ Erro ao redefinir senha. Tente novamente.
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
          case 'edit_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                      ✅ Cliente atualizado com sucesso!
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
          case 'edit_erro':
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                      ❌ Erro ao atualizar cliente. Tente novamente.
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
          case 'remove_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3" role="alert">
                      🗑️ Cliente removido com sucesso!
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
          case 'remove_erro':
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
                      ❌ Erro ao remover cliente. Verifique se há agendamentos vinculados.
                      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>';
              break;
      }
  }
  ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>Email</th>
          <th style="width:150px;">Ações</th>
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

              <!-- Botão Editar -->
              <button class="btn btn-sm btn-warning" 
                      data-bs-toggle="modal" 
                      data-bs-target="#editarModal<?= $row['id_cliente'] ?>"
                      title="Editar Cliente">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Botão Redefinir Senha -->
              <a href="redefinir_senha.php?id=<?= $row['id_cliente'] ?>" 
                 class="btn btn-sm btn-secondary"
                 title="Redefinir senha para 1234"
                 onclick="return confirm('Deseja redefinir a senha deste cliente? A nova senha será 1234.')">
                 <i class="bi bi-key"></i>
              </a>

              <!-- Botão Remover -->
              <a href="?remover=<?= $row['id_cliente'] ?>" 
                 class="btn btn-sm btn-danger"
                 title="Remover Cliente"
                 onclick="return confirm('Tem certeza que deseja remover este cliente?')">
                 <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Modal Editar Cliente -->
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

        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning mt-3">Nenhum cliente cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
