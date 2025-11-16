<?php include "header_cliente.php"; ?>

<div class="dashboard-section">
  <h2 class="text-center mb-2">👋 Olá, <?= htmlspecialchars($_SESSION['cliente_nome'] ?? 'Cliente'); ?></h2>
  <p class="section-lead text-center">Agende novos horários, acompanhe seus atendimentos e mantenha seu cadastro atualizado.</p>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <div class="col d-flex">
      <div class="dashboard-card card-accent-blue shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar-plus"></i>
          </div>
          <h5 class="card-title mb-3">Novo Agendamento</h5>
          <p class="text-muted mb-4">Escolha profissional, serviço e horário ideais para você.</p>
          <a href="agendamento.php" class="btn btn-primary mt-auto">Agendar agora</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="dashboard-card card-accent-green shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-list-check"></i>
          </div>
          <h5 class="card-title mb-3">Meus Agendamentos</h5>
          <p class="text-muted mb-4">Visualize detalhes, acompanhe status e cancele quando precisar.</p>
          <a href="meus_agendamentos.php" class="btn btn-primary mt-auto">Ver agendamentos</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="dashboard-card card-accent-amber shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <h5 class="card-title mb-3">Atualizar Cadastro</h5>
          <p class="text-muted mb-4">Mantenha suas informações em dia para receber avisos.</p>
          <a href="cadastro_cliente.php" class="btn btn-primary mt-auto">Editar dados</a>
        </div>
      </div>
    </div>
  </div>

  <hr class="hr-soft">

  <div class="text-center">
    <a href="logout.php" class="btn btn-danger px-4">
      <i class="bi bi-box-arrow-right"></i> Encerrar sessão
    </a>
  </div>
</div>

<?php include "footer.php"; ?>
