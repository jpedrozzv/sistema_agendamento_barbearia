<?php include "header_adm.php"; ?>

<?php
// --- ADICIONAR SERVIÇO ---
if (isset($_POST['adicionar'])) {
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $duracao = intval($_POST['duracao'] ?? 0);

    if ($descricao === '' || $preco <= 0 || $duracao <= 0) {
        mostrarAlerta('danger', '❌ Informe descrição, preço e duração válidos.');
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO Servico (descricao, preco, duracao) VALUES (?, ?, ?)');
            $stmt->bind_param('sdi', $descricao, $preco, $duracao);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '✅ Serviço adicionado com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao adicionar serviço: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao adicionar serviço.');
        }
    }
}

// --- EDITAR SERVIÇO ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_servico'] ?? 0);
    $descricao = trim($_POST['descricao'] ?? '');
    $preco = floatval($_POST['preco'] ?? 0);
    $duracao = intval($_POST['duracao'] ?? 0);

    if ($id <= 0 || $descricao === '' || $preco <= 0 || $duracao <= 0) {
        mostrarAlerta('danger', '❌ Dados inválidos para atualização.');
    } else {
        try {
            $stmt = $conn->prepare('UPDATE Servico SET descricao = ?, preco = ?, duracao = ? WHERE id_servico = ?');
            $stmt->bind_param('sdii', $descricao, $preco, $duracao, $id);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '✏️ Alterações salvas com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao atualizar serviço: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao salvar alterações.');
        }
    }
}

// --- REMOVER SERVIÇO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_servico') {
    $id = intval($_POST['__id'] ?? 0);

    if ($id <= 0) {
        mostrarAlerta('danger', '❌ Serviço inválido informado.');
    } else {
        try {
            $stmt = $conn->prepare('DELETE FROM Servico WHERE id_servico = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '🗑️ Serviço removido com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao remover serviço: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao remover serviço.');
        }
    }
}

// --- BUSCAR SERVIÇOS ---
$result = $conn->query("SELECT * FROM Servico ORDER BY id_servico ASC");
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">✂️ Gerenciar Serviços</h2>

  <!-- 🧾 FORMULÁRIO DE NOVO SERVIÇO -->
  <div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
      <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Adicionar Novo Serviço</h5>
      <form method="POST" class="row g-3">
        <div class="col-12 col-md-4">
          <input type="text" name="descricao" class="form-control" placeholder="Descrição" required>
        </div>
        <div class="col-12 col-md-3">
          <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço (R$)" required>
        </div>
        <div class="col-12 col-md-3">
          <input type="number" name="duracao" class="form-control" placeholder="Duração (min)" required>
        </div>
        <div class="col-12 col-md-2 d-grid">
          <button type="submit" name="adicionar" class="btn btn-success">
            <i class="bi bi-check-circle"></i> Adicionar
          </button>
        </div>
      </form>
    </div>
  </div>

  <!-- 📋 LISTA DE SERVIÇOS -->
  <?php if ($result->num_rows > 0): ?>
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="table-responsive table-scroll">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr class="text-center text-nowrap">
                <th scope="col" class="text-start table-note-col">Serviço</th>
                <th scope="col">Preço</th>
                <th scope="col">Duração</th>
                <th scope="col" class="text-center" style="width: 140px;">Ações</th>
              </tr>
            </thead>
            <tbody class="text-center">
              <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                  <td class="text-start fw-semibold table-note-col">
                    <span class="d-inline-block text-break clamp-2"
                          data-bs-toggle="tooltip"
                          data-bs-placement="top"
                          title="<?= htmlspecialchars($row['descricao']) ?>">
                      <?= htmlspecialchars($row['descricao']) ?>
                    </span>
                  </td>
                  <td class="text-nowrap">R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
                  <td class="text-nowrap"><?= $row['duracao'] ?> min</td>
                  <td class="text-center">
                    <div class="table-action-group">
                      <!-- Botão Editar -->
                      <button class="btn btn-sm btn-warning"
                              data-bs-toggle="modal"
                              data-bs-target="#editarModal<?= $row['id_servico'] ?>"
                              title="Editar serviço">
                        <i class="bi bi-pencil"></i>
                      </button>

                      <!-- Remover Serviço -->
                      <form id="formRemover<?= $row['id_servico'] ?>" method="POST" class="d-inline">
                        <input type="hidden" name="__action" value="remover_servico">
                        <input type="hidden" name="__id" value="<?= $row['id_servico'] ?>">
                      </form>
                      <button
                        class="btn btn-sm btn-danger"
                        data-confirm="remover_servico"
                        data-id="<?= $row['id_servico'] ?>"
                        data-form="formRemover<?= $row['id_servico'] ?>"
                        data-text="Deseja realmente remover o serviço <strong><?= htmlspecialchars($row['descricao']) ?></strong>?<br><small>Todos os agendamentos associados também serão afetados.</small>"
                        title="Remover serviço">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>

                <!-- Modal Editar -->
                <div class="modal fade" id="editarModal<?= $row['id_servico'] ?>" tabindex="-1">
                  <div class="modal-dialog">
                    <div class="modal-content">
                      <form method="POST">
                        <div class="modal-header bg-dark text-white">
                          <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Serviço</h5>
                          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>
                        <div class="modal-body">
                          <input type="hidden" name="id_servico" value="<?= $row['id_servico'] ?>">
                          <div class="mb-3">
                            <label class="form-label">Descrição</label>
                            <input type="text" name="descricao" class="form-control"
                                    value="<?= htmlspecialchars($row['descricao']) ?>" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Preço (R$)</label>
                            <input type="number" step="0.01" name="preco" class="form-control"
                                    value="<?= $row['preco'] ?>" required>
                          </div>
                          <div class="mb-3">
                            <label class="form-label">Duração (min)</label>
                            <input type="number" name="duracao" class="form-control"
                                    value="<?= $row['duracao'] ?>" required>
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
    <div class="alert alert-warning text-center mt-3">Nenhum serviço cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include "footer.php"; ?>
