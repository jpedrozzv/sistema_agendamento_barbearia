<?php
include("conexao.php");
session_start();

// Só clientes acessam
if (!isset($_SESSION['cliente_id']) || $_SESSION['tipo'] != "cliente") {
    header("Location: login.php");
    exit;
}

$id_cliente = $_SESSION['cliente_id'];

// Cancelar agendamento
if (isset($_GET['cancelar'])) {
    $id = intval($_GET['cancelar']);
    $conn->query("UPDATE Agendamento SET status='cancelado' 
                  WHERE id_agendamento = $id AND id_cliente = $id_cliente");

    $msg = "<h4 class='text-danger text-center'>❌ Agendamento cancelado!</h4>
            <p class='text-center'>Você será redirecionado em instantes...</p>";

    header("refresh:5;url=listar_agendamentos_cliente.php"); // espera 5 segundos
}


// Buscar agendamentos do cliente logado
$sql = "SELECT 
            a.id_agendamento,
            b.nome AS barbeiro,
            s.descricao AS servico,
            s.preco,
            s.duracao,
            a.data,
            a.hora,
            a.status
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
  <title>Meus Agendamentos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barbearia</a>
    <span class="navbar-text text-white">Bem-vindo, <?= $_SESSION['cliente_nome'] ?></span>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>

  </div>
</nav>

<div class="container mt-4">
  <h2>📅 Meus Agendamentos</h2>

  <?php if (isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>

  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Barbeiro</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): ?>
          <?php
            $badge = "secondary";
            if ($row['status'] == "pendente") $badge = "warning";
            if ($row['status'] == "confirmado") $badge = "primary";
            if ($row['status'] == "concluido") $badge = "success";
            if ($row['status'] == "cancelado") $badge = "danger";
          ?>
          <tr>
            <td><?= $row['id_agendamento'] ?></td>
            <td><?= $row['barbeiro'] ?></td>
            <td>
              <?= $row['servico'] ?>  
              (R$ <?= number_format($row['preco'], 2, ',', '.') ?> - <?= $row['duracao'] ?> min)
            </td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td>
              <?php if ($row['status'] != "cancelado" && $row['status'] != "concluido"): ?>
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
    <div class="alert alert-warning mt-3">Você não possui nenhum agendamento.</div>
  <?php endif; ?>

  <a href="cliente_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
