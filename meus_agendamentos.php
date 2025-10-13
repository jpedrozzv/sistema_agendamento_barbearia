<?php
include("conexao.php");
include("verifica_cliente.php");



$id_cliente = $_SESSION['cliente_id'];

// Cancelar agendamento (se o cliente pedir)
if (isset($_GET['cancelar'])) {
    $id = intval($_GET['cancelar']);
    $conn->query("UPDATE Agendamento SET status='cancelado' WHERE id_agendamento=$id AND id_cliente=$id_cliente");
    $msg = "❌ Agendamento cancelado!";
}

// Buscar agendamentos do cliente logado (com observação)
$sql = "SELECT 
            a.id_agendamento, 
            s.descricao AS servico, 
            s.duracao, 
            a.data, 
            a.hora, 
            a.status,
            a.observacao
        FROM Agendamento a
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_cliente = $id_cliente
        ORDER BY a.data DESC, a.hora DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>📅 Meus Agendamentos - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    td {
      vertical-align: middle;
    }
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

<!-- Navbar -->
<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barber La Mafia</a>
    <span class="navbar-text text-white">Bem-vindo, <?= $_SESSION['cliente_nome'] ?></span>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>📅 Meus Agendamentos</h2>

  <?php if (isset($msg)): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm mt-3 align-middle">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
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
          <tr>
            <td><?= $row['id_agendamento'] ?></td>
            <td><?= htmlspecialchars($row['servico']) ?> (<?= $row['duracao'] ?> min)</td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td class="observacao" title="<?= htmlspecialchars($row['observacao'] ?: 'Sem observação') ?>">
              <?= htmlspecialchars($row['observacao'] ?: '-') ?>
            </td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td class="text-center">
              <?php if (!in_array($row['status'], ["cancelado", "concluido"])): ?>
                <a href="?cancelar=<?= $row['id_agendamento'] ?>" class="btn btn-sm btn-danger"
                    onclick="return confirm('Deseja realmente cancelar este agendamento?')">
                    <i class="bi bi-x-circle"></i> Cancelar
                </a>
              <?php else: ?>
                <span class="text-muted">-</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning mt-3">Você ainda não possui nenhum agendamento.</div>
  <?php endif; ?>

  <a href="cliente_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
  // Ativa os tooltips do Bootstrap (para mostrar a observação completa ao passar o mouse)
  const tooltipTriggerList = document.querySelectorAll('[title]');
  tooltipTriggerList.forEach(el => new bootstrap.Tooltip(el));
</script>
</body>
</html>
