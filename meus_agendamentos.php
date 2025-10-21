<?php
session_start();
include("conexao.php");
include("verifica_cliente.php");
include("alerta.php");

$id_cliente = $_SESSION['cliente_id'];
$msg = null;

// --- CANCELAR AGENDAMENTO (POST via modal) ---
if (isset($_POST['cancelar_confirmado'])) {
    $id = intval($_POST['id_agendamento']);
    if ($conn->query("UPDATE Agendamento 
                      SET status='cancelado' 
                      WHERE id_agendamento=$id AND id_cliente=$id_cliente")) {
        $msg = ['success', '🗑️ Agendamento cancelado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao cancelar o agendamento.'];
    }
}

// --- BUSCAR AGENDAMENTOS DO CLIENTE ---
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
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>💈 Meus Agendamentos - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    td { vertical-align: middle; }
    td.observacao {
      max-width: 220px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      cursor: help;
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Meus Agendamentos</h2>

  <?php if ($msg) { mostrarAlerta($msg[0], $msg[1]); } ?>

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
              'pendente'   => 'warning',
              'confirmado' => 'primary',
              'concluido'  => 'success',
              'cancelado'  => 'danger',
              default      => 'secondary'
            };
          ?>
          <tr>
            <td><?= htmlspecialchars($row['servico']) ?> (R$ <?= number_format($row['preco'], 2, ',', '.') ?>)</td>
            <td><?= htmlspecialchars($row['barbeiro']) ?></td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td class="observacao" title="<?= htmlspecialchars($row['observacao'] ?: 'Sem observação') ?>">
              <?= htmlspecialchars($row['observacao'] ?: '-') ?>
            </td>
            <td>
              <?php if ($row['status'] === 'pendente' || $row['status'] === 'confirmado'): ?>
                <button 
                  class="btn btn-sm btn-danger cancelar-btn"
                  data-id="<?= $row['id_agendamento'] ?>"
                  data-servico="<?= htmlspecialchars($row['servico']) ?>"
                  data-barbeiro="<?= htmlspecialchars($row['barbeiro']) ?>"
                  data-data="<?= date('d/m/Y', strtotime($row['data'])) ?>"
                  data-hora="<?= date('H:i', strtotime($row['hora'])) ?>">
                  <i class="bi bi-x-circle"></i>
                </button>
              <?php else: ?>
                <button class="btn btn-sm btn-secondary" disabled>
                  <i class="bi bi-dash-circle"></i>
                </button>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-3">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="cliente_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="confirmarCancelamentoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Cancelamento</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="cancelamentoDetalhes"><!-- preenchido via JS --></div>
        <div class="modal-footer">
          <input type="hidden" name="id_agendamento" id="cancelarIdAgendamento">
          <button type="submit" name="cancelar_confirmado" class="btn btn-danger">Sim, cancelar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Voltar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tooltips para observações
document.querySelectorAll('[title]').forEach(el => new bootstrap.Tooltip(el));

// Modal dinâmico para cancelar (sem confirm() do navegador)
const modal = new bootstrap.Modal(document.getElementById('confirmarCancelamentoModal'));
document.querySelectorAll('.cancelar-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const servico = btn.dataset.servico;
    const barbeiro = btn.dataset.barbeiro;
    const data = btn.dataset.data;
    const hora = btn.dataset.hora;

    document.getElementById('cancelarIdAgendamento').value = id;
    document.getElementById('cancelamentoDetalhes').innerHTML = `
      <p>Deseja realmente <strong>cancelar</strong> o agendamento de:</p>
      <p class="text-center">
        💇 <strong>${servico}</strong><br>
        ✂️ com <strong>${barbeiro}</strong><br>
        📅 <strong>${data}</strong> às <strong>${hora}</strong>
      </p>
      <p class="text-muted text-center mb-0"><small>Esta ação não poderá ser desfeita.</small></p>
    `;
    modal.show();
  });
});
</script>
</body>
</html>
