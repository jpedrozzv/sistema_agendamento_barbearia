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
  <style>
    .auth-wrapper {
      padding-top: 3rem;
      padding-bottom: 3rem;
    }

    .auth-card {
      border-radius: 1rem;
    }

    .auth-card .form-control,
    .auth-card .btn {
      min-height: 48px;
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
  <div class="row justify-content-center">
    <div class="col-12 col-sm-9 col-md-7 col-lg-5">
      <div class="card shadow-sm auth-card">
        <div class="card-body p-4">
          <h3 class="text-center">Criar Conta</h3>
          <?php if (isset($msg)) echo "<div class='alert alert-info'>$msg</div>"; ?>
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
            <div class="mb-3">
              <label class="form-label">Senha</label>
              <input type="password" name="senha" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success w-100">Registrar</button>
          </form>
          <div class="text-center mt-3">
            Já tem conta? <a href="login.php">Fazer Login</a>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
