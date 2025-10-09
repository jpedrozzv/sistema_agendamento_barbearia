<?php
include("conexao.php");
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nome  = $_POST['nome'];
    $telefone = $_POST['telefone'];
    $email = $_POST['email'];
    $senha = $_POST['senha']; // 👉 em produção deveria ser hash

    // Verifica se o e-mail já existe
    $check = $conn->query("SELECT * FROM Cliente WHERE email='$email'");
    if ($check->num_rows > 0) {
        $msg = "<div class='alert alert-danger'>❌ Este e-mail já está cadastrado!</div>";
    } else {
        $sql = "INSERT INTO Cliente (nome, telefone, email, senha) 
                VALUES ('$nome', '$telefone', '$email', '$senha')";
        if ($conn->query($sql) === TRUE) {
            $msg = "<div class='alert alert-success text-center'>
                        ✅ Cadastro realizado com sucesso!<br>
                        Você será redirecionado para o login...
                    </div>";
            header("refresh:4;url=login.php"); // redireciona em 4s
        } else {
            $msg = "<div class='alert alert-danger'>❌ Erro: " . $conn->error . "</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastro de Cliente - Barbearia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-5">
  <div class="row justify-content-center">
    <div class="col-md-5">
      <div class="card shadow-sm p-4">
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
