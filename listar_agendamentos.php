<?php
include("conexao.php");
session_start();

// Só admin
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}

// Remover agendamento
if (isset($_GET['remover'])) {
    $id = intval($_GET['remover']);
    if ($conn->query("DELETE FROM Agendamento WHERE id_agendamento = $id")) {
        header("Location: listar_agendamentos.php?msg=remove_ok");
        exit;
    } else {
        header("Location: listar_agendamentos.php?msg=remove_erro");
        exit;
    }
}

// Editar agendamento
if (isset($_POST['editar'])) {
    $id = intval($_POST['id_agendamento']);
    $data = $_POST['data'];
    $hora = $_POST['hora'];
    $status = $_POST['status'];

    $sql = "UPDATE Agendamento SET data='$data', hora='$hora', status='$status' WHERE id_agendamento=$id";
    if ($conn->query($sql)) {
        header("Location: listar_agendamentos.php?msg=edit_ok");
        exit;
    } else {
        header("Location: listar_agendamentos.php?msg=edit_erro");
        exit;
    }
}

// Buscar agendamentos
$sql = "SELECT 
            a.id_agendamento,
            c.nome AS cliente,
            b.nome AS barbeiro,
            s.descricao AS servico,
            s.preco,
            s.duracao,
            a.data,
            a.hora,
            a.status
        FROM Agendamento a
        JOIN Cliente c ON a.id_cliente = c.id_cliente
        JOIN Barbeiro b ON a.id_barbeiro = b.id_barbeiro
        JOIN Servico s ON a.id_servico = s.id_servico
        ORDER BY a.data DESC, a.hora DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Listar Agendamentos</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<nav class="navbar navbar-dark bg-dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="admin_dashboard.php">💈 Barbearia</a>
    <a href="logout.php" class="btn btn-outline-light">Sair</a>
  </div>
</nav>

<div class="container mt-4">
  <h2>📅 Lista de Agendamentos</h2>

  <!-- Mensagens -->
  <?php
  if (isset($_GET['msg'])) {
      switch ($_GET['msg']) {
          case 'edit_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3">✅ Agendamento atualizado com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
          case 'remove_ok':
              echo '<div class="alert alert-success alert-dismissible fade show mt-3">🗑️ Agendamento removido com sucesso!<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
          case 'edit_erro':
          case 'remove_erro':
              echo '<div class="alert alert-danger alert-dismissible fade show mt-3">❌ Erro ao processar ação. Tente novamente.<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
              break;
      }
  }
  ?>

  <!-- Tabela -->
  <?php if ($result->num_rows > 0): ?>
    <table class="table table-bordered table-hover shadow-sm mt-3">
      <thead class="table-dark">
        <tr>
          <th>ID</th>
          <th>Cliente</th>
          <th>Barbeiro</th>
          <th>Serviço</th>
          <th>Data</th>
          <th>Hora</th>
          <th>Status</th>
          <th>Ações</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($row = $result->fetch_assoc()): 
          $badge = match($row['status']) {
            "pendente" => "warning",
            "confirmado" => "primary",
            "concluido" => "success",
            "cancelado" => "danger",
            default => "secondary"
          };
        ?>
          <tr>
            <td><?= $row['id_agendamento'] ?></td>
            <td><?= $row['cliente'] ?></td>
            <td><?= $row['barbeiro'] ?></td>
            <td><?= $row['servico'] ?> (R$ <?= number_format($row['preco'], 2, ',', '.') ?> - <?= $row['duracao'] ?> min)</td>
            <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
            <td><?= date('H:i', strtotime($row['hora'])) ?></td>
            <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
            <td class="text-center">
              <button class="btn btn-sm btn-warning" data-bs-toggle="modal" data-bs-target="#editarModal<?= $row['id_agendamento'] ?>"><i class="bi bi-pencil"></i></button>
              <a href="?remover=<?= $row['id_agendamento'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Deseja remover este agendamento?')"><i class="bi bi-trash"></i></a>
            </td>
          </tr>

          <!-- Modal editar -->
          <div class="modal fade" id="editarModal<?= $row['id_agendamento'] ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header">
                    <h5 class="modal-title">Editar Agendamento</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="id_agendamento" value="<?= $row['id_agendamento'] ?>">
                    <div class="mb-3">
                      <label class="form-label">Data</label>
                      <input type="date" name="data" class="form-control" value="<?= $row['data'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Hora</label>
                      <input type="time" name="hora" class="form-control" value="<?= $row['hora'] ?>" required>
                    </div>
                    <div class="mb-3">
                      <label class="form-label">Status</label>
                      <select name="status" class="form-select">
                        <option value="pendente" <?= $row['status']=="pendente"?"selected":"" ?>>Pendente</option>
                        <option value="confirmado" <?= $row['status']=="confirmado"?"selected":"" ?>>Confirmado</option>
                        <option value="concluido" <?= $row['status']=="concluido"?"selected":"" ?>>Concluído</option>
                        <option value="cancelado" <?= $row['status']=="cancelado"?"selected":"" ?>>Cancelado</option>
                      </select>
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" name="editar" class="btn btn-success">Salvar</button>
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
    <div class="alert alert-warning mt-3">Nenhum agendamento registrado.</div>
  <?php endif; ?>

  <a href="admin_dashboard.php" class="btn btn-secondary mt-3">Voltar</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
