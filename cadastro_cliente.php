<?php
include("conexao.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = trim($_POST['nome'] ?? '');
    $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';

    if ($nome === '' || $telefone === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($senha) < 4) {
        $msg = "<div class='alert alert-danger'>❌ Dados informados são inválidos. Verifique e tente novamente.</div>";
    } else {
        try {
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

            $check = $conn->prepare('SELECT 1 FROM Cliente WHERE email = ? LIMIT 1');
            $check->bind_param('s', $email);
            $check->execute();
            $check->store_result();

            if ($check->num_rows > 0) {
                $msg = "<div class='alert alert-danger'>❌ Este e-mail já está cadastrado!</div>";
            } else {
                $stmt = $conn->prepare('INSERT INTO Cliente (nome, telefone, email, senha) VALUES (?, ?, ?, ?)');
                $stmt->bind_param('ssss', $nome, $telefone, $email, $senhaHash);
                $stmt->execute();

                $msg = "<div class='alert alert-success text-center'>
                            ✅ Cadastro realizado com sucesso!<br>
                            Você será redirecionado para o login...
                        </div>";
                header("refresh:4;url=login.php");
                $stmt->close();
            }

            $check->close();
        } catch (Throwable $exception) {
            error_log('Erro ao cadastrar cliente: ' . $exception->getMessage());
            $msg = "<div class='alert alert-danger'>❌ Erro ao processar o cadastro.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cadastro de Cliente - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .auth-wrapper {
      padding-top: 3rem;
      padding-bottom: 3rem;
    }

    .auth-card {
      border-radius: 1rem;
    }

    .auth-card .form-control {
      min-height: 48px;
    }

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
      <div class="card shadow-sm p-4 auth-card">
        <h3 class="text-center mb-3">📝 Cadastro de Cliente</h3>

        <?php if (isset($msg)) echo $msg; ?>

        <form method="POST">
          <div class="mb-3">
            <label class="form-label">Nome</label>
            <input type="text" name="nome" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Telefone</label>
            <input type="text" name="telefone" class="form-control" placeholder="(XX) XXXXX-XXXX" required>
          </div>

          <div class="mb-3">
            <label class="form-label">E-mail</label>
            <input type="email" name="email" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Senha</label>
            <input type="password" name="senha" class="form-control" required>
          </div>

          <button type="submit" class="btn btn-success w-100">Cadastrar</button>
        </form>

        <div class="text-center mt-3">
          <p>Já tem conta? <a href="login.php">Entrar</a></p>
        </div>
      </div>
    </div>
  </div>
</div>

</body>
</html>
