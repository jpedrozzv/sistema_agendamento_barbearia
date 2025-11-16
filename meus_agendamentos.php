<?php
include_once "conexao.php";
include_once "verifica_cliente.php";
include_once "alerta.php";
include_once "header_cliente.php";

// --- CANCELAR AGENDAMENTO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'cancelar_agendamento') {
    $id = intval($_POST['__id'] ?? 0);
    $idCliente = $_SESSION['cliente_id'] ?? 0;

    if ($id <= 0 || $idCliente <= 0) {
        mostrarAlerta('danger', '❌ Agendamento inválido informado.');
    } else {
        try {
            $stmt = $conn->prepare("UPDATE Agendamento SET status='cancelado' WHERE id_agendamento = ? AND id_cliente = ?");
            $stmt->bind_param('ii', $id, $idCliente);
            $stmt->execute();
            $stmt->close();
            mostrarAlerta('success', '🗑️ Agendamento cancelado com sucesso!');
        } catch (Throwable $exception) {
            error_log('Erro ao cancelar agendamento: ' . $exception->getMessage());
            mostrarAlerta('danger', '❌ Erro ao cancelar o agendamento.');
        }
    }
}

// --- BUSCAR AGENDAMENTOS DO CLIENTE ---
$id_cliente = $_SESSION['cliente_id'];
$stmt = $conn->prepare(
    "SELECT
            a.id_agendamento,
            b.nome AS barbeiro,
            s.descricao AS servico,
            s.preco,
            a.data,
            a.hora,
            a.status,
            a.observacao
        FROM Agendamento a
        JOIN Barbeiro b ON a.id_barbeiro = b.id_barbeiro
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_cliente = ?
        ORDER BY a.data DESC, a.hora DESC"
);
$stmt->bind_param('i', $id_cliente);
$stmt->execute();
$result = $stmt->get_result();
?>

<style>
  .meus-agendamentos-table-wrapper {
    max-height: 70vh;
    overflow-y: auto;
  }

  .meus-agendamentos-table-wrapper .table thead th {
    position: sticky;
    top: 0;
    z-index: 2;
    background-color: var(--bs-table-bg);
  }

  .clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    white-space: normal;
  }

  @media (max-width: 576px) {
    .meus-agendamentos-table-wrapper {
      max-height: none;
    }
  }

  .table-action-group {
    display: flex;
    justify-content: center;
    align-items: center;
    flex-wrap: wrap;
    gap: 0.5rem;
  }

  @media (max-width: 575.98px) {
    .meus-agendamentos-table-wrapper {
      margin-left: -1rem;
      margin-right: -1rem;
      padding-left: 1rem;
      padding-right: 1rem;
    }

    .table-action-group .btn {
      flex: 1 1 48%;
    }
  }
