<?php
// header_cliente.php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
include_once "conexao.php";
include_once "verifica_cliente.php"; // garante cliente logado
include_once "alerta.php";
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Barbearia La Mafia — Área do Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="app-body">

<nav class="navbar navbar-expand-lg app-navbar py-3">
  <div class="container-fluid gap-3">
    <a class="navbar-brand d-flex align-items-center gap-2" href="cliente_dashboard.php">
      <i class="bi bi-person-badge"></i>
      <div>
        <strong>Barbearia La Mafia</strong><br>
        <small class="text-muted">Área do cliente</small>
      </div>
    </a>
    <div class="d-flex flex-wrap gap-2 ms-auto">
      <a href="cliente_dashboard.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-house-door"></i> Início
      </a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">
        <i class="bi bi-box-arrow-right"></i> Sair
      </a>
    </div>
  </div>
</nav>

<div class="container py-4">
