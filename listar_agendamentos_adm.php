<?php
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");
include("header_adm.php");

$msg = null;

// --- REMOVER AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remover_agendamento') {
    $id = intval($_POST['__id'] ?? 0);

    if ($id <= 0) {
        $msg = ['danger', '❌ Agendamento inválido informado.'];
    } else {
        try {
            $stmt = $conn->prepare('DELETE FROM Agendamento WHERE id_agendamento = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();
            $msg = ['success', '🗑️ Agendamento removido com sucesso!'];
        } catch (Throwable $exception) {
            error_log('Erro ao remover agendamento: ' . $exception->getMessage());
            $msg = ['danger', '❌ Erro ao remover o agendamento.'];
        }
    }
}

// --- EDITAR AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'editar_agendamento') {
    $id = intval($_POST['__id'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $status = $_POST['status'] ?? '';

    $dataObj = DateTimeImmutable::createFromFormat('Y-m-d', $data);
    $statusPermitidos = ['pendente', 'confirmado', 'concluido', 'cancelado'];

    if ($id <= 0 || !$dataObj || $dataObj->format('Y-m-d') !== $data || !preg_match('/^\d{2}:\d{2}$/', $hora) || !in_array($status, $statusPermitidos, true)) {
        $msg = ['danger', '❌ Dados inválidos informados para atualização.'];
    } else {
        try {
            $stmt = $conn->prepare('UPDATE Agendamento SET data = ?, hora = ?, status = ? WHERE id_agendamento = ?');
            $stmt->bind_param('sssi', $data, $hora, $status, $id);
            $stmt->execute();
            $stmt->close();
            $msg = ['success', '✏️ Agendamento atualizado com sucesso!'];
        } catch (Throwable $exception) {
            error_log('Erro ao atualizar agendamento: ' . $exception->getMessage());
            $msg = ['danger', '❌ Erro ao atualizar o agendamento.'];
        }
    }
}

// --- BUSCAR AGENDAMENTOS ---
$stmtLista = $conn->prepare(
    "SELECT
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
        ORDER BY a.data DESC, a.hora DESC"
);
$stmtLista->execute();
$result = $stmtLista->get_result();
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Lista de Agendamentos</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <?php if ($result->num_rows > 0): ?>
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="table-responsive table-scroll">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead>
              <tr class="text-nowrap text-center">
                <th scope="col">Cliente</th>
                <th scope="col">Barbeiro</th>
                <th scope="col" class="text-start">Serviço</th>
                <th scope="col">Data</th>
                <th scope="col">Hora</th>
                <th scope="col">Status</th>
                <th scope="col" class="text-start">Observações</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                  $badge = match($row['status']) {
                    'pendente' => 'warning',
                    'confirmado' => 'primary',
                    'concluido' => 'success',
                    'cancelado' => 'danger',
                    default => 'secondary'
                  };
                  $observacaoTexto = trim($row['observacao'] ?? '');
                ?>
                <tr>
                  <td class="text-nowrap" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($row['cliente']) ?>">
                    <?= htmlspecialchars($row['cliente']) ?>
                  </td>
                  <td class="text-nowrap" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($row['barbeiro']) ?>">
                    <?= htmlspecialchars($row['barbeiro']) ?>
                  </td>
                  <td class="text-start">
                    <div class="fw-semibold text-truncate" style="max-width: 220px;" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($row['servico']) ?>">
                      <?= htmlspecialchars($row['servico']) ?>
                    </div>
                    <small class="text-muted">R$ <?= number_format($row['preco'], 2, ',', '.') ?> · <?= $row['duracao'] ?> min</small>
                  </td>
                  <td class="text-nowrap"><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                  <td class="text-nowrap"><?= date('H:i', strtotime($row['hora'])) ?></td>
                  <td>
                    <span class="badge bg-<?= $badge ?>">
                      <?= ucfirst($row['status']) ?>
                    </span>
                  </td>
                  <td class="text-start" style="max-width: 260px;">
                    <?php if ($observacaoTexto !== ''): ?>
                      <span
                        class="d-inline-block clamp-2 text-break"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="<?= htmlspecialchars($observacaoTexto) ?>"
                        aria-label="Observações completas: <?= htmlspecialchars($observacaoTexto) ?>">
                        <?= htmlspecialchars($observacaoTexto) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted fst-italic">Sem observações</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center text-nowrap" style="width: 110px;">
                    <div class="table-action-group">
                      <form method="POST" class="d-inline">
                        <input type="hidden" name="__action" value="editar_agendamento">
                        <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
                        <button
                          type="button"
                          class="btn btn-sm btn-warning editar-btn"
                          data-id="<?= $row['id_agendamento'] ?>"
                          data-data="<?= $row['data'] ?>"
                          data-hora="<?= $row['hora'] ?>"
                          data-status="<?= $row['status'] ?>"
                          title="Editar agendamento"
                          aria-label="Editar agendamento de <?= htmlspecialchars($row['cliente']) ?> com <?= htmlspecialchars($row['barbeiro']) ?>">
                          <i class="bi bi-pencil"></i>
                        </button>
                      </form>
                      <form id="formRemover<?= $row['id_agendamento'] ?>" method="POST" class="d-inline">
                        <input type="hidden" name="__action" value="remover_agendamento">
                        <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
                      </form>
                      <button
                        class="btn btn-sm btn-danger"
                        data-confirm="remover_agendamento"
                        data-id="<?= $row['id_agendamento'] ?>"
                        data-form="formRemover<?= $row['id_agendamento'] ?>"
                        data-text="Deseja realmente <strong>remover</strong> o agendamento de <strong><?= htmlspecialchars($row['cliente']) ?></strong> com <strong><?= htmlspecialchars($row['barbeiro']) ?></strong>?"
                        title="Remover agendamento"
                        aria-label="Remover agendamento de <?= htmlspecialchars($row['cliente']) ?> com <?= htmlspecialchars($row['barbeiro']) ?>">
                        <i class="bi bi-trash"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php $stmtLista->close(); ?>
<?php include("footer.php"); ?>

<div class="modal fade" id="editarModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST" id="editarAgendamentoForm">
        <div class="modal-header bg-dark text-white">
          <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Agendamento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="__action" value="editar_agendamento">
          <input type="hidden" name="__id" id="editarId">
          <div class="mb-3">
            <label class="form-label">Data</label>
            <input type="date" name="data" id="editarData" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Hora</label>
            <input type="time" name="hora" id="editarHora" class="form-control" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Status</label>
            <select name="status" id="editarStatus" class="form-select">
              <option value="pendente">Pendente</option>
              <option value="confirmado">Confirmado</option>
              <option value="concluido">Concluído</option>
              <option value="cancelado">Cancelado</option>
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

<script>
document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));

const editarModal = document.getElementById('editarModal');
const editarForm = document.getElementById('editarAgendamentoForm');

document.querySelectorAll('.editar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    editarForm.querySelector('#editarId').value = btn.dataset.id;
    editarForm.querySelector('#editarData').value = btn.dataset.data;
    editarForm.querySelector('#editarHora').value = btn.dataset.hora;
    editarForm.querySelector('#editarStatus').value = btn.dataset.status;

    const modal = new bootstrap.Modal(editarModal);
    modal.show();
  });
});
</script>
