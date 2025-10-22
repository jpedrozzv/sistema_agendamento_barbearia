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
  <title>Barbearia La Mafia — Área do Cliente</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .modal-confirm .modal-header { border-bottom: 0; }
    .modal-confirm .modal-footer { border-top: 0; }
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