</style>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Meus Agendamentos</h2>

  <?php if (isset($_SESSION['alerta'])) { echo $_SESSION['alerta']; unset($_SESSION['alerta']); } ?>

  <?php if ($result->num_rows > 0): ?>
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="meus-agendamentos-table-wrapper table-responsive">
          <table class="table table-striped table-hover align-middle mb-0">
            <thead class="table-light">
              <tr class="text-center text-nowrap">
                <th scope="col">Data</th>
                <th scope="col">Horário</th>
                <th scope="col" class="text-start">Serviço</th>
                <th scope="col">Profissional</th>
                <th scope="col">Situação</th>
                <th scope="col" class="text-start">Observações</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <?php while ($row = $result->fetch_assoc()): ?>
                <?php
                  $badge = match($row['status']) {
                    'pendente' => 'warning',
                    'confirmado' => 'primary',
                    'concluido' => 'success',
                    'cancelado' => 'danger',
                    default => 'secondary'
                  };

                  $statusLabel = match($row['status']) {
                    'pendente' => 'Pendente',
                    'confirmado' => 'Confirmado',
                    'concluido' => 'Concluído',
                    'cancelado' => 'Cancelado',
                    default => ucfirst($row['status'])
                  };

                  $observacaoTexto = trim($row['observacao'] ?? '');
                  $observacaoTooltip = $observacaoTexto !== '' ? htmlspecialchars($observacaoTexto) : 'Sem observações';
                ?>
                <tr>
                  <td class="text-nowrap" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= date('d/m/Y', strtotime($row['data'])) ?>">
                    <?= date('d/m/Y', strtotime($row['data'])) ?>
                  </td>
                  <td class="text-nowrap" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= date('H:i', strtotime($row['hora'])) ?>">
                    <?= date('H:i', strtotime($row['hora'])) ?>
                  </td>
                  <td class="text-start" style="max-width: 220px;">
                    <div
                      class="fw-semibold text-truncate"
                      data-bs-toggle="tooltip"
                      data-bs-placement="top"
                      title="<?= htmlspecialchars($row['servico']) ?>"
                      aria-label="Serviço: <?= htmlspecialchars($row['servico']) ?>">
                      <?= htmlspecialchars($row['servico']) ?>
                    </div>
                    <small class="text-muted">R$ <?= number_format($row['preco'], 2, ',', '.') ?></small>
                  </td>
                  <td class="text-nowrap" data-bs-toggle="tooltip" data-bs-placement="top" title="<?= htmlspecialchars($row['barbeiro']) ?>">
                    <?= htmlspecialchars($row['barbeiro']) ?>
                  </td>
                  <td>
                    <span class="badge bg-<?= $badge ?>" aria-label="Status: <?= $statusLabel ?>">
                      <?= $statusLabel ?>
                    </span>
                  </td>
                  <td class="text-start" style="max-width: 260px;">
                    <?php if ($observacaoTexto !== ''): ?>
                      <span
                        class="d-inline-block clamp-2 text-break"
                        data-bs-toggle="tooltip"
                        data-bs-placement="top"
                        title="<?= $observacaoTooltip ?>"
                        aria-label="Observações completas: <?= $observacaoTooltip ?>">
                        <?= htmlspecialchars($observacaoTexto) ?>
                      </span>
                    <?php else: ?>
                      <span class="text-muted fst-italic">Sem observações</span>
                    <?php endif; ?>
                  </td>
                  <td class="text-center text-nowrap" style="width: 140px;">
                    <div class="table-action-group">
                      <button
                        type="button"
                        class="btn btn-sm btn-outline-primary ver-detalhes-btn"
                        data-servico="<?= htmlspecialchars($row['servico']) ?>"
                        data-preco="R$ <?= number_format($row['preco'], 2, ',', '.') ?>"
                        data-profissional="<?= htmlspecialchars($row['barbeiro']) ?>"
                        data-data="<?= date('d/m/Y', strtotime($row['data'])) ?>"
                        data-hora="<?= date('H:i', strtotime($row['hora'])) ?>"
                        data-status="<?= $statusLabel ?>"
                        data-observacao="<?= $observacaoTooltip ?>"
                        data-observacao-vazia="<?= $observacaoTexto === '' ? '1' : '0' ?>"
                        title="Ver detalhes do agendamento"
                        aria-label="Ver detalhes do agendamento de <?= htmlspecialchars($row['servico']) ?>">
                        <i class="bi bi-eye"></i>
                      </button>

                      <?php if (in_array($row['status'], ['pendente', 'confirmado'], true)): ?>
                        <form id="formCancelar<?= $row['id_agendamento'] ?>" method="POST" class="d-inline">
                          <input type="hidden" name="__action" value="cancelar_agendamento">
                          <input type="hidden" name="__id" value="<?= $row['id_agendamento'] ?>">
                        </form>
                        <button
                          type="button"
                          class="btn btn-sm btn-danger"
                          data-confirm="cancelar_agendamento"
                          data-id="<?= $row['id_agendamento'] ?>"
                          data-form="formCancelar<?= $row['id_agendamento'] ?>"
                          data-text="Deseja realmente <strong>cancelar</strong> o agendamento de
                                     <strong><?= htmlspecialchars($row['servico']) ?></strong> com
                                     <strong><?= htmlspecialchars($row['barbeiro']) ?></strong> em
                                     <strong><?= date('d/m/Y', strtotime($row['data'])) ?></strong> às
                                     <strong><?= date('H:i', strtotime($row['hora'])) ?></strong>?"
                          title="Cancelar agendamento"
                          aria-label="Cancelar agendamento de <?= htmlspecialchars($row['servico']) ?>">
                          <i class="bi bi-x-circle"></i>
                        </button>
                      <?php else: ?>
                        <button
                          type="button"
                          class="btn btn-sm btn-outline-secondary"
                          disabled
                          title="Cancelamento indisponível para este status"
                          aria-disabled="true">
                          <i class="bi bi-dash-circle"></i>
                        </button>
                      <?php endif; ?>
                    </div>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum agendamento encontrado.</div>
  <?php endif; ?>

  <a href="cliente_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<div class="modal fade" id="detalhesAgendamentoModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-scrollable">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title"><i class="bi bi-eye"></i> Detalhes do Agendamento</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
      </div>
      <div class="modal-body">
        <dl class="row mb-0">
          <dt class="col-sm-4">Serviço</dt>
          <dd class="col-sm-8" id="detalhesServico"></dd>

          <dt class="col-sm-4">Profissional</dt>
          <dd class="col-sm-8" id="detalhesProfissional"></dd>

          <dt class="col-sm-4">Data</dt>
          <dd class="col-sm-8" id="detalhesData"></dd>

          <dt class="col-sm-4">Horário</dt>
          <dd class="col-sm-8" id="detalhesHora"></dd>

          <dt class="col-sm-4">Status</dt>
          <dd class="col-sm-8" id="detalhesStatus"></dd>

          <dt class="col-sm-4">Observações</dt>
          <dd class="col-sm-8 text-break" id="detalhesObservacao"></dd>
        </dl>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
  if (window.bootstrap && typeof window.bootstrap.Tooltip === 'function') {
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach((el) => {
      new bootstrap.Tooltip(el);
    });
  }

  const modalElement = document.getElementById('detalhesAgendamentoModal');
  if (!modalElement || !window.bootstrap || typeof window.bootstrap.Modal !== 'function') {
    return;
  }

  const detalhesModal = new bootstrap.Modal(modalElement);
  const servicoEl = document.getElementById('detalhesServico');
  const profissionalEl = document.getElementById('detalhesProfissional');
  const dataEl = document.getElementById('detalhesData');
  const horaEl = document.getElementById('detalhesHora');
  const statusEl = document.getElementById('detalhesStatus');
  const observacaoEl = document.getElementById('detalhesObservacao');

  document.querySelectorAll('.ver-detalhes-btn').forEach((btn) => {
    btn.addEventListener('click', () => {
      if (servicoEl) {
        const servico = btn.dataset.servico || '';
        const preco = btn.dataset.preco || '';
        servicoEl.textContent = preco ? `${servico} (${preco})` : servico;
      }

      if (profissionalEl) {
        profissionalEl.textContent = btn.dataset.profissional || '';
      }

      if (dataEl) {
        dataEl.textContent = btn.dataset.data || '';
      }

      if (horaEl) {
        horaEl.textContent = btn.dataset.hora || '';
      }

      if (statusEl) {
        statusEl.textContent = btn.dataset.status || '';
      }

      if (observacaoEl) {
        const isVazia = btn.dataset.observacaoVazia === '1';
        const textoObservacao = btn.dataset.observacao || 'Sem observações';

        observacaoEl.textContent = textoObservacao;
        observacaoEl.classList.toggle('text-muted', isVazia);
        observacaoEl.classList.toggle('fst-italic', isVazia);
      }

      detalhesModal.show();
    });
  });
});
</script>

<?php $stmt->close(); ?>
<?php include("footer.php"); ?>
