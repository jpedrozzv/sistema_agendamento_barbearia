<?php include "header_adm.php"; ?>
<?php include "alerta.php"; ?>

<?php
// --- REMOVER CLIENTE ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_cliente') {
    $id = intval($_POST['__id']);
    $conn->query("DELETE FROM Agendamento WHERE id_cliente = $id");
    if ($conn->query("DELETE FROM Cliente WHERE id_cliente = $id")) {
        mostrarAlerta('success', '🗑️ Cliente removido com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao remover cliente.');
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
        mostrarAlerta('success', '✏️ Alterações salvas com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao atualizar cliente.');
    }
}

$result = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente ASC");
?>

<h2 class="text-center mb-4">👥 Gerenciar Clientes</h2>

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

            <!-- Redefinir Senha -->
            <form id="formSenha<?= $row['id_cliente'] ?>" method="POST" class="d-inline">
              <input type="hidden" name="__action" value="redefinir_senha">
              <input type="hidden" name="__id" value="<?= $row['id_cliente'] ?>">
            </form>
            <button
              class="btn btn-sm btn-secondary"
              data-confirm="redefinir_senha"
              data-id="<?= $row['id_cliente'] ?>"
              data-text="Deseja redefinir a senha de <strong><?= htmlspecialchars($row['nome']) ?></strong> para <strong>1234</strong>?">
              <i class="bi bi-key"></i>
            </button>

            <!-- Remover Cliente -->
            <form id="formRemover<?= $row['id_cliente'] ?>" method="POST" class="d-inline">
              <input type="hidden" name="__action" value="remover_cliente">
              <input type="hidden" name="__id" value="<?= $row['id_cliente'] ?>">
            </form>
            <button
              class="btn btn-sm btn-danger"
              data-confirm="remover_cliente"
              data-id="<?= $row['id_cliente'] ?>"
              data-text="Deseja realmente remover o cliente <strong><?= htmlspecialchars($row['nome']) ?></strong>?<br><small>Todos os agendamentos vinculados também serão excluídos.</small>">
              <i class="bi bi-trash"></i>
            </button>
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
                  <button type="submit" name="editar" class="btn btn-success">
                    <i class="bi bi-check-circle"></i> Salvar
                  </button>
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

<?php include "footer.php"; ?>
