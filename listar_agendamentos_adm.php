<?php
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");
include("header_adm.php");

$msg = null;

// --- REMOVER AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_agendamento') {
    $id = intval($_POST['__id']);
    if ($conn->query("DELETE FROM Agendamento WHERE id_agendamento = $id")) {
        $msg = ['success', '🗑️ Agendamento removido com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao remover o agendamento.'];
    }
}

// --- EDITAR AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'editar_agendamento') {
    $id = intval($_POST['__id']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $status = $_POST['status'];

    if ($conn->query("UPDATE Agendamento SET data='$data', hora='$hora', status='$status' WHERE id_agendamento=$id")) {
        $msg = ['success', '✏️ Agendamento atualizado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao atualizar o agendamento.'];
    }
}

// --- BUSCAR AGENDAMENTOS ---
$sql = "SELECT 
            a.id_agendamento,
            c.nome AS cliente,
            b.nome AS barbeiro,
            s.descricao AS servico,
            s.preco,
            s.duracao,
            a.data,
            a.hora,
            a.status,
            a.observacao
        FROM Agendamento a
        JOIN Cliente c ON a.id_cliente = c.id_cliente
        JOIN Barbeiro b ON a.id_barbeiro = b.id_barbeiro
        JOIN Servico s ON a.id_servico = s.id_servico
        ORDER BY a.data DESC, a.hora DESC";
$result = $conn->query($sql);
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Lista de Agendamentos</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Barbeiro</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Status</th>
          <th>Observação</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $badge = match($row['status']) {
              'pendente' => 'warning',
              'confirmado' => 'primary',
              'concluido' => 'success',
              'cancelado' => 'danger',
              default => 'secondary'
            };
          ?>
          <tr>
            <td><?= $row['id_agendamento'] ?></td>
            <td><?= htmlspecialchars($row['cliente']) ?></td>
            <td><?= htmlspecialchars($row['barbeiro']) ?></td>
            <td><?= htmlspecialchars($row['servico']) ?><br>
              <small>(R$ <?= number_format($row['preco'], 2, ',', '.') ?> - <?= $row['duracao'] ?> min)</small>
            </td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td class="observacao" title="<?= htmlspecialchars($row['observacao'] ?: 'Sem observação') ?>">
              <?= htmlspecialchars($row['observacao'] ?: '-') ?>
            </td>
            <td>
              <!-- Form editar -->
              <form method="POST" class="d-inline">
                <input type="hidden" name="__action" value="editar_agendamento">
                <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
                <button
                  type="button"
                  class="btn btn-sm btn-warning editar-btn"
                  data-id="<?= $row['id_agendamento'] ?>"
                  data-data="<?= $row['data'] ?>"
                  data-hora="<?= $row['hora'] ?>"
                  data-status="<?= $row['status'] ?>">
                  <i class="bi bi-pencil"></i>
                </button>
              </form>

              <!-- Form remover -->
              <form id="formRemover<?= $row['id_agendamento'] ?>" method="POST" class="d-inline">
                <input type="hidden" name="__action" value="remover_agendamento">
                <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
              </form>
              <button
                class="btn btn-sm btn-danger"
                data-confirm="remover_agendamento"
                data-id="<?= $row['id_agendamento'] ?>"
                data-text="Deseja realmente <strong>remover</strong> o agendamento de <strong><?= htmlspecialchars($row['cliente']) ?></strong> com <strong><?= htmlspecialchars($row['barbeiro']) ?></strong>?">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include("footer.php"); ?>

<script>
// Tooltip
document.querySelectorAll('[title]').forEach(el => new bootstrap.Tooltip(el));

// Modal de confirmação para remoção
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const text = btn.dataset.text;

    const modalHTML = `
      <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar ação</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
              <p>${text}</p>
              <p class="text-muted"><small>Esta ação não poderá ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button class="btn btn-danger" id="confirmYes">Sim, remover</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();

    document.getElementById('confirmYes').addEventListener('click', () => {
      document.getElementById(`formRemover${id}`).submit();
      modal.hide();
    });

    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', e => e.target.remove());
  });
});

// Modal de edição dinâmica
document.querySelectorAll('.editar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const data = btn.dataset.data;
    const hora = btn.dataset.hora;
    const status = btn.dataset.status;

    const modalHTML = `
      <div class="modal fade" id="editarModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <form method="POST">
              <div class="modal-header bg-dark text-white">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Agendamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <input type="hidden" name="__action" value="editar_agendamento">
                <input type="hidden" name="__id" value="${id}">
                <div class="mb-3">
                  <label class="form-label">Data</label>
                  <input type="date" name="data" class="form-control" value="${data}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Hora</label>
                  <input type="time" name="hora" class="form-control" value="${hora}" required>
                </div>
                <div class="mb-3">
                  <label class="form-label">Status</label>
                  <select name="status" class="form-select">
                    <option value="pendente" ${status === 'pendente' ? 'selected' : ''}>Pendente</option>
                    <option value="confirmado" ${status === 'confirmado' ? 'selected' : ''}>Confirmado</option>
                    <option value="concluido" ${status === 'concluido' ? 'selected' : ''}>Concluído</option>
                    <option value="cancelado" ${status === 'cancelado' ? 'selected' : ''}>Cancelado</option>
                  </select>
                </div>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              </div>
            </form>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('editarModal'));
    modal.show();
    document.getElementById('editarModal').addEventListener('hidden.bs.modal', e => e.target.remove());
  });
});
</script>
