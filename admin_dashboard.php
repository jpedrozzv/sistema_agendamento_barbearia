<?php
session_start();
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Painel do Admin - Barbearia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Painel do Admin</a>
    <div class="d-flex">
      <span class="navbar-text text-white me-3">
        Olá, <?= $_SESSION['admin_nome'] ?>
      </span>
      <a href="logout.php" class="btn btn-outline-light">Sair</a>
    </div>
  </div>
</nav>

<!-- Conteúdo principal -->
<div class="container mt-5">
  <div class="text-center">
    <h1 class="mb-4">Painel do Dono da Barbearia</h1>
    <p class="lead">Gerencie clientes, agendamentos e serviços de forma prática.</p>
  </div>

  <div class="row mt-5">
    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-people display-4 text-secondary"></i>
        <h5 class="card-title mt-2">Clientes</h5>
        <p class="card-text">Visualize, edite e remova clientes cadastrados.</p>
        <a href="listar_clientes.php" class="btn btn-secondary">Gerenciar</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-calendar-check display-4 text-primary"></i>
        <h5 class="card-title mt-2">Agendamentos</h5>
        <p class="card-text">Controle todos os agendamentos da barbearia.</p>
        <a href="listar_agendamentos.php" class="btn btn-primary">Gerenciar</a>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm text-center p-3">
        <i class="bi bi-scissors display-4 text-success"></i>
        <h5 class="card-title mt-2">Serviços</h5>
        <p class="card-text">Adicione ou altere serviços e preços.</p>
        <a href="listar_servicos.php" class="btn btn-success">Gerenciar</a>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
