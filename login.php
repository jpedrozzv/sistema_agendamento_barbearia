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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<main class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-area">
      <div class="brand-icon">
        <i class="bi bi-scissors"></i>
      </div>
      <span class="text-uppercase text-muted small">Barbearia La Mafia</span>
      <h1>Entrar</h1>
      <p class="mb-0">Acesse para gerenciar horários e clientes.</p>
    </div>

    <?php if (!empty($erro)): ?>
      <div class="alert alert-danger" role="alert">
        <?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8'); ?>
      </div>
    <?php endif; ?>

    <form method="POST" class="mt-4 position-relative">
      <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" required>
      </div>
      <div class="mb-4">
        <label class="form-label" for="senha">Senha</label>
        <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Entrar</button>
    </form>

    <p class="text-center mt-4 mb-0 text-muted">
      Ainda não tem conta?
      <a class="fw-semibold" href="cadastro_cliente.php">Cadastre-se para começar</a>
    </p>
  </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
