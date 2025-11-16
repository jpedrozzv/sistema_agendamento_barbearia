<?php include "header_adm.php"; ?>

<?php
// --- REMOVER CLIENTE ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_cliente') {
    $id = intval($_POST['__id'] ?? 0);

    if ($id <= 0) {
        mostrarAlerta('danger', '❌ Cliente inválido informado.');
    } else {
        try {
            $stmtAg = $conn->prepare('DELETE FROM Agendamento WHERE id_cliente = ?');
            $stmtAg->bind_param('i', $id);
            $stmtAg->execute();
            $stmtAg->close();

            $stmt = $conn->prepare('DELETE FROM Cliente WHERE id_cliente = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            mostrarAlerta('success', '🗑️ Cliente removido com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao remover cliente: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao remover cliente.');
        }
    }
}

// --- EDITAR CLIENTE ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_cliente'] ?? 0);
    $nome = trim($_POST['nome'] ?? '');
    $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $observacoes = trim($_POST['observacoes'] ?? '');

    if ($id <= 0 || $nome === '' || $telefone === '' || ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL))) {
        mostrarAlerta('danger', '❌ Dados inválidos para atualização.');
    } else {
        try {
            $stmt = $conn->prepare('UPDATE Cliente SET nome = ?, telefone = ?, email = ?, observacoes = ? WHERE id_cliente = ?');
            $stmt->bind_param('ssssi', $nome, $telefone, $email, $observacoes, $id);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '✏️ Alterações salvas com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao atualizar cliente: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao atualizar cliente.');
        }
    }
}

if (isset($_POST['__action']) && $_POST['__action'] === 'redefinir_senha') {
    $id = intval($_POST['__id'] ?? 0);

    if ($id <= 0) {
        mostrarAlerta('danger', '❌ Cliente inválido informado.');
    } else {
        try {
            $novaSenha = password_hash('1234', PASSWORD_DEFAULT);
            $stmt = $conn->prepare('UPDATE Cliente SET senha = ? WHERE id_cliente = ?');
            $stmt->bind_param('si', $novaSenha, $id);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '🔑 Senha redefinida para 1234 com sucesso.');
        } catch (Throwable $exception) {
            error_log('Erro ao redefinir senha do cliente: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Não foi possível redefinir a senha.');
        }
    }
}

$result = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente ASC");
?>

<style>
  .clientes-observacao {
    max-width: 260px;
  }

  @media (max-width: 575.98px) {
    .clientes-observacao {
      max-width: 180px;
    }
  }

  .table-action-group {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 0.5rem;
    flex-wrap: wrap;
  }

  @media (max-width: 575.98px) {
    .table-action-group .btn {
      flex: 1 1 48%;
    }
  }
</style>

<div class="container mt-4">
  <h2 class="text-center mb-4">👥 Gerenciar Clientes</h2>

  <?php if ($result->num_rows > 0): ?>
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-dark text-center">
              <tr class="text-nowrap">
                <th scope="col" class="text-start">Nome</th>
                <th scope="col">Telefone</th>
                <th scope="col">Email</th>
                <th scope="col" class="text-start clientes-observacao">Observações</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <?php while ($row = $result->fetch_assoc()): ?>
                <?php $observacaoCliente = trim($row['observacoes'] ?? ''); ?>
                <tr>
                  <td class="text-start fw-semibold" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($row['nome']) ?>">
                    <?= htmlspecialchars($row['nome']) ?>
                  </td>
                  <td class="text-nowrap"><?= htmlspecialchars($row['telefone']) ?></td>
                  <td class="text-break"><?= htmlspecialchars($row['email']) ?></td>
                  <td class="text-start clientes-observacao">
                    <?php if ($observacaoCliente !== ''): ?>
                      <span class="d-inline-block text-break" style="max-height: 3.75rem; overflow: hidden;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($observacaoCliente) ?>">
                        <?= htmlspecialchars($observacaoCliente) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted fst-italic">Sem observações</span>
                    <?php endif; ?>
                  </td>
                  <td>
                    <div class="table-action-group">
                      <!-- Botão Editar -->
                      <button class="btn btn-sm btn-warning"
                              data-bs-toggle="modal"
                              data-bs-target="#editarModal<?= $row['id_cliente'] ?>"
                              title="Editar cliente">
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
                        data-form="formSenha<?= $row['id_cliente'] ?>"
                        data-text="Deseja redefinir a senha de <strong><?= htmlspecialchars($row['nome']) ?></strong> para <strong>1234</strong>?"
                        title="Redefinir senha">
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
                        data-form="formRemover<?= $row['id_cliente'] ?>"
                        data-text="Deseja realmente remover o cliente <strong><?= htmlspecialchars($row['nome']) ?></strong>?<br><small>Todos os agendamentos vinculados também serão excluídos.</small>"
                        title="Remover cliente">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
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
                            <input type="text" name="telefone" class="form-control" value="<?= htmlspecialchars($row['telefone']) ?>" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($row['email']) ?>">
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Observações</label>
                            <textarea name="observacoes" class="form-control" rows="3" placeholder="Informações adicionais sobre o cliente"><?= htmlspecialchars($row['observacoes'] ?? '') ?></textarea>
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
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-3">Nenhum cliente cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include "footer.php"; ?>
