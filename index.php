<?php
include("conexao.php");

session_start();
if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar últimos 5 clientes
$clientes = $conn->query("SELECT * FROM Cliente ORDER BY id_cliente DESC LIMIT 5");

// Buscar últimos 5 agendamentos
$agendamentos = $conn->query("
    SELECT a.id_agendamento, c.nome AS cliente, s.descricao AS servico, a.data, a.hora, a.status
    FROM Agendamento a
    JOIN Cliente c ON a.id_cliente = c.id_cliente
    JOIN Servico s ON a.id_servico = s.id_servico
    ORDER BY a.data DESC, a.hora DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Barbearia - Sistema de Agendamento</title>
  <!-- Bootstrap CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <!-- Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .quick-actions .card {
      border-radius: 1rem;
      min-height: 100%;
    }

    .quick-actions .btn {
      min-height: 48px;
    }

    .table-responsive.shadow-frame {
      border-radius: 0.75rem;
      overflow: hidden;
    }

    @media (max-width: 575.98px) {
      .navbar-brand { white-space: normal; }
      .quick-actions { row-gap: 1.5rem; }
      .table-responsive { margin-left: -1rem; margin-right: -1rem; padding-left: 1rem; padding-right: 1rem; }
      .table-responsive.shadow-frame { border-radius: 0.75rem; }
    }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="index.php">💈 Barbearia La Mafia</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#menuNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="menuNav">
      <ul class="navbar-nav me-auto">
        <li class="nav-item"><a class="nav-link" href="cadastro_cliente.php"><i class="bi bi-person-plus"></i> Cadastrar Cliente</a></li>
        <li class="nav-item"><a class="nav-link" href="agendamento.php"><i class="bi bi-calendar-plus"></i> Novo Agendamento</a></li>
        <li class="nav-item"><a class="nav-link" href="listar_agendamentos.php"><i class="bi bi-list-check"></i> Listar Agendamentos</a></li>
        <li class="nav-item"><a class="nav-link" href="listar_clientes.php"><i class="bi bi-people"></i> Listar Clientes</a></li>
      </ul>
    </div>
  </div>
</nav>

<!-- Conteúdo principal -->
<div class="container py-4 py-lg-5">
  <div class="text-center">
    <h1 class="mb-4">Sistema de Agendamento - Barbearia La Mafia</h1>
    <p class="lead">Gerencie seus clientes e agendamentos de forma simples e rápida.</p>
  </div>

  <div class="row quick-actions mt-5 g-4">
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-person-plus display-4 text-success"></i>
        <h5 class="card-title mt-2">Cadastrar Cliente</h5>
        <p class="card-text">Adicione novos clientes ao sistema.</p>
        <a href="cadastro_cliente.php" class="btn btn-success">Cadastrar</a>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-calendar-plus display-4 text-primary"></i>
        <h5 class="card-title mt-2">Novo Agendamento</h5>
        <p class="card-text">Agende um horário para o cliente.</p>
        <a href="agendamento.php" class="btn btn-primary">Agendar</a>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-list-check display-4 text-dark"></i>
        <h5 class="card-title mt-2">Lista de Agendamentos</h5>
        <p class="card-text">Veja todos os horários agendados.</p>
        <a href="listar_agendamentos.php" class="btn btn-dark">Listar</a>
      </div>
    </div>

    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-people display-4 text-secondary"></i>
        <h5 class="card-title mt-2">Lista de Clientes</h5>
        <p class="card-text">Veja todos os clientes cadastrados.</p>
        <a href="listar_clientes.php" class="btn btn-secondary">Listar</a>
      </div>
    </div>
  </div>

  <!-- Últimos clientes -->
  <div class="mt-5">
    <h3><i class="bi bi-people"></i> Últimos Clientes Cadastrados</h3>
    <?php if ($clientes->num_rows > 0): ?>
      <div class="table-responsive shadow-frame mt-3">
        <table class="table table-bordered table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Nome</th>
              <th>Telefone</th>
              <th>Email</th>
            </tr>
          </thead>
          <tbody>
            <?php while($c = $clientes->fetch_assoc()): ?>
              <tr>
                <td><?= $c['id_cliente'] ?></td>
                <td><?= $c['nome'] ?></td>
                <td><?= $c['telefone'] ?></td>
                <td><?= $c['email'] ?></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <a href="listar_clientes.php" class="btn btn-secondary">Ver todos os clientes</a>
    <?php else: ?>
      <div class="alert alert-warning mt-3">Nenhum cliente cadastrado ainda.</div>
    <?php endif; ?>
  </div>

  <!-- Últimos agendamentos -->
  <div class="mt-5">
    <h3><i class="bi bi-calendar-check"></i> Últimos Agendamentos</h3>
    <?php if ($agendamentos->num_rows > 0): ?>
      <div class="table-responsive shadow-frame mt-3">
        <table class="table table-bordered table-hover mb-0">
          <thead class="table-dark">
            <tr>
              <th>ID</th>
              <th>Cliente</th>
              <th>Serviço</th>
              <th>Data</th>
              <th>Hora</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while($a = $agendamentos->fetch_assoc()): ?>
              <tr>
                <td><?= $a['id_agendamento'] ?></td>
                <td><?= $a['cliente'] ?></td>
                <td><?= $a['servico'] ?></td>
                <td><?= date('d/m/Y', strtotime($a['data'])) ?></td>
                <td><?= date('H:i', strtotime($a['hora'])) ?></td>
                <td>
                  <?php
                    $badge = "secondary";
                    if ($a['status'] == "pendente") $badge = "warning";
                    if ($a['status'] == "confirmado") $badge = "primary";
                    if ($a['status'] == "concluido") $badge = "success";
                    if ($a['status'] == "cancelado") $badge = "danger";
                  ?>
                  <span class="badge bg-<?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
      <a href="listar_agendamentos.php" class="btn btn-secondary">Ver todos os agendamentos</a>
    <?php else: ?>
      <div class="alert alert-warning mt-3">Nenhum agendamento registrado ainda.</div>
    <?php endif; ?>
  </div>
</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
