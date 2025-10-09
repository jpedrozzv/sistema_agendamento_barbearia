<?php
session_start();
if (!isset($_SESSION['cliente_id']) || $_SESSION['tipo'] != "cliente") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel do Cliente - Barbearia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barbearia</a>
    <div class="d-flex">
      <span class="navbar-text text-white me-3">
        Olá, <?= $_SESSION['cliente_nome'] ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light">Sair</a>
    </div>
  </div>
</nav>

<!-- Conteúdo principal -->
<div class="container mt-5">
  <div class="text-center">
    <h1 class="mb-4">Painel do Cliente</h1>
    <p class="lead">Aqui você pode agendar novos horários e acompanhar seus agendamentos.</p>
  </div>

  <div class="row mt-5">
    <div class="col-md-6">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-calendar-plus display-4 text-primary"></i>
        <h5 class="card-title mt-2">Novo Agendamento</h5>
        <p class="card-text">Agende um novo horário com a barbearia.</p>
        <a href="agendamento.php" class="btn btn-primary">Agendar</a>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-list-check display-4 text-success"></i>
        <h5 class="card-title mt-2">Meus Agendamentos</h5>
        <p class="card-text">Veja seus agendamentos e cancele quando necessário.</p>
        <a href="meus_agendamentos.php" class="btn btn-success">Ver Agendamentos</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
