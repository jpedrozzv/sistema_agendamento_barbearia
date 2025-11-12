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
  <title>Login - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600&display=swap" rel="stylesheet">
  <style>
    body {
      min-height: 100vh;
      margin: 0;
      font-family: 'Montserrat', sans-serif;
      background: radial-gradient(circle at top left, rgba(22, 40, 66, 0.55), transparent 60%),
                  radial-gradient(circle at bottom right, rgba(3, 9, 19, 0.85), #050608 80%);
      background-color: #050608;
      color: #f5f5f5;
    }

    .login-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
    }

    .login-card {
      background: linear-gradient(150deg, rgba(11, 14, 22, 0.95), rgba(9, 22, 39, 0.95));
      border-radius: 1.25rem;
      border: 1px solid rgba(54, 107, 171, 0.35);
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65);
      overflow: hidden;
      max-width: 420px;
      width: 100%;
      padding: 3rem 2.75rem;
      position: relative;
    }

    .login-card::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(54, 107, 171, 0.2), transparent 55%);
      pointer-events: none;
    }

    .login-card::after {
      content: "";
      position: absolute;
      top: 0;
      right: 1.2rem;
      height: 100%;
      width: 6px;
      background: repeating-linear-gradient(
        180deg,
        #0f1e33,
        #0f1e33 10px,
        #ffffff 10px,
        #ffffff 20px,
        #b32026 20px,
        #b32026 30px
      );
      opacity: 0.25;
      filter: blur(0.5px);
    }

    .brand-area {
      text-align: center;
      margin-bottom: 2rem;
      position: relative;
      z-index: 2;
    }

    .brand-area .brand-icon {
      width: 68px;
      height: 68px;
      border-radius: 50%;
      background: linear-gradient(145deg, rgba(54, 107, 171, 0.35), rgba(5, 11, 20, 0.95));
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 1rem;
      font-size: 2rem;
      color: #c5ddff;
      border: 1px solid rgba(81, 142, 213, 0.4);
      box-shadow: inset 0 0 12px rgba(11, 24, 40, 0.9);
    }

    .brand-area h1 {
      font-family: 'Playfair Display', serif;
      font-size: 1.9rem;
      margin-bottom: 0.25rem;
      letter-spacing: 0.05em;
      color: #ebf4ff;
    }

    .brand-area span {
      font-size: 0.95rem;
      color: rgba(197, 213, 237, 0.75);
      text-transform: uppercase;
      letter-spacing: 0.35em;
      font-weight: 600;
      display: inline-block;
      padding-top: 0.25rem;
    }

    label.form-label {
      font-weight: 600;
      color: rgba(229, 236, 246, 0.85);
      position: relative;
      z-index: 2;
    }

    .input-group-text {
      background: rgba(21, 43, 72, 0.6);
      border: 1px solid rgba(54, 107, 171, 0.45);
      color: #9fc4ff;
    }

    .form-control {
      background-color: rgba(10, 15, 24, 0.85);
      border: 1px solid rgba(54, 107, 171, 0.3);
      color: #f5f5f5;
    }

    .form-control:focus {
      background-color: rgba(13, 22, 35, 0.95);
      border-color: rgba(126, 173, 232, 0.65);
      box-shadow: 0 0 0 0.2rem rgba(54, 107, 171, 0.25);
      color: #fff;
    }

    .btn-login {
      background: linear-gradient(140deg, #1e3a5c, #10213a 55%, #1f4f82);
      border: 1px solid rgba(88, 146, 214, 0.45);
      color: #f5f8ff;
      font-weight: 700;
      text-transform: uppercase;
      letter-spacing: 0.08em;
      padding: 0.9rem 1rem;
      border-radius: 0.9rem;
      transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
      position: relative;
      z-index: 2;
      box-shadow: 0 18px 32px rgba(9, 21, 36, 0.55);
    }

    .btn-login:hover {
      transform: translateY(-2px);
      filter: brightness(1.08);
      box-shadow: 0 22px 38px rgba(7, 15, 26, 0.65);
    }

    .signup-link {
      color: rgba(146, 187, 240, 0.9);
      font-weight: 500;
      text-decoration: none;
    }

    .signup-link:hover {
      color: #a9c8ff;
      text-decoration: underline;
    }

    .alert {
      border-radius: 0.75rem;
      font-weight: 500;
      background-color: rgba(179, 32, 38, 0.18);
      border-color: rgba(179, 32, 38, 0.4);
      color: #ffb9bf;
    }

    .text-muted {
      color: rgba(214, 223, 236, 0.6) !important;
    }
  </style>
</head>
<body>

<div class="login-wrapper">
  <div class="login-card position-relative">
    <div class="brand-area">
      <div class="brand-icon">
        <i class="bi bi-scissors"></i>
      </div>
      <h1>Barbearia La Mafia</h1>
      <span>Bem-vindo</span>
    </div>

    <?php if (isset($erro)): ?>
      <div class="alert alert-danger" role="alert"><?= $erro ?></div>
    <?php endif; ?>

    <form method="POST" class="position-relative z-2">
      <div class="mb-3">
        <label for="email" class="form-label">E-mail</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" required>
        </div>
      </div>
      <div class="mb-4">
        <label for="senha" class="form-label">Senha</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" id="senha" name="senha" class="form-control" placeholder="Digite sua senha" required>
        </div>
      </div>
      <button type="submit" class="btn btn-login w-100">Entrar</button>
    </form>

    <div class="text-center mt-4 position-relative z-2">
      <p class="mb-0 text-muted">Ainda não tem cadastro?</p>
      <a href="cadastro_cliente.php" class="signup-link">Criar conta agora</a>
    </div>
  </div>
</div>

</body>
</html>
