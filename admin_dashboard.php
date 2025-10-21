<?php
session_start();
include("conexao.php");
include("verifica_adm.php");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel do Admin - Barbearia La Mafia</title>
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
    <a class="navbar-brand fw-bold" href="admin_dashboard.php">💈 Painel do Admin</a>
    <div class="d-flex align-items-center">
      <span class="text-white me-3">
        <i class="bi bi-person-circle"></i> <?= $_SESSION['admin_nome'] ?>
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
    <h1 class="fw-bold">Painel do Dono da Barbearia</h1>
    <p class="lead">Gerencie clientes, agendamentos e serviços de forma prática.</p>
  </div>

  <div class="row g-4">
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-4 border-0">
        <i class="bi bi-people display-4 text-secondary"></i>
        <h5 class="card-title mt-3">Clientes</h5>
        <p class="card-text text-muted">Visualize, edite e remova clientes cadastrados.</p>
        <a href="listar_clientes.php" class="btn btn-secondary">Gerenciar</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm text-center p-4 border-0">
        <i class="bi bi-calendar-check display-4 text-primary"></i>
        <h5 class="card-title mt-3">Agendamentos</h5>
        <p class="card-text text-muted">Controle todos os agendamentos da barbearia.</p>
        <a href="listar_agendamentos_adm.php" class="btn btn-primary">Gerenciar</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm text-center p-4 border-0">
        <i class="bi bi-scissors display-4 text-success"></i>
        <h5 class="card-title mt-3">Serviços</h5>
        <p class="card-text text-muted">Adicione ou altere serviços e preços.</p>
        <a href="listar_servicos.php" class="btn btn-success">Gerenciar</a>
      </div>
    </div>
  </div>
</div>

<div class="col-md-4 mt-4">
  <div class="card shadow-sm text-center p-3">
    <i class="bi bi-calendar-x display-4 text-danger"></i>
    <h5 class="card-title mt-2">Feriados</h5>
    <p class="card-text">Adicione e gerencie feriados e dias de folga.</p>
    <a href="gerenciar_feriados.php" class="btn btn-danger">Gerenciar</a>
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
