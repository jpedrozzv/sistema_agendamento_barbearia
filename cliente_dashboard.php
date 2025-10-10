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
  <title>Painel do Cliente - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    footer {
      background-color: #212529;
      color: #ccc;
      text-align: center;
      padding: 15px 0;
      margin-top: 50px;
    }
    .mafia-icon {
      width: 28px;
      height: 28px;
      vertical-align: middle;
      margin-right: 6px;
    }
  </style>
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm">
  <div class="container-fluid">
    <a class="navbar-brand fw-bold" href="cliente_dashboard.php">💈 Barber La Mafia</a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="bi bi-person-circle"></i> <?= $_SESSION['cliente_nome'] ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-right"></i> Sair
      </a>
    </div>
  </div>
</nav>

<!-- Conteúdo principal -->
<div class="container mt-5">
  <div class="text-center mb-5">
    <h1 class="fw-bold">Painel do Cliente</h1>
    <p class="lead">Agende novos horários e acompanhe seus agendamentos.</p>
  </div>

  <div class="row g-4">
    <div class="col-md-6">
      <div class="card shadow-sm text-center p-4 border-0">
        <i class="bi bi-calendar-plus display-4 text-primary"></i>
        <h5 class="card-title mt-3">Novo Agendamento</h5>
        <p class="card-text text-muted">Agende um novo horário na barbearia.</p>
        <a href="agendamento.php" class="btn btn-primary">Agendar</a>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm text-center p-4 border-0">
        <i class="bi bi-list-check display-4 text-success"></i>
        <h5 class="card-title mt-3">Meus Agendamentos</h5>
        <p class="card-text text-muted">Veja e cancele seus agendamentos.</p>
        <a href="meus_agendamentos.php" class="btn btn-success">Ver Agendamentos</a>
      </div>
    </div>
  </div>
</div>

<!-- Rodapé -->
<footer>
  <img class="mafia-icon" src="https://cdn-icons-png.flaticon.com/512/2073/2073905.png" alt="Mafioso">
  <span>© 2025 <strong>Barber La Mafia</strong> - Todos os direitos reservados</span>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
