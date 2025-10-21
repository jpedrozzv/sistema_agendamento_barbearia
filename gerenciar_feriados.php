<?php
session_start();
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");

$msg = null;


if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// --- ADICIONAR FERIADO ---
if (isset($_POST['adicionar'])) {
    $data = $_POST['data'];
    $descricao = trim($_POST['descricao']);
    if ($conn->query("INSERT INTO Feriado (data, descricao) VALUES ('$data', '$descricao')")) {
        $msg = ['success', '✅ Feriado adicionado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao adicionar feriado.'];
    }
}

// --- REMOVER FERIADO ---
if (isset($_POST['remover_confirmado'])) {
    $id = intval($_POST['id_feriado']);
    if ($conn->query("DELETE FROM Feriado WHERE id_feriado = $id")) {
        $msg = ['success', '🗑️ Feriado removido com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao remover feriado.'];
    }
}

// --- BUSCAR FERIADOS ---
$feriados = $conn->query("SELECT * FROM Feriado ORDER BY data ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>📆 Gerenciar Feriados - Barbearia La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2 class="text-center mb-4">📅 Gerenciar Feriados</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <!-- Formulário de novo feriado -->
  <div class="card shadow-sm p-3 mb-4">
    <h5><i class="bi bi-plus-circle"></i> Adicionar Novo Feriado</h5>
    <form method="POST" class="row g-3">
      <div class="col-md-4">
        <input type="date" name="data" class="form-control" required>
      </div>
      <div class="col-md-6">
        <input type="text" name="descricao" class="form-control" placeholder="Descrição do feriado" required>
      </div>
      <div class="col-md-2">
        <button type="submit" name="adicionar" class="btn btn-success w-100">
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
          <th style="width: 130px;">Ações</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while ($f = $feriados->fetch_assoc()): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($f['data'])) ?></td>
            <td><?= htmlspecialchars($f['descricao']) ?></td>
            <td>
              <button class="btn btn-sm btn-danger"
                      data-bs-toggle="modal"
                      data-bs-target="#removerModal<?= $f['id_feriado'] ?>">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>

          <!-- Modal Remover -->
          <div class="modal fade" id="removerModal<?= $f['id_feriado'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    Deseja realmente remover o feriado <strong><?= htmlspecialchars($f['descricao']) ?></strong>
                    (<em><?= date('d/m/Y', strtotime($f['data'])) ?></em>)?
                    <input type="hidden" name="id_feriado" value="<?= $f['id_feriado'] ?>">
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="remover_confirmado" class="btn btn-danger">Sim, remover</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning text-center mt-3">Nenhum feriado cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
