<?php
require_once __DIR__ . '/src/horarios.php';
include("conexao.php");
include("verifica_cliente.php");
include_once("alerta.php");
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
            <option
              value="<?= $s['id_servico'] ?>"
              data-duracao="<?= (int) $s['duracao'] ?>"
            >
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
        <select name="hora" class="form-select" required aria-label="Selecione um horário" disabled>
          <option value="" selected disabled>Selecione um horário</option>
        </select>
        <div id="horarios-status" class="form-text text-muted" aria-live="polite" role="status"></div>
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
    const barberSelect = form ? form.querySelector('select[name="barbeiro"]') : null;
    const serviceSelect = form ? form.querySelector('select[name="servico"]') : null;
    const timeSelect = form ? form.querySelector('select[name="hora"]') : null;
    const statusElement = document.getElementById('horarios-status');
    const sundayMessage = '🚫 Não é possível agendar aos domingos.';
    const defaultStatusMessage = 'Selecione barbeiro, serviço e data para ver horários disponíveis.';

    if (!alertPlaceholder || !form || !dateInput || !barberSelect || !serviceSelect || !timeSelect || !statusElement) {
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

    const setStatus = (message, tone = 'muted') => {
      statusElement.textContent = message || '';
      statusElement.classList.remove('text-danger', 'text-muted');
      statusElement.classList.add(tone === 'danger' ? 'text-danger' : 'text-muted');
    };

    const setSelectPlaceholder = (label = 'Selecione um horário') => {
      timeSelect.innerHTML = '';
      const placeholder = document.createElement('option');
      placeholder.value = '';
      placeholder.textContent = label;
      placeholder.disabled = true;
      placeholder.selected = true;
      timeSelect.appendChild(placeholder);
    };

    const resetSelect = (
      message = defaultStatusMessage,
      tone = 'muted',
      placeholderLabel = 'Selecione um horário',
    ) => {
      setSelectPlaceholder(placeholderLabel);
      timeSelect.disabled = true;
      timeSelect.setAttribute('aria-busy', 'false');
      setStatus(message, tone);
    };

    const setLoading = () => {
      timeSelect.disabled = true;
      timeSelect.setAttribute('aria-busy', 'true');
      setSelectPlaceholder('Buscando horários…');
      setStatus('Buscando horários…');
    };

    const isSunday = (value) => {
      if (!value) {
        return false;
      }

      const date = new Date(`${value}T00:00`);

      return !Number.isNaN(date.getTime()) && date.getDay() === 0;
    };

    const minutesFromSlot = (slot) => {
      const [hours, minutes] = slot.split(':').map(Number);
      if (Number.isNaN(hours) || Number.isNaN(minutes)) {
        return null;
      }

      return hours * 60 + minutes;
    };

    const slotFromMinutes = (minutes) => {
      const h = String(Math.floor(minutes / 60)).padStart(2, '0');
      const m = String(minutes % 60).padStart(2, '0');
      return `${h}:${m}`;
    };

    const filterByDuration = (slots, stepsNeeded) => {
      if (stepsNeeded <= 1) {
        return slots.slice();
      }

      const slotSet = new Set(slots);

      return slots.filter((slot) => {
        const startMinutes = minutesFromSlot(slot);
        if (startMinutes === null) {
          return false;
        }

        for (let step = 1; step < stepsNeeded; step += 1) {
          const candidate = slotFromMinutes(startMinutes + step * 30);
          if (!slotSet.has(candidate)) {
            return false;
          }
        }

        return true;
      });
    };

    const fetchAvailability = async () => {
      const barberId = barberSelect.value;
      const dateValue = dateInput.value;
      const selectedService = serviceSelect.selectedOptions[0];

      if (!barberId || !dateValue || !selectedService) {
        resetSelect();
        return;
      }

      if (isSunday(dateValue)) {
        resetSelect(sundayMessage, 'danger');
        showAlert();
        return;
      }

      clearAlert();

      const duracao = Math.max(30, parseInt(selectedService.dataset.duracao || '30', 10));
      const stepsNeeded = Math.max(1, Math.ceil(duracao / 30));

      setLoading();

      try {
        const params = new URLSearchParams({
          id_barbeiro: barberId,
          data: dateValue,
        });

        const response = await fetch(`horarios_dispo.php?${params.toString()}`, {
          headers: {
            'Accept': 'application/json',
          },
        });

        if (!response.ok) {
          throw new Error(`HTTP ${response.status}`);
        }

        const payload = await response.json();
        const horarios = Array.isArray(payload.horarios) ? payload.horarios : [];

        if (payload.erro) {
          resetSelect(payload.erro, 'danger', 'Horário indisponível');
          return;
        }

        const filtrados = filterByDuration(horarios, stepsNeeded);

        if (filtrados.length === 0) {
          resetSelect('Sem horários neste dia.', 'muted', 'Sem horários neste dia');
          return;
        }

        timeSelect.innerHTML = '';
        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = 'Selecione um horário';
        placeholder.disabled = true;
        placeholder.selected = true;
        timeSelect.appendChild(placeholder);

        filtrados.forEach((slot) => {
          const option = document.createElement('option');
          option.value = slot;
          option.textContent = slot;
          timeSelect.appendChild(option);
        });

        timeSelect.disabled = false;
        timeSelect.setAttribute('aria-busy', 'false');
        setStatus('Selecione um horário disponível.');
      } catch (error) {
        console.error('Erro ao buscar horários disponíveis:', error);
        resetSelect('Erro ao carregar horários. Tente novamente.', 'danger', 'Erro ao carregar horários');
      }
    };

    const handleSundayState = () => {
      if (isSunday(dateInput.value)) {
        showAlert();
        resetSelect(sundayMessage, 'danger');
        return true;
      }

      clearAlert();
      return false;
    };

    dateInput.addEventListener('change', () => {
      const isDomingo = handleSundayState();
      if (!isDomingo) {
        fetchAvailability();
      }
    });

    barberSelect.addEventListener('change', () => {
      if (!handleSundayState()) {
        fetchAvailability();
      }
    });

    serviceSelect.addEventListener('change', () => {
      if (!handleSundayState()) {
        fetchAvailability();
      }
    });

    form.addEventListener('submit', (event) => {
      if (isSunday(dateInput.value)) {
        event.preventDefault();
        showAlert();
        dateInput.focus();
      }
    });

    resetSelect();
    if (!handleSundayState()) {
      fetchAvailability();
    }
  })();
</script>

<?php include("footer.php"); ?>
