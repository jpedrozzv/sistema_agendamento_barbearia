<?php
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");
include("header_adm.php");

$msg = null;

// --- ADICIONAR FERIADO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'add_feriado') {
    $data = trim($_POST['data'] ?? '');
    $descricao = trim($_POST['descricao'] ?? '');
    $dataObj = DateTimeImmutable::createFromFormat('Y-m-d', $data);

    if (!$dataObj || $dataObj->format('Y-m-d') !== $data || $descricao === '') {
        $msg = ['danger', '❌ Informe uma data válida e a descrição do feriado.'];
    } else {
        try {
            $stmt = $conn->prepare('INSERT INTO Feriado (data, descricao) VALUES (?, ?)');
            $stmt->bind_param('ss', $data, $descricao);
            $stmt->execute();
            $stmt->close();

            $msg = ['success', '✅ Feriado adicionado com sucesso!'];
        } catch (Throwable $exception) {
            error_log('Erro ao inserir feriado: ' . $exception->getMessage());
            $msg = ['danger', '❌ Erro ao adicionar feriado.'];
        }
    }
}

// --- REMOVER FERIADO ---
if (isset($_POST['__action']) && $_POST['__action'] === 'remove_feriado') {
    $id = intval($_POST['__id'] ?? 0);

    if ($id <= 0) {
        $msg = ['danger', '❌ Feriado inválido informado.'];
    } else {
        try {
            $stmt = $conn->prepare('DELETE FROM Feriado WHERE id_feriado = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $stmt->close();

            $msg = ['success', '🗑️ Feriado removido com sucesso!'];
        } catch (Throwable $exception) {
            error_log('Erro ao remover feriado: ' . $exception->getMessage());
            $msg = ['danger', '❌ Erro ao remover feriado.'];
        }
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
    <div class="card shadow-sm border-0">
      <div class="card-body">
        <div class="table-responsive table-scroll">
          <table class="table table-hover align-middle mb-0 text-center">
            <thead>
              <tr>
                <th>Data</th>
                <th>Descrição</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
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
                      data-form="formRemove<?= $f['id_feriado'] ?>"
                      data-text="Deseja realmente remover o feriado <strong><?= htmlspecialchars($f['descricao']) ?></strong> do dia <strong><?= date('d/m/Y', strtotime($f['data'])) ?></strong>?">
                      <i class="bi bi-trash"></i>
                    </button>
                  </td>
                </tr>
              <?php endwhile; ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="alert alert-warning text-center">Nenhum feriado cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<?php include("footer.php"); ?>
