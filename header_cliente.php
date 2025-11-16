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
  <style>
    .modal-confirm .modal-header { border-bottom: 0; }
    .modal-confirm .modal-footer { border-top: 0; }

    .navbar .container-fluid {
      flex-wrap: wrap;
      gap: 0.75rem;
    }

    .navbar .navbar-brand {
      white-space: normal;
      line-height: 1.2;
    }

    .navbar .btn {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      gap: 0.35rem;
    }

    @media (max-width: 575.98px) {
      .navbar .navbar-brand strong { display: block; }
      .navbar .d-flex { width: 100%; justify-content: flex-start; }
      .navbar .btn { width: 100%; }
      .navbar .btn-sm { padding: 0.55rem 0.75rem; font-size: 0.95rem; }
      .container { padding-left: 1rem; padding-right: 1rem; }
      .card-body > .table-responsive { margin: 0 -1rem; padding: 0 1rem; }
      .card-body > .table-responsive::-webkit-scrollbar { height: 6px; }
      .card-body > .table-responsive::-webkit-scrollbar-thumb { background-color: rgba(0, 0, 0, 0.2); border-radius: 999px; }
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center" href="cliente_dashboard.php">
      <img src="https://cdn-icons-png.flaticon.com/512/4339/4339982.png" width="26" height="26" class="me-2" alt="Mafia Hat">
      <strong>Barbearia La Mafia</strong> — Cliente
    </a>
    <div class="d-flex gap-2">
      <a href="cliente_dashboard.php" class="btn btn-outline-light btn-sm"><i class="bi bi-house-door"></i> Início</a>
      <a href="logout.php" class="btn btn-outline-light btn-sm">Sair</a>
    </div>
  </div>
</nav>

<div class="container mt-4">
