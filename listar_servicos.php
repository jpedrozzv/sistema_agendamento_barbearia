<?php
session_start();
include("conexao.php");
include("verifica_adm.php");
include("alerta.php");

if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}


$msg = null;

// --- ADICIONAR SERVIÇO ---
if (isset($_POST['adicionar'])) {
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao = intval($_POST['duracao']);

    if ($conn->query("INSERT INTO Servico (descricao, preco, duracao) VALUES ('$descricao','$preco','$duracao')")) {
        $msg = ['success', '✅ Serviço adicionado com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao adicionar serviço.'];
    }
}

// --- EDITAR SERVIÇO ---
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_servico']);
    $descricao = trim($_POST['descricao']);
    $preco = floatval($_POST['preco']);
    $duracao = intval($_POST['duracao']);

    if ($conn->query("UPDATE Servico SET descricao='$descricao', preco='$preco', duracao='$duracao' WHERE id_servico=$id")) {
        $msg = ['success', '✏️ Alterações salvas com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao atualizar serviço.'];
    }
}

// --- REMOVER SERVIÇO ---
if (isset($_POST['remover_confirmado'])) {
    $id = intval($_POST['id_servico']);
    if ($conn->query("DELETE FROM Servico WHERE id_servico = $id")) {
        $msg = ['success', '🗑️ Serviço removido com sucesso!'];
    } else {
        $msg = ['danger', '❌ Erro ao remover serviço.'];
    }
}

$result = $conn->query("SELECT * FROM Servico ORDER BY id_servico ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>✂️ Gerenciar Serviços - Barbearia La Mafia</title>
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
  <h2 class="text-center mb-4">✂️ Gerenciar Serviços</h2>

  <?php if ($msg) mostrarAlerta($msg[0], $msg[1]); ?>

  <!-- Formulário de novo serviço -->
  <div class="card shadow-sm p-3 mb-4">
    <h5><i class="bi bi-plus-circle"></i> Adicionar Novo Serviço</h5>
    <form method="POST" class="row g-3">
      <div class="col-md-4">
        <input type="text" name="descricao" class="form-control" placeholder="Descrição" required>
      </div>
      <div class="col-md-3">
        <input type="number" step="0.01" name="preco" class="form-control" placeholder="Preço (R$)" required>
      </div>
      <div class="col-md-3">
        <input type="number" name="duracao" class="form-control" placeholder="Duração (min)" required>
      </div>
      <div class="col-md-2">
        <button type="submit" name="adicionar" class="btn btn-success w-100">
          <i class="bi bi-check-circle"></i> Adicionar
        </button>
      </div>
    </form>
  </div>

  <!-- Tabela de serviços -->
  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm align-middle">
      <thead class="table-dark text-center">
        <tr>
          <th>ID</th>
          <th>Descrição</th>
          <th>Preço</th>
          <th>Duração</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody class="text-center">
        <?php while ($row = $result->fetch_assoc()): ?>
          <tr>
            <td><?= $row['id_servico'] ?></td>
            <td><?= htmlspecialchars($row['descricao']) ?></td>
            <td>R$ <?= number_format($row['preco'], 2, ',', '.') ?></td>
            <td><?= $row['duracao'] ?> min</td>
            <td>
              <!-- Botão editar -->
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?= $row['id_servico'] ?>">
                <i class="bi bi-pencil"></i>
              </button>

              <!-- Botão remover -->
              <button class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#removerModal<?= $row['id_servico'] ?>">
                <i class="bi bi-trash"></i>
              </button>
            </td>
          </tr>

          <!-- Modal Editar -->
          <div class="modal fade" id="editarModal<?= $row['id_servico'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title"><i class="bi bi-pencil"></i> Editar Serviço</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_servico" value="<?= $row['id_servico'] ?>">
                    <div class="mb-3">
                      <label class="form-label">Descrição</label>
                      <input type="text" name="descricao" class="form-control" value="<?= htmlspecialchars($row['descricao']) ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Preço (R$)</label>
                      <input type="number" step="0.01" name="preco" class="form-control" value="<?= $row['preco'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Duração (min)</label>
                      <input type="number" name="duracao" class="form-control" value="<?= $row['duracao'] ?>" required>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="editar" class="btn btn-success"><i class="bi bi-check-circle"></i> Salvar</button>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                  </div>
                </form>
              </div>
            </div>
          </div>

          <!-- Modal de confirmação de exclusão -->
          <div class="modal fade" id="removerModal<?= $row['id_servico'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header bg-danger text-white">
                    <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar exclusão</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    Deseja realmente remover o serviço <strong><?= htmlspecialchars($row['descricao']) ?></strong>?
                    <input type="hidden" name="id_servico" value="<?= $row['id_servico'] ?>">
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
    <div class="alert alert-warning text-center mt-3">Nenhum serviço cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
    <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
  </a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
