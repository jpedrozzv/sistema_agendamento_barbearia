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
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
</head>
<body class="auth-page">

<div class="auth-wrapper">
  <div class="auth-card position-relative">
    <div class="brand-area">
      <div class="brand-icon">
        <i class="bi bi-person-check"></i>
      </div>
      <span class="text-uppercase text-muted small">Cadastre-se</span>
      <h1>Criar conta</h1>
      <p>Cadastre-se para agendar seus horários com mais praticidade.</p>
    </div>

    <?php if (isset($msg)) echo $msg; ?>

    <form method="POST" class="position-relative z-2">
      <div class="mb-3">
        <label class="form-label" for="nome">Nome completo</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-person"></i></span>
          <input type="text" id="nome" name="nome" class="form-control" placeholder="Seu nome" value="<?= htmlspecialchars($_POST['nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="telefone">Telefone</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-telephone"></i></span>
          <input type="text" id="telefone" name="telefone" class="form-control" placeholder="(XX) XXXXX-XXXX" value="<?= htmlspecialchars($_POST['telefone'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label" for="email">E-mail</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-envelope"></i></span>
          <input type="email" id="email" name="email" class="form-control" placeholder="seuemail@exemplo.com" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" required>
        </div>
      </div>

      <div class="mb-4">
        <label class="form-label" for="senha">Senha</label>
        <div class="input-group">
          <span class="input-group-text"><i class="bi bi-lock"></i></span>
          <input type="password" id="senha" name="senha" class="form-control" placeholder="Crie uma senha" required>
        </div>
        <small class="text-muted">Mínimo de 4 caracteres.</small>
      </div>

      <button type="submit" class="btn btn-primary w-100">Criar conta</button>
    </form>

    <div class="text-center mt-4 position-relative z-2">
      <p class="mb-0 text-muted">Já possui cadastro?</p>
      <a href="login.php" class="fw-semibold">Ir para o login</a>
    </div>
  </div>
</div>

</body>
</html>
