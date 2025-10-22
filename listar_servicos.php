<?php include "header_adm.php"; ?>
<?php include "alerta.php"; ?>

<?php
// --- ADICIONAR SERVIÇO ---
if (isset($_POST['adicionar'])) {
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao = intval($_POST['duracao']);

    $sql = "INSERT INTO Servico (descricao, preco, duracao)
            VALUES ('$descricao', '$preco', '$duracao')";
    if ($conn->query($sql)) {
        mostrarAlerta('success', '✅ Serviço adicionado com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao adicionar serviço.');
    }
}

// --- EDITAR SERVIÇO ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_servico']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao = intval($_POST['duracao']);

    $sql = "UPDATE Servico
            SET descricao='$descricao', preco='$preco', duracao='$duracao'
            WHERE id_servico=$id";

    if ($conn->query($sql)) {
        mostrarAlerta('success', '✏️ Alterações salvas com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao salvar alterações.');
    }
}

// --- REMOVER SERVIÇO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_servico') {
    $id = intval($_POST['__id']);
    if ($conn->query("DELETE FROM Servico WHERE id_servico = $id")) {
        mostrarAlerta('success', '🗑️ Serviço removido com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao remover serviço.');
    }
}

// --- BUSCAR SERVIÇOS ---
$result = $conn->query("SELECT * FROM Servico ORDER BY id_servico ASC");
?>

<h2 class="text-center mb-4">✂️ Gerenciar Serviços</h2>

<!-- 🧾 FORMULÁRIO DE NOVO SERVIÇO -->
<div class="card shadow-sm p-3 mb-4">
  <h5><i class="bi bi-plus-circle"></i> Adicionar Novo Serviço</h5>
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
      <button type="submit" name="adicionar" class="btn btn-success w-100">
        <i class="bi bi-check-circle"></i> Adicionar
      </button>
    </div>
  </form>
</div>

<!-- 📋 LISTA DE SERVIÇOS -->
<?php if ($result->num_rows > 0): ?>
  <table class="table table-bordered table-hover shadow-sm align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>ID</th>
        <th>Descrição</th>
        <th>Preço</th>
        <th>Duração</th>
        <th style="width: 150px;">Ações</th>
      </tr>
    </thead>
    <tbody class="text-center">
      <?php while ($row = $result->fetch_assoc()): ?>
        <tr>
          <td><?= $row['id_servico'] ?></td>
          <td><?= htmlspecialchars($row['descricao']) ?></td>
          <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
          <td><?= $row['duracao'] ?> min</td>
          <td>
            <!-- Botão Editar -->
            <button class="btn btn-sm btn-warning" data-bs-toggle="modal"
                    data-bs-target="#editarModal<?= $row['id_servico'] ?>" title="Editar Serviço">
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
              data-text="Deseja realmente remover o serviço <strong><?= htmlspecialchars($row['descricao']) ?></strong>?<br><small>Todos os agendamentos associados também serão afetados.</small>">
              <i class="bi bi-trash"></i>
            </button>
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
<?php else: ?>
  <div class="alert alert-warning text-center mt-3">Nenhum serviço cadastrado.</div>
<?php endif; ?>

<a href="admin_dashboard.php" class="btn btn-secondary mt-3">
  <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
</a>

<?php include "footer.php"; ?>
