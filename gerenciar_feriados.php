<?php
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");
include("header_adm.php");

$msg = null;

// --- ADICIONAR FERIADO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'add_feriado') {
    $data = $_POST['data'];
    $descricao = trim($_POST['descricao']);
    if ($conn->query("INSERT INTO Feriado (data, descricao) VALUES ('$data', '$descricao')")) {
        $msg = ['success', '✅ Feriado adicionado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao adicionar feriado.'];
    }
}

// --- REMOVER FERIADO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remove_feriado') {
    $id = intval($_POST['__id']);
    if ($conn->query("DELETE FROM Feriado WHERE id_feriado = $id")) {
        $msg = ['success', '🗑️ Feriado removido com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao remover feriado.'];
    }
}

// --- BUSCAR FERIADOS ---
$feriados = $conn->query("SELECT * FROM Feriado ORDER BY data ASC");
?>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Gerenciar Feriados</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <!-- Formulário de novo feriado -->
  <div class="card shadow-sm p-3 mb-4">
    <h5><i class="bi bi-calendar-plus"></i> Adicionar Novo Feriado</h5>
    <form method="POST" class="row g-3">
      <input type="hidden" name="__action" value="add_feriado">
      <div class="col-md-4">
        <input type="date" name="data" class="form-control" required>
      </div>
      <div class="col-md-6">
        <input type="text" name="descricao" class="form-control" placeholder="Descrição do feriado" required>
      </div>
      <div class="col-md-2">
        <button type="submit" class="btn btn-success w-100">
          <i class="bi bi-check-circle"></i> Adicionar
        </button>
      </div>
    </form>
  </div>

  <!-- Tabela de feriados -->
  <?php if ($feriados->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>Data</th>
          <th>Descrição</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while($f = $feriados->fetch_assoc()): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($f['data'])) ?></td>
            <td><?= htmlspecialchars($f['descricao']) ?></td>
            <td>
              <form id="formRemove<?= $f['id_feriado'] ?>" method="POST" class="d-inline">
                <input type="hidden" name="__action" value="remove_feriado">
                <input type="hidden" name="__id" value="<?= $f['id_feriado'] ?>">
              </form>

              <button
                class="btn btn-sm btn-danger"
                data-confirm="remove_feriado"
                data-id="<?= $f['id_feriado'] ?>"
                data-text="Deseja realmente remover o feriado <strong><?= htmlspecialchars($f['descricao']) ?></strong> do dia <strong><?= date('d/m/Y', strtotime($f['data'])) ?></strong>?">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum feriado cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include("footer.php"); ?>

<script>
// Função global para modais de confirmação
document.querySelectorAll('[data-confirm]').forEach(btn => {
  btn.addEventListener('click', () => {
    const id = btn.dataset.id;
    const text = btn.dataset.text;

    const modalHTML = `
      <div class="modal fade" id="confirmModal" tabindex="-1">
        <div class="modal-dialog">
          <div class="modal-content">
            <div class="modal-header bg-danger text-white">
              <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar ação</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body text-center">
              <p>${text}</p>
              <p class="text-muted"><small>Esta ação não poderá ser desfeita.</small></p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
              <button class="btn btn-danger" id="confirmYes">Sim, remover</button>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
    const modal = new bootstrap.Modal(document.getElementById('confirmModal'));
    modal.show();

    document.getElementById('confirmYes').addEventListener('click', () => {
      document.getElementById(`formRemove${id}`).submit();
      modal.hide();
    });

    // Remove modal após fechar
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', e => e.target.remove());
  });
});
</script>
