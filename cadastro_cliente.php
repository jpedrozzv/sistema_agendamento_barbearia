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

    .auth-wrapper {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 2rem 1.5rem;
    }

    .auth-card {
      background: linear-gradient(150deg, rgba(11, 14, 22, 0.95), rgba(9, 22, 39, 0.95));
      border-radius: 1.25rem;
      border: 1px solid rgba(54, 107, 171, 0.35);
      box-shadow: 0 25px 60px rgba(0, 0, 0, 0.65);
      overflow: hidden;
      max-width: 480px;
      width: 100%;
      padding: 3rem 2.75rem;
      position: relative;
    }

    .auth-card::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top right, rgba(54, 107, 171, 0.2), transparent 55%);
      pointer-events: none;
    }

    .auth-card::after {
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

    .brand-area p {
      color: rgba(214, 223, 236, 0.8);
      margin-top: 0.5rem;
      font-size: 0.95rem;
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
      min-height: 48px;
    }

    .form-control {
      background-color: rgba(10, 15, 24, 0.85);
      border: 1px solid rgba(54, 107, 171, 0.3);
      color: #f5f5f5;
      min-height: 48px;
    }

    .form-control:focus {
      background-color: rgba(13, 22, 35, 0.95);
      border-color: rgba(126, 173, 232, 0.65);
      box-shadow: 0 0 0 0.2rem rgba(54, 107, 171, 0.25);
      color: #fff;
    }

    .btn-register {
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

    .btn-register:hover {
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

    .alert-success {
      background-color: rgba(38, 179, 103, 0.18);
      border-color: rgba(38, 179, 103, 0.4);
      color: #c7ffde;
    }

    .text-muted {
      color: rgba(214, 223, 236, 0.6) !important;
    }

    @media (max-width: 575.98px) {
      .auth-wrapper { padding: 1.5rem 1rem; }
      .auth-card { padding: 2.5rem 1.75rem; border-radius: 1.15rem; }
      .brand-area h1 { font-size: 1.6rem; }
      .brand-area span { letter-spacing: 0.28em; font-size: 0.85rem; }
      .btn-register { padding: 1rem; font-size: 0.95rem; }
      .input-group-text { min-height: 46px; }
      .form-control { min-height: 46px; }
    }
  </style>
</head>
<body>

<div class="auth-wrapper">
  <div class="auth-card position-relative">
    <div class="brand-area">
      <div class="brand-icon">
        <i class="bi bi-person-check"></i>
      </div>
      <h1>Criar conta</h1>
      <span>Cadastre-se</span>
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

      <button type="submit" class="btn btn-register w-100">Criar conta</button>
    </form>

    <div class="text-center mt-4 position-relative z-2">
      <p class="mb-0 text-muted">Já possui cadastro?</p>
      <a href="login.php" class="signup-link">Ir para o login</a>
    </div>
  </div>
</div>

</body>
</html>
