<?php
session_start();
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");

$msg = null;

// --- REMOVER AGENDAMENTO ---
if (isset($_POST['remover_confirmado'])) {
    $id = intval($_POST['id_agendamento']);
    if ($conn->query("DELETE FROM Agendamento WHERE id_agendamento = $id")) {
        $msg = ['success', '🗑️ Agendamento removido com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao remover agendamento.'];
    }
}

// --- EDITAR AGENDAMENTO ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_agendamento']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $status = $_POST['status'];

    if ($conn->query("UPDATE Agendamento SET data='$data', hora='$hora', status='$status' WHERE id_agendamento=$id")) {
        $msg = ['success', '✏️ Agendamento atualizado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao atualizar agendamento.'];
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
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>📅 Lista de Agendamentos - Barber La Mafia</title>
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
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="mb-4">📅 Lista de Agendamentos</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark">
        <tr class="text-center">
          <th>ID</th>
          <th>Cliente</th>
          <th>Barbeiro</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Observação</th>
          <th>Status</th>
          <th>Ações</th>
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
          ?>
          <tr class="text-center">
            <td><?= $row['id_agendamento'] ?></td>
            <td><?= htmlspecialchars($row['cliente']) ?></td>
            <td><?= htmlspecialchars($row['barbeiro']) ?></td>
            <td><?= htmlspecialchars($row['servico']) ?>  
              <small class="text-muted d-block">
                R$ <?= number_format($row['preco'], 2, ',', '.') ?> - <?= $row['duracao'] ?> min
              </small>
            </td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td class="observacao" title="<?= htmlspecialchars($row['observacao'] ?: 'Sem observação') ?>">
              <?= htmlspecialchars($row['observacao'] ?: '-') ?>
            </td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td>
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?= $row['id_agendamento'] ?>">
                <i class="bi bi-pencil"></i>
              </button>

              <button class="btn btn-sm btn-danger remover-btn" 
                      data-id="<?= $row['id_agendamento'] ?>"
                      data-cliente="<?= htmlspecialchars($row['cliente']) ?>"
                      data-servico="<?= htmlspecialchars($row['servico']) ?>"
                      data-barbeiro="<?= htmlspecialchars($row['barbeiro']) ?>"
                      data-data="<?= date('d/m/Y', strtotime($row['data'])) ?>"
                      data-hora="<?= date('H:i', strtotime($row['hora'])) ?>">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>

          <!-- Modal Editar -->
          <div class="modal fade" id="editarModal<?= $row['id_agendamento'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Agendamento</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_agendamento" value="<?= $row['id_agendamento'] ?>">
                    <div class="mb-3">
                      <label class="form-label">Data</label>
                      <input type="date" name="data" class="form-control" value="<?= $row['data'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Hora</label>
                      <input type="time" name="hora" class="form-control" value="<?= $row['hora'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select">
                        <option value="pendente" <?= $row['status']=="pendente"?"selected":"" ?>>Pendente</option>
                        <option value="confirmado" <?= $row['status']=="confirmado"?"selected":"" ?>>Confirmado</option>
                        <option value="concluido" <?= $row['status']=="concluido"?"selected":"" ?>>Concluído</option>
                        <option value="cancelado" <?= $row['status']=="cancelado"?"selected":"" ?>>Cancelado</option>
                      </select>
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
    <div class="alert alert-warning mt-3">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<!-- Modal genérico de confirmação -->
<div class="modal fade" id="removerConfirmacaoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header bg-danger text-white">
          <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar Remoção</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body" id="removerDetalhes"></div>
        <div class="modal-footer">
          <input type="hidden" name="id_agendamento" id="removerIdAgendamento">
          <button type="submit" name="remover_confirmado" class="btn btn-danger">Sim, remover</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Tooltips
document.querySelectorAll('[title]').forEach(el => new bootstrap.Tooltip(el));

// Modal dinâmico
const removerModal = new bootstrap.Modal(document.getElementById('removerConfirmacaoModal'));
document.querySelectorAll('.remover-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const cliente = btn.dataset.cliente;
    const servico = btn.dataset.servico;
    const barbeiro = btn.dataset.barbeiro;
    const data = btn.dataset.data;
    const hora = btn.dataset.hora;

    document.getElementById('removerIdAgendamento').value = id;
    document.getElementById('removerDetalhes').innerHTML = `
      <p>Deseja realmente <strong>remover</strong> o agendamento?</p>
      <p class="text-center">
        👤 <strong>${cliente}</strong><br>
        💇 ${servico}<br>
        ✂️ ${barbeiro}<br>
        📅 ${data} às ${hora}
      </p>
      <p class="text-muted text-center mb-0"><small>Esta ação não poderá ser desfeita.</small></p>
    `;
    removerModal.show();
  });
});
</script>
</body>
</html>
