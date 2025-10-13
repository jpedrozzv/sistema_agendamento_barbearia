<?php
session_start();
include("conexao.php");
include("verifica_adm.php");



// --- REMOVER CLIENTE ---
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);
    $conn->query("DELETE FROM Agendamento WHERE id_cliente = $id");
    if ($conn->query("DELETE FROM Cliente WHERE id_cliente = $id")) {
        header("Location: listar_clientes.php?msg=ok_remove");
        exit;
    } else {
        header("Location: listar_clientes.php?msg=erro_remove");
        exit;
    }
}

// --- EDITAR CLIENTE ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_cliente']);
    $nome = trim($_POST['nome']);
    $telefone = trim($_POST['telefone']);
    $email = trim($_POST['email']);

    $sql = "UPDATE Cliente 
            SET nome='$nome', telefone='$telefone', email='$email' 
            WHERE id_cliente=$id";
    if ($conn->query($sql)) {
        header("Location: listar_clientes.php?msg=ok_edit");
        exit;
    } else {
        header("Location: listar_clientes.php?msg=erro_edit");
        exit;
    }
}

$result = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Clientes - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="text-center mb-4">👥 Gerenciar Clientes</h2>

  <!-- ALERTAS UNIFICADOS -->
  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-dismissible fade show mt-3 
      <?= str_contains($_GET['msg'], 'erro') ? 'alert-danger' : 'alert-success' ?>" role="alert">
      <?php
        switch ($_GET['msg']) {
          case 'ok_edit':   echo "✏️ Alterações salvas com sucesso!"; break;
          case 'ok_remove': echo "🗑️ Cliente removido com sucesso!"; break;
          case 'erro_edit': echo "❌ Erro ao atualizar cliente."; break;
          case 'erro_remove': echo "❌ Erro ao remover cliente."; break;
          case 'senha_ok':  echo "🔑 Senha redefinida com sucesso! Nova senha: <strong>1234</strong>"; break;
          case 'senha_erro':echo "❌ Erro ao redefinir senha."; break;
        }
      ?>
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Nome</th>
          <th>Telefone</th>
          <th>Email</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id_cliente'] ?></td>
            <td><?= htmlspecialchars($row['nome']) ?></td>
            <td><?= $row['telefone'] ?></td>
            <td><?= $row['email'] ?></td>
            <td>
              <!-- Botão Editar -->
              <button class="btn btn-sm btn-warning"
                      data-bs-toggle="modal"
                      data-bs-target="#editarModal<?= $row['id_cliente'] ?>">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Botão Redefinir Senha -->
              <a href="redefinir_senha.php?id=<?= $row['id_cliente'] ?>"
                 class="btn btn-sm btn-secondary"
                 onclick="return confirm('Redefinir a senha deste cliente para 1234?')">
                <i class="bi bi-key"></i>
              </a>

              <!-- Botão Remover -->
              <a href="?remover=<?= $row['id_cliente'] ?>"
                 class="btn btn-sm btn-danger"
                 onclick="return confirm('Tem certeza que deseja remover este cliente?')">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Modal Editar -->
          <div class="modal fade" id="editarModal<?= $row['id_cliente'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Cliente</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_cliente" value="<?= $row['id_cliente'] ?>">
                    <div class="mb-3">
                      <label class="form-label">Nome</label>
                      <input type="text" name="nome" class="form-control" value="<?= htmlspecialchars($row['nome']) ?>" required>
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
                    <button type="submit" name="editar" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
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
    <div class="alert alert-warning text-center mt-3">Nenhum cliente cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
  <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
