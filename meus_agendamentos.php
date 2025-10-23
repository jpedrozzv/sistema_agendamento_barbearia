<?php
include("conexao.php");
include("verifica_cliente.php");
include("alerta.php");
include("header_cliente.php");

// --- CANCELAR AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'cancelar_agendamento') {
    $id = intval($_POST['__id']);
    if ($conn->query("UPDATE Agendamento SET status='cancelado' WHERE id_agendamento=$id AND id_cliente=".$_SESSION['cliente_id'])) {
        mostrarAlerta('success', '🗑️ Agendamento cancelado com sucesso!');
    } else {
        mostrarAlerta('danger', '❌ Erro ao cancelar o agendamento.');
    }
}

// --- BUSCAR AGENDAMENTOS DO CLIENTE ---
$id_cliente = $_SESSION['cliente_id'];
$sql = "SELECT 
            a.id_agendamento,
            b.nome AS barbeiro,
            s.descricao AS servico,
            s.preco,
            a.data,
            a.hora,
            a.status,
            a.observacao
        FROM Agendamento a
        JOIN Barbeiro b ON a.id_barbeiro = b.id_barbeiro
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_cliente = $id_cliente
        ORDER BY a.data DESC, a.hora DESC";
$result = $conn->query($sql);
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Meus Agendamentos</h2>

  <?php if (isset($_SESSION['alerta'])) { echo $_SESSION['alerta']; unset($_SESSION['alerta']); } ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>Serviço</th>
          <th>Barbeiro</th>
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
            <td><?= htmlspecialchars($row['servico']) ?><br>
              <small>R$ <?= number_format($row['preco'], 2, ',', '.') ?></small>
            </td>
            <td><?= htmlspecialchars($row['barbeiro']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td class="observacao" title="<?= htmlspecialchars($row['observacao'] ?: 'Sem observação') ?>">
              <?= htmlspecialchars($row['observacao'] ?: '-') ?>
            </td>
            <td>
              <?php if (in_array($row['status'], ['pendente','confirmado'])): ?>
                <form id="formCancelar<?= $row['id_agendamento'] ?>" method="POST" class="d-inline">
                  <input type="hidden" name="__action" value="cancelar_agendamento">
                  <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
                </form>
                <button
                  class="btn btn-sm btn-danger"
                  data-confirm="cancelar_agendamento"
                  data-id="<?= $row['id_agendamento'] ?>"
                  data-text="Deseja realmente <strong>cancelar</strong> o agendamento de
                             <strong><?= htmlspecialchars($row['servico']) ?></strong> com
                             <strong><?= htmlspecialchars($row['barbeiro']) ?></strong> em
                             <strong><?= date('d/m/Y', strtotime($row['data'])) ?></strong> às
                             <strong><?= date('H:i', strtotime($row['hora'])) ?></strong>?">
                  <i class="bi bi-x-circle"></i>
                </button>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary" disabled><i class="bi bi-dash-circle"></i></button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="cliente_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include("footer.php"); ?>

<script>
// Tooltip
document.querySelectorAll('[title]').forEach(el => new bootstrap.Tooltip(el));

// Modal de confirmação
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const text = btn.dataset.text;

    const modalHTML = `
      <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar cancelamento</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
              <p>${text}</p>
              <p class="text-muted"><small>Esta ação não poderá ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
              <button class="btn btn-danger" id="confirmYes">Sim, cancelar</button>
            </div>
          </div>
        </div>
      </div>
    `;
    document.body.insertAdjacentHTML('beforeend', modalHTML);

    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();

    document.getElementById('confirmYes').addEventListener('click', () => {
      document.getElementById(`formCancelar${id}`).submit();
      modal.hide();
    });

    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', e => e.target.remove());
  });
});
</script>
