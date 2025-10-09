<?php
include("conexao.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha'];

    // Criptografa a senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    $sql = "INSERT INTO Cliente (nome, telefone, email, senha)
            VALUES ('$nome', '$telefone', '$email', '$senhaHash')";

    if ($conn->query($sql) === TRUE) {
        $msg = "✅ Registro feito com sucesso! Agora você já pode fazer login.";
    } else {
        $msg = "❌ Erro ao registrar: " . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Registrar - Barbearia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm">
        <div class="card-body">
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
