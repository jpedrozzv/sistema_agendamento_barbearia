<?php
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = trim($_POST['nome'] ?? '');
    $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $telefone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 4) {
        $msg = "❌ Dados inválidos. Verifique as informações e tente novamente.";
    } else {
        try {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $stmt = $conn->prepare('INSERT INTO Cliente (nome, telefone, email, senha) VALUES (?, ?, ?, ?)');
            $stmt->bind_param('ssss', $nome, $telefone, $email, $senhaHash);
            $stmt->execute();
            $stmt->close();

            $msg = "✅ Registro feito com sucesso! Agora você já pode fazer login.";
        } catch (Throwable $exception) {
            error_log('Erro ao registrar cliente: ' . $exception->getMessage());
            $msg = "❌ Erro ao registrar. Tente novamente mais tarde.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Registrar - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-wrapper">
  <div class="auth-card">
    <div class="brand-area">
      <div class="brand-icon">
        <i class="bi bi-person-plus"></i>
      </div>
      <h1>Registrar</h1>
      <p>Crie sua conta para acessar o painel completo.</p>
    </div>

    <?php if (isset($msg)) echo "<div class='alert alert-info text-center'>$msg</div>"; ?>

    <form method="POST">
      <div class="mb-3">
        <label class="form-label">Nome</label>
        <input type="text" name="nome" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Telefone</label>
        <input type="text" name="telefone" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" required>
      </div>
      <div class="mb-4">
        <label class="form-label">Senha</label>
        <input type="password" name="senha" class="form-control" required>
      </div>
      <button type="submit" class="btn btn-primary w-100">Registrar</button>
    </form>

    <div class="text-center mt-4">
      <p class="text-muted mb-1">Já tem conta?</p>
      <a href="login.php" class="fw-semibold">Fazer Login</a>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
