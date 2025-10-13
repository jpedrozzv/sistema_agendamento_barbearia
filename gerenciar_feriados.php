<?php
session_start();
include("conexao.php");
include("verifica_adm.php");



// Adicionar feriado
if (isset($_POST['adicionar'])) {
    $data = $_POST['data'];
    $descricao = $_POST['descricao'];
    $conn->query("INSERT INTO Feriado (data, descricao) VALUES ('$data', '$descricao')");
    header("Location: gerenciar_feriados.php?msg=ok");
    exit;
}

// Remover feriado
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);
    $conn->query("DELETE FROM Feriado WHERE id_feriado = $id");
    header("Location: gerenciar_feriados.php?msg=remove");
    exit;
}

// Buscar feriados
$feriados = $conn->query("SELECT * FROM Feriado ORDER BY data ASC");
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Gerenciar Feriados - Barber La Mafia</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barber La Mafia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>📅 Gerenciar Feriados</h2>

  <?php if (isset($_GET['msg']) && $_GET['msg'] == 'ok'): ?>
    <div class="alert alert-success">✅ Feriado adicionado com sucesso!</div>
  <?php elseif (isset($_GET['msg']) && $_GET['msg'] == 'remove'): ?>
    <div class="alert alert-info">🗑️ Feriado removido com sucesso!</div>
  <?php endif; ?>

  <form method="POST" class="card p-3 shadow-sm mb-4">
    <div class="row">
      <div class="col-md-4">
        <input type="date" name="data" class="form-control" required>
      </div>
      <div class="col-md-6">
        <input type="text" name="descricao" class="form-control" placeholder="Descrição do feriado" required>
      </div>
      <div class="col-md-2">
        <button type="submit" name="adicionar" class="btn btn-success w-100">Adicionar</button>
      </div>
    </div>
  </form>

  <?php if ($feriados->num_rows > 0): ?>
    <table class="table table-bordered table-striped shadow-sm">
      <thead class="table-dark">
        <tr>
          <th>Data</th>
          <th>Descrição</th>
          <th style="width: 100px;">Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while($f = $feriados->fetch_assoc()): ?>
          <tr>
            <td><?= date('d/m/Y', strtotime($f['data'])) ?></td>
            <td><?= htmlspecialchars($f['descricao']) ?></td>
            <td class="text-center">
              <a href="?remover=<?= $f['id_feriado'] ?>" 
                  class="btn btn-danger btn-sm"
                  onclick="return confirm('Remover este feriado?')">
                  Remover
              </a>
            </td>
          </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
  <?php else: ?>
    <div class="alert alert-warning">Nenhum feriado cadastrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">
  <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
</a>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
