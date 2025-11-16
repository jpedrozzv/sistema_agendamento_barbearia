<?php
include("header_adm.php");

$hoje = (new DateTimeImmutable('today'))->format('Y-m-d');
$stmt = null;

try {
    $stmt = $conn->prepare(
        "SELECT
            a.id_agendamento,
            c.nome AS cliente,
            b.nome AS barbeiro,
            s.descricao AS servico,
            a.data,
            a.hora,
            a.status
        FROM Agendamento a
        JOIN Cliente c ON a.id_cliente = c.id_cliente
        JOIN Barbeiro b ON a.id_barbeiro = b.id_barbeiro
        JOIN Servico s ON a.id_servico = s.id_servico
        ORDER BY a.data ASC, a.hora ASC"
    );
    $stmt->execute();
    $agendamentos = $stmt->get_result();
} catch (Throwable $exception) {
    error_log('Erro ao listar agendamentos: ' . $exception->getMessage());
    $agendamentos = false;
}
?>

<h2 class="text-center mb-4">📆 Todos os Agendamentos</h2>

<?php if ($agendamentos && $agendamentos->num_rows > 0): ?>
  <div class="card shadow-sm border-0">
    <div class="card-body">
      <div class="table-responsive table-scroll">
        <table class="table table-striped align-middle mb-0">
          <thead>
            <tr class="text-center">
              <th>#</th>
              <th>Cliente</th>
              <th>Barbeiro</th>
              <th>Serviço</th>
              <th>Data</th>
              <th>Hora</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody class="text-center">
            <?php while ($row = $agendamentos->fetch_assoc()): ?>
              <?php
                $badge = match($row['status']) {
                  'pendente' => 'warning',
                  'confirmado' => 'primary',
                  'concluido' => 'success',
                  'cancelado' => 'danger',
                  default => 'secondary'
                };
                $isHoje = $row['data'] === $hoje;
              ?>
              <tr class="<?= $isHoje ? 'table-info' : '' ?>">
                <td><?= $row['id_agendamento'] ?></td>
                <td><?= htmlspecialchars($row['cliente']) ?></td>
                <td><?= htmlspecialchars($row['barbeiro']) ?></td>
                <td><?= htmlspecialchars($row['servico']) ?></td>
                <td><?= date('d/m/Y', strtotime($row['data'])) ?></td>
                <td><?= date('H:i', strtotime($row['hora'])) ?></td>
                <td><span class="badge bg-<?= $badge ?>"><?= ucfirst($row['status']) ?></span></td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
<?php elseif ($agendamentos === false): ?>
  <div class="alert alert-danger text-center">Não foi possível carregar os agendamentos no momento.</div>
<?php else: ?>
  <div class="alert alert-warning text-center">Nenhum agendamento encontrado.</div>
<?php endif; ?>

<a href="admin_dashboard.php" class="btn btn-secondary mt-3">
  <i class="bi bi-arrow-left-circle"></i> Voltar ao Painel
</a>

<?php if ($stmt instanceof mysqli_stmt) { $stmt->close(); } ?>
<?php include("footer.php"); ?>
