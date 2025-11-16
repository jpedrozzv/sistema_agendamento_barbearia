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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar app-navbar py-3">
  <div class="container-fluid gap-3">
    <a class="navbar-brand" href="index.php">
      💈 Barbearia La Mafia
    </a>
    <div class="d-flex flex-wrap gap-2 ms-auto">
      <a class="btn btn-outline-light btn-sm" href="cadastro_cliente.php"><i class="bi bi-person-plus"></i> Cadastrar cliente</a>
      <a class="btn btn-outline-light btn-sm" href="agendamento.php"><i class="bi bi-calendar-plus"></i> Novo agendamento</a>
      <a class="btn btn-outline-light btn-sm" href="listar_agendamentos.php"><i class="bi bi-list-check"></i> Lista de agendamentos</a>
      <a class="btn btn-outline-light btn-sm" href="listar_clientes.php"><i class="bi bi-people"></i> Lista de clientes</a>
    </div>
  </div>
</nav>

<div class="container py-5">
  <div class="text-center mb-5">
    <h1 class="mb-3">Sistema de Agendamento</h1>
    <p class="section-lead">Gerencie clientes, serviços e horários com uma experiência dark premium inspirada no universo da barbearia.</p>
  </div>

  <div class="row g-4 mb-5">
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card quick-card text-center shadow-sm w-100">
        <i class="bi bi-person-plus"></i>
        <h5 class="card-title">Cadastrar Cliente</h5>
        <p class="card-text">Adicione novos clientes ao sistema.</p>
        <a href="cadastro_cliente.php" class="btn btn-primary">Cadastrar</a>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card quick-card text-center shadow-sm w-100">
        <i class="bi bi-calendar-plus"></i>
        <h5 class="card-title">Novo Agendamento</h5>
        <p class="card-text">Crie novos horários em poucos cliques.</p>
        <a href="agendamento.php" class="btn btn-primary">Agendar</a>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card quick-card text-center shadow-sm w-100">
        <i class="bi bi-list-check"></i>
        <h5 class="card-title">Agendamentos</h5>
        <p class="card-text">Visualize e acompanhe todas as reservas.</p>
        <a href="listar_agendamentos.php" class="btn btn-primary">Listar</a>
      </div>
    </div>
    <div class="col-12 col-sm-6 col-lg-3 d-flex">
      <div class="card quick-card text-center shadow-sm w-100">
        <i class="bi bi-people"></i>
        <h5 class="card-title">Clientes</h5>
        <p class="card-text">Consulte e edite cadastros rapidamente.</p>
        <a href="listar_clientes.php" class="btn btn-primary">Listar</a>
      </div>
    </div>
  </div>

  <div class="row g-4">
    <div class="col-12 col-xl-6">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0"><i class="bi bi-people"></i> Últimos Clientes</h3>
            <a href="listar_clientes.php" class="btn btn-link">Ver todos</a>
          </div>
          <?php if ($clientes->num_rows > 0): ?>
            <div class="table-responsive mt-3">
              <table class="table table-hover align-middle mb-0">
                <thead>
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
                      <td><?= htmlspecialchars($c['nome']) ?></td>
                      <td><?= htmlspecialchars($c['telefone']) ?></td>
                      <td><?= htmlspecialchars($c['email']) ?></td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-warning mt-3">Nenhum cliente cadastrado ainda.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div class="col-12 col-xl-6">
      <div class="card shadow-sm h-100">
        <div class="card-body">
          <div class="d-flex justify-content-between align-items-center mb-3">
            <h3 class="mb-0"><i class="bi bi-calendar-check"></i> Últimos Agendamentos</h3>
            <a href="listar_agendamentos.php" class="btn btn-link">Ver todos</a>
          </div>
          <?php if ($agendamentos->num_rows > 0): ?>
            <div class="table-responsive mt-3">
              <table class="table table-hover align-middle mb-0">
                <thead>
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
                      <td><?= htmlspecialchars($a['cliente']) ?></td>
                      <td><?= htmlspecialchars($a['servico']) ?></td>
                      <td><?= date('d/m/Y', strtotime($a['data'])) ?></td>
                      <td><?= date('H:i', strtotime($a['hora'])) ?></td>
                      <td>
                        <?php
                          $badge = match($a['status']) {
                            'pendente' => 'warning',
                            'confirmado' => 'primary',
                            'concluido' => 'success',
                            'cancelado' => 'danger',
                            default => 'secondary'
                          };
                        ?>
                        <span class="badge bg-<?= $badge ?>"><?= ucfirst($a['status']) ?></span>
                      </td>
                    </tr>
                  <?php endwhile; ?>
                </tbody>
              </table>
            </div>
          <?php else: ?>
            <div class="alert alert-warning mt-3">Nenhum agendamento registrado ainda.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
