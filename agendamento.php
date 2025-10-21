<?php
session_start();
include("conexao.php");
include("verifica_cliente.php");
include("alerta.php");

$id_cliente = $_SESSION['cliente_id'];
$msg = null;

// --- SALVAR AGENDAMENTO ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_agendamento'])) {
    $id_barbeiro = intval($_POST['id_barbeiro']);
    $id_servico  = intval($_POST['id_servico']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $observacao = trim($_POST['observacao']);

    // Verifica se a data é feriado
    $isFeriado = $conn->query("SELECT 1 FROM Feriado WHERE data = '$data'")->num_rows > 0;

    if ($isFeriado) {
        $msg = ['danger', '🚫 Não é possível agendar em feriados.'];
    } else {
        // Verifica se o horário está ocupado
        $verifica = $conn->query("SELECT * FROM Agendamento WHERE data='$data' AND hora='$hora' AND id_barbeiro=$id_barbeiro");
        if ($verifica->num_rows > 0) {
            $msg = ['danger', '⚠️ Horário já ocupado. Escolha outro.'];
        } else {
            $sql = "INSERT INTO Agendamento (id_cliente, id_barbeiro, id_servico, data, hora, status, observacao)
                    VALUES ($id_cliente, $id_barbeiro, $id_servico, '$data', '$hora', 'pendente', '$observacao')";
            if ($conn->query($sql)) {
                $msg = ['success', '✅ Agendamento realizado com sucesso!'];
            } else {
                $msg = ['danger', '❌ Erro ao salvar agendamento. Tente novamente.'];
            }
        }
    }
}

// --- LISTAR BARBEIROS ---
$barbeiros = $conn->query("SELECT * FROM Barbeiro ORDER BY nome ASC");

// --- LISTAR SERVIÇOS ---
$servicos = $conn->query("SELECT * FROM Servico ORDER BY descricao ASC");

// --- LISTAR FERIADOS ---
$feriados = [];
$resFeriados = $conn->query("SELECT data FROM Feriado");
while ($f = $resFeriados->fetch_assoc()) {
    $feriados[] = $f['data'];
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>📅 Novo Agendamento - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .btn-horario {
      min-width: 90px;
      margin: 3px;
      border-radius: 8px;
    }
    .btn-horario.disponivel { background-color: #28a745; color: #fff; }
    .btn-horario.ocupado { background-color: #6c757d; color: #fff; cursor: not-allowed; }
    .btn-horario.selecionado { background-color: #0d6efd !important; color: #fff; }
    .calendar-disabled {
      background-color: #f8d7da !important;
      color: #721c24 !important;
      pointer-events: none;
      opacity: 0.6;
    }
  </style>
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Novo Agendamento</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <div class="card shadow-sm p-4">
    <form method="POST">
      <div class="row mb-3">
        <div class="col-md-6">
          <label class="form-label">Barbeiro</label>
          <select name="id_barbeiro" class="form-select" required>
            <option value="">Selecione</option>
            <?php while ($b = $barbeiros->fetch_assoc()): ?>
              <option value="<?= $b['id_barbeiro'] ?>"><?= htmlspecialchars($b['nome']) ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Serviço</label>
          <select name="id_servico" class="form-select" required>
            <option value="">Selecione</option>
            <?php while ($s = $servicos->fetch_assoc()): ?>
              <option value="<?= $s['id_servico'] ?>">
                <?= htmlspecialchars($s['descricao']) ?> (<?= $s['duracao'] ?> min)
              </option>
            <?php endwhile; ?>
          </select>
        </div>
      </div>

      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label">Data</label>
          <input type="date" name="data" id="data" class="form-control" required>
        </div>
        <div class="col-md-8">
          <label class="form-label">Horários Disponíveis</label><br>
          <div id="horarios" class="d-flex flex-wrap">
            <span class="text-muted">Selecione a data primeiro</span>
          </div>
        </div>
      </div>

      <div class="mb-3">
        <label class="form-label">Observação (opcional)</label>
        <textarea name="observacao" class="form-control" rows="2" placeholder="Ex: Prefiro máquina 0 ou navalha."></textarea>
      </div>

      <input type="hidden" name="hora" id="horaSelecionada">

      <div class="text-center">
        <button type="submit" name="confirmar_agendamento" class="btn btn-success px-4">
          <i class="bi bi-calendar-check"></i> Confirmar Agendamento
        </button>
        <a href="cliente_dashboard.php" class="btn btn-secondary px-4 ms-2">
          <i class="bi bi-arrow-left-circle"></i> Voltar
        </a>
      </div>
    </form>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const feriados = <?= json_encode($feriados) ?>;

// Desabilita feriados e domingos
const inputData = document.getElementById('data');
inputData.addEventListener('input', () => {
  const data = inputData.value;
  const dia = new Date(data + "T00:00:00").getDay();
  
  if (feriados.includes(data) || dia === 0) {
    inputData.value = "";
    alert("🚫 Não é possível agendar em domingos ou feriados.");
    return;
  }

  fetch(`horarios_dispo.php?data=${data}`)
    .then(resp => resp.json())
    .then(horarios => {
      const div = document.getElementById('horarios');
      div.innerHTML = "";
      horarios.forEach(h => {
        const btn = document.createElement('button');
        btn.type = "button";
        btn.className = "btn btn-horario " + (h.disponivel ? "disponivel" : "ocupado");
        btn.textContent = h.hora;
        btn.disabled = !h.disponivel;
        btn.onclick = () => {
          document.querySelectorAll(".btn-horario").forEach(b => b.classList.remove("selecionado"));
          btn.classList.add("selecionado");
          document.getElementById('horaSelecionada').value = h.hora;
        };
        div.appendChild(btn);
      });
    });
});
</script>
</body>
</html>
