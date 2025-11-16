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
    :root {
      --brand-dark: #0b0e16;
      --card-radius: 1rem;
    }

    body {
      background-color: #f8f9fa;
      font-family: "Inter", "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, "Helvetica Neue", sans-serif;
      min-height: 100vh;
      color: #212529;
    }

    main.auth-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 3rem 1rem;
    }

    .auth-card {
      border-radius: var(--card-radius);
      border: 1px solid #e9ecef;
      box-shadow: 0 25px 60px rgba(15, 23, 42, 0.08);
    }

    .auth-card .card-body {
      padding: 2.75rem 3rem;
    }

    .auth-title {
      font-size: 1.75rem;
      font-weight: 600;
      color: var(--brand-dark);
    }

    .auth-card .form-control {
      min-height: 48px;
      border-radius: 0.65rem;
      border-color: #ced4da;
      padding: 0.65rem 0.85rem;
      transition: border-color 0.2s ease, box-shadow 0.2s ease;
    }

    .auth-card .form-control:focus {
      border-color: #198754;
      box-shadow: 0 0 0 0.2rem rgba(25, 135, 84, 0.15);
    }

    .btn-success {
      min-height: 48px;
      border-radius: 0.65rem;
      font-weight: 600;
      box-shadow: 0 8px 20px rgba(25, 135, 84, 0.25);
    }

    .btn-success:hover {
      box-shadow: 0 12px 24px rgba(25, 135, 84, 0.35);
    }

    .alert-feedback {
      border-radius: 0.75rem;
      font-weight: 500;
    }

    @media (max-width: 575.98px) {
      .auth-card .card-body {
        padding: 2rem 1.5rem;
      }

      .auth-title {
        font-size: 1.5rem;
      }
    }
  </style>
</head>
<body>

<main class="auth-wrapper">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-9 col-md-7 col-lg-5">
        <div class="card auth-card">
          <div class="card-body">
            <div class="text-center mb-4">
              <p class="text-uppercase text-muted mb-1">Barbearia La Mafia</p>
              <h1 class="auth-title mb-2">Bem-vindo de volta</h1>
              <p class="text-muted mb-0">Acesse sua conta para continuar</p>
            </div>

            <?php if (!empty($erro)): ?>
              <div class="alert alert-danger alert-feedback" role="alert">
                <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
              </div>
            <?php endif; ?>

            <form method="POST" class="mt-4">
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

            <p class="text-center mt-4 mb-0">
              Ainda não tem conta?
              <a class="fw-semibold" href="cadastro_cliente.php">Crie sua conta agora</a>
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
