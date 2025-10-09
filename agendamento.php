<?php
include("conexao.php");

session_start();
if (!isset($_SESSION['cliente_id']) || $_SESSION['tipo'] != "cliente") {
    header("Location: login.php");
    exit;
}

// Buscar barbeiros
$barbeiros = $conn->query("SELECT * FROM Barbeiro");

// Buscar serviços cadastrados
$servicos = $conn->query("SELECT id_servico, descricao, preco, duracao FROM Servico ORDER BY descricao");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente  = $_SESSION['cliente_id']; // pega o cliente logado
    $id_barbeiro = $_POST['id_barbeiro'];
    $id_servico  = $_POST['id_servico'];
    $data        = $_POST['data'];
    $hora        = $_POST['hora'];

    // Buscar duração do serviço
    $duracao_sql = "SELECT duracao FROM Servico WHERE id_servico = '$id_servico'";
    $duracao_res = $conn->query($duracao_sql);
    $duracao = ($duracao_res->num_rows > 0) ? $duracao_res->fetch_assoc()['duracao'] : 30;

    // Calcular intervalo do novo agendamento
    $hora_inicio = strtotime($hora);
    $hora_fim    = strtotime("+$duracao minutes", $hora_inicio);

    // Verificar conflito com outros agendamentos do barbeiro
    $check_sql = "
        SELECT a.hora, s.duracao
        FROM Agendamento a
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_barbeiro = '$id_barbeiro'
          AND a.data = '$data'
          AND a.status IN ('pendente','confirmado')
    ";
    $res = $conn->query($check_sql);

    $conflito = false;
    while ($row = $res->fetch_assoc()) {
        $existente_inicio = strtotime($row['hora']);
        $existente_fim    = strtotime("+{$row['duracao']} minutes", $existente_inicio);

        if ($hora_inicio < $existente_fim && $hora_fim > $existente_inicio) {
            $conflito = true;
            break;
        }
    }

    if ($conflito) {
        $msg = "⚠️ Esse barbeiro já tem outro atendimento nesse horário!";
    } else {
        $sql = "INSERT INTO Agendamento (id_cliente, id_barbeiro, id_servico, data, hora, status)
                VALUES ('$id_cliente', '$id_barbeiro', '$id_servico', '$data', '$hora', 'pendente')";
        if ($conn->query($sql) === TRUE) {
            $msg = "<h4 class='text-success text-center'>✅ Agendamento realizado com sucesso!</h4>
                <p class='text-center'>Você será redirecionado em instantes...</p>";
                header("refresh:4;url=cliente_dashboard.php"); 


        } else {
            $msg = "❌ Erro: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Novo Agendamento</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <h2>Novo Agendamento</h2>

  <?php if (isset($msg)): ?>
    <div class="alert alert-info"><?= $msg ?></div>
  <?php endif; ?>

  <form method="POST" class="card p-4 shadow-sm">

    <div class="mb-3">
      <label class="form-label">Barbeiro</label>
      <select name="id_barbeiro" class="form-select" required>
        <option value="">Selecione um barbeiro</option>
        <?php while($b = $barbeiros->fetch_assoc()) { ?>
          <option value="<?= $b['id_barbeiro'] ?>"><?= $b['nome'] ?> - <?= $b['especialidade'] ?></option>
        <?php } ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Serviço</label>
      <select name="id_servico" class="form-select" required>
        <option value="">Selecione um serviço</option>
        <?php while ($s = $servicos->fetch_assoc()): ?>
          <option value="<?= $s['id_servico'] ?>">
            <?= $s['descricao'] ?> - R$ <?= number_format($s['preco'], 2, ',', '.') ?> (<?= $s['duracao'] ?> min)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="row">
      <div class="col-md-6 mb-3">
        <label class="form-label">Data</label>
        <input type="date" name="data" class="form-control" required>
      </div>
      <div class="col-md-6 mb-3">
        <label class="form-label">Hora</label>
        <input type="time" name="hora" class="form-control" required>
      </div>
    </div>

    <button type="submit" class="btn btn-success">Agendar</button>
    

  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
