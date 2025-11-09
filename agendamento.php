<?php
require_once __DIR__ . '/src/horarios.php';
include("conexao.php");
include("verifica_cliente.php");
include("alerta.php");
include("header_cliente.php");

$id_cliente = $_SESSION['cliente_id'] ?? 0;
$msg = null;
$mensagemDomingo = '🚫 Não é possível agendar aos domingos.';

// --- AGENDAR SERVIÇO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'novo_agendamento') {
    $id_barbeiro = intval($_POST['barbeiro'] ?? 0);
    $id_servico = intval($_POST['servico'] ?? 0);
    $data = trim($_POST['data'] ?? '');
    $hora = trim($_POST['hora'] ?? '');
    $observacao = trim($_POST['observacao'] ?? '');

    $dataObj = horarios_dispo_validar_data($data);

    if (!$id_barbeiro || !$id_servico) {
        mostrarAlerta('danger', '❌ É necessário selecionar um barbeiro e um serviço.');
    } elseif (!$dataObj) {
        mostrarAlerta('danger', '❌ Data inválida informada.');
    } elseif (!preg_match('/^\d{2}:\d{2}$/', $hora)) {
        mostrarAlerta('danger', '❌ Hora inválida informada.');
    } elseif ((int)$dataObj->format('w') === 0) {
        mostrarAlerta('danger', $mensagemDomingo);
    } else {
        try {
            $disponibilidade = horarios_dispo_calcular($conn, $id_barbeiro, $dataObj->format('Y-m-d'));
            if ($disponibilidade['vazio'] || !in_array($hora, $disponibilidade['horarios'], true)) {
                mostrarAlerta('danger', '❌ O horário selecionado não está mais disponível.');
            } else {
                $stmt = $conn->prepare(
                    "INSERT INTO Agendamento (id_cliente, id_barbeiro, id_servico, data, hora, observacao, status)
                     VALUES (?, ?, ?, ?, ?, ?, 'pendente')"
                );

                $observacaoDb = $observacao !== '' ? $observacao : null;
                $dataDb = $dataObj->format('Y-m-d');
                $stmt->bind_param('iiisss', $id_cliente, $id_barbeiro, $id_servico, $dataDb, $hora, $observacaoDb);
                $stmt->execute();
                $stmt->close();

                mostrarAlerta('success', '✅ Agendamento realizado com sucesso! Aguarde a confirmação do barbeiro.');
            }
        } catch (Throwable $exception) {
            error_log('Erro ao criar agendamento: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao realizar o agendamento.');
        }
    }
}

// --- BUSCAR DADOS ---
$barbeiros = $conn->query("SELECT id_barbeiro, nome FROM Barbeiro ORDER BY nome ASC");
$servicos = $conn->query("SELECT id_servico, descricao, preco, duracao FROM Servico ORDER BY descricao ASC");
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">💈 Novo Agendamento</h2>

  <?php if (isset($_SESSION['alerta'])) { echo $_SESSION['alerta']; unset($_SESSION['alerta']); } ?>
  <div id="js-alert-placeholder"></div>

  <form method="POST" class="card p-4 shadow-sm border-0">
    <input type="hidden" name="__action" value="novo_agendamento">

    <div class="row g-3">
      <div class="col-md-6">
        <label class="form-label"><i class="bi bi-person-badge"></i> Barbeiro</label>
        <select name="barbeiro" class="form-select" required>
          <option value="">Selecione</option>
          <?php while ($b = $barbeiros->fetch_assoc()): ?>
            <option value="<?= $b['id_barbeiro'] ?>"><?= htmlspecialchars($b['nome']) ?></option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-6">
        <label class="form-label"><i class="bi bi-scissors"></i> Serviço</label>
        <select name="servico" class="form-select" required>
          <option value="">Selecione</option>
          <?php while ($s = $servicos->fetch_assoc()): ?>
            <option value="<?= $s['id_servico'] ?>">
              <?= htmlspecialchars($s['descricao']) ?> - 
              R$ <?= number_format($s['preco'], 2, ',', '.') ?> (<?= $s['duracao'] ?> min)
            </option>
          <?php endwhile; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label"><i class="bi bi-calendar-event"></i> Data</label>
        <input type="date" name="data" class="form-control" required min="<?= date('Y-m-d') ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label"><i class="bi bi-clock"></i> Hora</label>
        <input type="time" name="hora" class="form-control" required>
      </div>

      <div class="col-md-4">
        <label class="form-label"><i class="bi bi-chat-left-text"></i> Observação</label>
        <input type="text" name="observacao" class="form-control" placeholder="Opcional">
      </div>
    </div>

    <div class="text-center mt-4">
      <button type="submit" class="btn btn-success px-4">
        <i class="bi bi-check-circle"></i> Confirmar Agendamento
      </button>
      <a href="cliente_dashboard.php" class="btn btn-secondary ms-2">
        <i class="bi bi-arrow-left-circle"></i> Voltar
      </a>
    </div>
  </form>

  <div class="alert alert-info mt-4 text-center">
    <i class="bi bi-info-circle"></i> Após o envio, o barbeiro confirmará o horário.
  </div>
</div>

<script>
  (function() {
    const alertPlaceholder = document.getElementById('js-alert-placeholder');
    const form = document.querySelector('form[method="POST"]');
    const dateInput = form ? form.querySelector('input[name="data"]') : null;
    const sundayMessage = '🚫 Não é possível agendar aos domingos.';

    if (!alertPlaceholder || !form || !dateInput) {
      return;
    }

    const buildAlert = () => `
      <div class="alert alert-danger alert-dismissible fade show mt-3" role="alert">
        ${sundayMessage}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    `;

    const showAlert = () => {
      alertPlaceholder.innerHTML = buildAlert();
    };

    const clearAlert = () => {
      alertPlaceholder.innerHTML = '';
    };

    const isSunday = (value) => {
      if (!value) {
        return false;
      }

      const date = new Date(`${value}T00:00`);

      return !Number.isNaN(date.getTime()) && date.getDay() === 0;
    };

    const handleSundayState = () => {
      if (isSunday(dateInput.value)) {
        showAlert();
      } else {
        clearAlert();
      }
    };

    dateInput.addEventListener('change', handleSundayState);

    form.addEventListener('submit', (event) => {
      if (isSunday(dateInput.value)) {
        event.preventDefault();
        showAlert();
        dateInput.focus();
      }
    });

    handleSundayState();
  })();
</script>

<?php include("footer.php"); ?>
