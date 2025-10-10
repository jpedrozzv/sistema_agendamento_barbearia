<?php
include("conexao.php");
session_start();

if (!isset($_SESSION['cliente_id'])) {
    header("Location: login.php");
    exit;
}

// Buscar barbeiros
$barbeiros = $conn->query("SELECT * FROM Barbeiro");
// Buscar serviços
$servicos = $conn->query("SELECT id_servico, descricao, preco, duracao FROM Servico ORDER BY descricao");

// Buscar feriados cadastrados no banco
$feriados = [];
$res = $conn->query("SELECT data FROM Feriado");
while ($row = $res->fetch_assoc()) {
    $feriados[] = $row['data'];
}
$feriados_js = json_encode($feriados);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente  = $_SESSION['cliente_id'];
    $id_barbeiro = $_POST['id_barbeiro'];
    $id_servico  = $_POST['id_servico'];
    $data        = $_POST['data'];
    $hora        = $_POST['hora'];

    // Buscar duração do serviço
    $duracao_sql = "SELECT duracao FROM Servico WHERE id_servico = '$id_servico'";
    $duracao_res = $conn->query($duracao_sql);
    $duracao = ($duracao_res->num_rows > 0) ? $duracao_res->fetch_assoc()['duracao'] : 30;

    // Calcular intervalo
    $hora_inicio = strtotime($hora);
    $hora_fim    = strtotime("+$duracao minutes", $hora_inicio);

    // Verificar conflito
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
        $sql = "INSERT INTO Agendamento (id_cliente, id_barbeiro, id_servico, data, hora)
                VALUES ('$id_cliente', '$id_barbeiro', '$id_servico', '$data', '$hora')";
        if ($conn->query($sql) === TRUE) {
            $msg = "✅ Agendamento realizado com sucesso!";
            echo "<script>
                    setTimeout(function(){
                        window.location.href = 'cliente_dashboard.php';
                    }, 3000);
                  </script>";
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
  <title>Novo Agendamento - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>

  <style>
    #horarios-container button {
      min-width: 70px;
      font-weight: 500;
      transition: all 0.2s ease;
    }
    #horarios-container button.btn-success {
      color: white;
    }
    .legenda span {
      display: inline-block;
      margin-right: 15px;
      font-weight: 500;
    }
    .legenda .disponivel::before {
      content: "🟩";
      margin-right: 5px;
    }
    .legenda .ocupado::before {
      content: "🟥";
      margin-right: 5px;
    }
    .legenda .selecionado::before {
      content: "⚫";
      margin-right: 5px;
    }
  </style>
</head>

<body class="bg-light">

<div class="container mt-4">
  <h2>💈 Novo Agendamento</h2>

  <?php if (isset($msg)): ?>
    <div class="alert alert-info text-center fs-5"><?= $msg ?></div>
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
        <input type="text" id="data" name="data" class="form-control" placeholder="Selecione a data" required>
      </div>

      <div class="col-md-6 mb-3">
        <label class="form-label">Horário</label>
        <div class="legenda mb-2">
          <span class="disponivel">Disponível</span>
          <span class="ocupado">Ocupado</span>
          <span class="selecionado">Selecionado</span>
        </div>
        <div id="horarios-container" class="d-flex flex-wrap gap-2"></div>
        <input type="hidden" name="hora" id="hora" required>
        <small class="text-muted d-block mt-2" id="sem-horarios-msg"></small>
      </div>
    </div>

    <button type="submit" class="btn btn-success w-100">Agendar</button>
  </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const dataInput        = document.getElementById("data");
  const barbeiroSelect   = document.querySelector("select[name='id_barbeiro']");
  const horariosContainer= document.getElementById("horarios-container");
  const horaInput        = document.getElementById("hora");
  const msgSemHorarios   = document.getElementById("sem-horarios-msg");

  function renderHorarios(disponiveis) {
    horariosContainer.innerHTML = "";
    msgSemHorarios.textContent = "";

    const todos = [];
    for (let h = 9; h <= 17; h++) {
      todos.push(`${String(h).padStart(2,'0')}:00`);
      todos.push(`${String(h).padStart(2,'0')}:30`);
    }
    todos.push("18:00");

    let temDisponivel = false;

    todos.forEach(h => {
      const btn = document.createElement("button");
      btn.textContent = h;
      btn.classList.add("btn", "btn-sm", "m-1");

      if (disponiveis.includes(h)) {
        // Livre
        btn.classList.add("btn-outline-success");
        btn.addEventListener("click", () => {
          document.querySelectorAll("#horarios-container .btn-success")
                  .forEach(b => b.classList.replace("btn-success", "btn-outline-success"));
          btn.classList.replace("btn-outline-success", "btn-success");
          horaInput.value = h;
        });
        temDisponivel = true;
      } else {
        // Ocupado
        btn.classList.add("btn-outline-danger");
        btn.disabled = true;
      }
      horariosContainer.appendChild(btn);
    });

    if (!temDisponivel) {
      msgSemHorarios.textContent = "⚠️ Nenhum horário disponível para este dia.";
    }
  }

  async function carregarHorarios() {
    const id_barbeiro = barbeiroSelect.value;
    const data = dataInput.value;

    if (!id_barbeiro || !data) {
      horariosContainer.innerHTML = "";
      msgSemHorarios.textContent = "Selecione barbeiro e data.";
      horaInput.value = "";
      return;
    }

    try {
      horariosContainer.innerHTML = "<div class='text-secondary'>Carregando horários...</div>";
      horaInput.value = "";

      const url = `horarios_dispo.php?id_barbeiro=${encodeURIComponent(id_barbeiro)}&data=${encodeURIComponent(data)}`;
      const res = await fetch(url, { cache: "no-store" });
      const raw = await res.text();

      // Garante que falhas de JSON não quebrem a UI
      let disponiveis = [];
      try {
        disponiveis = JSON.parse(raw);
      } catch (e) {
        console.error("Resposta inválida do servidor:", raw);
        throw e;
      }

      renderHorarios(disponiveis);
    } catch (err) {
      console.error("Erro ao carregar horários:", err);
      horariosContainer.innerHTML = "<div class='text-danger'>Erro ao carregar horários.</div>";
    }
  }

  // Inicializa o calendário DEPOIS de definir as funções
  flatpickr("#data", {
    locale: "pt",
    minDate: "today",
    dateFormat: "Y-m-d",
    disableMobile: true,
    inline: true,
    weekNumbers: true,
    disable: [
      (d) => d.getDay() === 0,          // domingo
      ...<?php echo $feriados_js ?? '[]'; ?> // feriados do banco
    ],
    onChange: carregarHorarios
  });

  barbeiroSelect.addEventListener("change", carregarHorarios);
});
</script>


</body>
</html>
