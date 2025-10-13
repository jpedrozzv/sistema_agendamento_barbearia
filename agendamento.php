<?php
include("conexao.php");
session_start();

if (!isset($_SESSION['cliente_id']) || $_SESSION['tipo'] != "cliente") {
    header("Location: login.php");
    exit;
}

$barbeiros = $conn->query("SELECT * FROM Barbeiro");
$servicos = $conn->query("SELECT * FROM Servico");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente  = $_SESSION['cliente_id'];
    $id_barbeiro = $_POST['id_barbeiro'];
    $id_servico  = $_POST['id_servico'];
    $data        = $_POST['data'];
    $hora        = $_POST['hora'];
    $observacao  = trim($_POST['observacao'] ?? '');

    $sql = "INSERT INTO Agendamento (id_cliente, id_barbeiro, id_servico, data, hora, observacao)
            VALUES ('$id_cliente', '$id_barbeiro', '$id_servico', '$data', '$hora', '$observacao')";

    if ($conn->query($sql)) {
        echo "<script>
                alert('✅ Agendamento confirmado com sucesso!');
                window.location.href='cliente_dashboard.php';
              </script>";
        exit;
    } else {
        echo "<script>alert('❌ Erro ao agendar: {$conn->error}');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Novo Agendamento - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    body { background-color: #f8f9fa; }
    .hora-btn {
      margin: 5px;
      width: 90px;
      height: 45px;
      font-weight: 500;
      border-radius: 8px;
      transition: all 0.25s ease;
    }
    .hora-btn:hover:not(:disabled) {
      transform: scale(1.08);
      box-shadow: 0 0 8px rgba(0,0,0,0.15);
    }
    .hora-btn.btn-outline-secondary {
      background-color: #f1f1f1;
      color: #888;
      border: 1px solid #ccc;
    }
    /* Destaque visual do botão selecionado */
    .hora-btn.active {
      background-color: #0d6efd !important; /* azul Bootstrap */
      color: #fff !important;
      border: 2px solid #0a58ca !important;
      box-shadow: 0 0 10px rgba(13,110,253,0.4);
      transform: scale(1.1);
    }
  </style>
</head>
<body>

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="cliente_dashboard.php">💈 Barber La Mafia</a>
    <span class="navbar-text text-white">Olá, <?= $_SESSION['cliente_nome'] ?></span>
    <a href="logout.php" class="btn btn-outline-light ms-2">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Novo Agendamento</h2>

  <form method="POST" id="formAgendamento" class="card p-4 shadow-sm mx-auto" style="max-width: 650px;">

    <div class="mb-3">
      <label class="form-label">Barbeiro</label>
      <select name="id_barbeiro" id="barbeiro" class="form-select" required>
        <option value="">Selecione o barbeiro</option>
        <?php while($b = $barbeiros->fetch_assoc()) { ?>
          <option value="<?= $b['id_barbeiro'] ?>"><?= $b['nome'] ?> - <?= $b['especialidade'] ?></option>
        <?php } ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Serviço</label>
      <select name="id_servico" id="servico" class="form-select" required>
        <option value="">Selecione um serviço</option>
        <?php while($s = $servicos->fetch_assoc()): ?>
          <option value="<?= $s['id_servico'] ?>">
            <?= $s['descricao'] ?> - R$ <?= number_format($s['preco'], 2, ',', '.') ?> (<?= $s['duracao'] ?> min)
          </option>
        <?php endwhile; ?>
      </select>
    </div>

    <div class="mb-3">
      <label class="form-label">Data</label>
      <input type="date" name="data" id="data" class="form-control" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Horário</label>
      <div id="horariosContainer" class="d-flex flex-wrap border p-2 rounded bg-white text-center justify-content-start">
        <span class="text-muted w-100">Selecione a data primeiro</span>
      </div>
      <input type="hidden" name="hora" id="horaSelecionada" required>
    </div>

    <div class="mb-3">
      <label class="form-label">Observação (opcional)</label>
      <textarea name="observacao" id="observacao" class="form-control" rows="3"
        placeholder="Ex: Prefiro navalha no contorno, evite o topo muito curto..."></textarea>
    </div>

    <button type="button" id="btnConfirmar" class="btn btn-success w-100">Avançar para Confirmação</button>
  </form>
</div>

<!-- Modal de confirmação -->
<div class="modal fade" id="confirmacaoModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Confirmar Agendamento</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="resumoAgendamento"></div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Editar</button>
        <button type="submit" form="formAgendamento" class="btn btn-success">Confirmar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const dataInput = document.getElementById('data');
const barbeiroSelect = document.getElementById('barbeiro');
const horariosContainer = document.getElementById('horariosContainer');
const horaInput = document.getElementById('horaSelecionada');

// Recarrega horários quando mudar data ou barbeiro
dataInput.addEventListener('change', carregarHorarios);
barbeiroSelect.addEventListener('change', carregarHorarios);

function carregarHorarios() {
  const barbeiro = barbeiroSelect.value;
  const data = dataInput.value;
  if (!barbeiro || !data) return;

  horariosContainer.innerHTML = '<div class="text-muted w-100">⏳ Carregando horários...</div>';

  fetch(`horarios_dispo.php?id_barbeiro=${barbeiro}&data=${data}`)
    .then(resp => resp.ok ? resp.json() : Promise.reject('Erro HTTP'))
    .then(json => {
      horariosContainer.innerHTML = '';

      if (json.vazio || json.horarios.length === 0) {
        horariosContainer.innerHTML = '<div class="text-danger fw-bold w-100 text-center p-2 border rounded">🟥 Nenhum horário disponível neste dia</div>';
        return;
      }

      const todosHorarios = [];
      for (let h = 9; h <= 18; h++) {
        ["00", "30"].forEach(m => todosHorarios.push(`${String(h).padStart(2, '0')}:${m}`));
      }

      todosHorarios.forEach(hora => {
        const disponivel = json.horarios.includes(hora);
        const btn = document.createElement('button');
        btn.type = "button";
        btn.className = "hora-btn btn";
        btn.textContent = hora;

        if (disponivel) btn.classList.add('btn-success');
        else { btn.classList.add('btn-outline-secondary'); btn.disabled = true; }

        horariosContainer.appendChild(btn);
      });
    })
    .catch(err => {
      console.error("Erro ao carregar horários:", err);
      horariosContainer.innerHTML = '<span class="text-danger w-100">❌ Erro ao carregar horários.</span>';
    });
}

// Destaque visual ao clicar
horariosContainer.addEventListener('click', (e) => {
  const btn = e.target.closest('.hora-btn');
  if (!btn || btn.disabled) return;
  document.querySelectorAll('.hora-btn').forEach(b => b.classList.remove('active'));
  btn.classList.add('active');
  horaInput.value = btn.textContent.trim();
});

// Confirmação antes de enviar
document.getElementById('btnConfirmar').addEventListener('click', () => {
  const barbeiro = barbeiroSelect.options[barbeiroSelect.selectedIndex].text;
  const servico = document.getElementById('servico').options[document.getElementById('servico').selectedIndex].text;
  const data = dataInput.value;
  const hora = horaInput.value;
  const obs = document.getElementById('observacao').value || '(sem observação)';

  if (!barbeiro || !servico || !data || !hora) {
    alert('Por favor, selecione todos os campos antes de confirmar.');
    return;
  }

  const resumo = `
    <p><strong>Barbeiro:</strong> ${barbeiro}</p>
    <p><strong>Serviço:</strong> ${servico}</p>
    <p><strong>Data:</strong> ${new Date(data).toLocaleDateString('pt-BR')}</p>
    <p><strong>Hora:</strong> ${hora}</p>
    <p><strong>Observação:</strong> ${obs}</p>
  `;

  document.getElementById('resumoAgendamento').innerHTML = resumo;
  new bootstrap.Modal(document.getElementById('confirmacaoModal')).show();
});
</script>
</body>
</html>
