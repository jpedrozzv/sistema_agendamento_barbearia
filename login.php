<?php
session_start();
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Verifica se é admin
    $sql_admin = "SELECT * FROM Admin WHERE email = ?";
    $stmt = $conn->prepare($sql_admin);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res_admin = $stmt->get_result();

    if ($res_admin->num_rows > 0) {
        $admin = $res_admin->fetch_assoc();
        if (password_verify($senha, $admin['senha'])) {
            $_SESSION['admin_id'] = $admin['id_admin'];
            $_SESSION['admin_nome'] = $admin['nome'];
            $_SESSION['tipo'] = "admin";
            header("Location: admin_dashboard.php");
            exit;
        }
    }

    // Verifica se é cliente
    $sql_cliente = "SELECT * FROM Cliente WHERE email = ?";
    $stmt = $conn->prepare($sql_cliente);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $res_cliente = $stmt->get_result();

    if ($res_cliente->num_rows > 0) {
        $cliente = $res_cliente->fetch_assoc();
        if (password_verify($senha, $cliente['senha'])) {
            $_SESSION['cliente_id'] = $cliente['id_cliente'];
            $_SESSION['cliente_nome'] = $cliente['nome'];
            $_SESSION['tipo'] = "cliente";
            header("Location: cliente_dashboard.php");
            exit;
        }
    }

    // Se não encontrou ou senha errada
    $erro = "❌ E-mail ou senha inválidos.";
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
    }

    .auth-wrapper {
      padding-top: 3rem;
      padding-bottom: 3rem;
      min-height: 100vh;
      display: flex;
      align-items: center;
    }

    .auth-card {
      border-radius: 1rem;
    }

    .auth-card .form-control,
    .auth-card .btn {
      min-height: 48px;
    }

    .brand-icon {
      width: 64px;
      height: 64px;
      border-radius: 50%;
      background-color: rgba(25, 135, 84, 0.1);
      color: #198754;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 1.75rem;
      margin-bottom: 0.5rem;
    }

    @media (max-width: 575.98px) {
      .auth-wrapper {
        padding-top: 2rem;
        padding-bottom: 2rem;
      }

      .auth-card {
        padding: 1.5rem 1.25rem !important;
      }
    }
  </style>
</head>
<body class="bg-light">

<div class="container auth-wrapper">
  <div class="row justify-content-center w-100">
    <div class="col-12 col-sm-9 col-md-7 col-lg-5">
      <div class="card shadow-sm auth-card">
        <div class="card-body p-4">
          <div class="text-center mb-4">
            <div class="brand-icon">
              <span class="fw-semibold">LM</span>
            </div>
            <h3 class="mb-1">Bem-vindo de volta</h3>
            <p class="text-muted mb-0">Faça login para continuar</p>
          </div>

          <?php if (isset($erro)) echo "<div class='alert alert-danger'>$erro</div>"; ?>

          <form method="POST">
            <div class="mb-3">
              <label class="form-label" for="email">E-mail</label>
              <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" required>
            </div>
            <div class="mb-4">
              <label class="form-label" for="senha">Senha</label>
              <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Entrar</button>
          </form>

          <div class="text-center mt-3">
            Ainda não tem conta? <a href="cadastro_cliente.php">Criar conta</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
