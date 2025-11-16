<?php include "header_cliente.php"; ?>

<style>
  .dashboard-section {
    max-width: 1100px;
    margin: 0 auto;
  }

  .dashboard-card {
    border-width: 2px;
    border-radius: 1rem;
    padding: 1.5rem;
    min-height: 220px;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
  }

  .dashboard-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 1.25rem 2.5rem rgba(0, 0, 0, 0.08);
  }

  .dashboard-icon {
    width: 72px;
    height: 72px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-bottom: 1rem;
    font-size: 2.5rem;
  }

  .card-booking {
    background: #f7f3ff;
    border-color: #cdbdf2;
  }

  .card-booking .dashboard-icon {
    background: #e3d9ff;
    color: #5a3f9b;
  }

  .card-appointments {
    background: #f4fbf7;
    border-color: #b7e2c9;
  }

  .card-appointments .dashboard-icon {
    background: #d8f1e2;
    color: #2f7a53;
  }

  .card-profile {
    background: #fff6f2;
    border-color: #f3c9b9;
  }

  .card-profile .dashboard-icon {
    background: #ffe1d6;
    color: #c0572b;
  }

  @media (max-width: 767.98px) {
    .dashboard-section { padding: 0 0.25rem; }
    .dashboard-card { min-height: auto; padding: 1.25rem; }
    .dashboard-icon {
      width: 60px;
      height: 60px;
      font-size: 2rem;
      margin-bottom: 0.75rem;
    }
  }

  @media (max-width: 575.98px) {
    .dashboard-card { border-radius: 0.9rem; }
  }
</style>

<div class="dashboard-section">
  <h2 class="text-center mb-3">👋 Olá, <?= htmlspecialchars($_SESSION['cliente_nome'] ?? 'Cliente'); ?></h2>
  <p class="text-center text-muted mb-5">Bem-vindo ao seu painel. Agende novos horários, acompanhe seus atendimentos e mantenha seus dados atualizados.</p>

  <div class="row row-cols-1 row-cols-md-3 g-4">
    <div class="col d-flex">
      <div class="card dashboard-card card-booking shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar-plus"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Novo Agendamento</h5>
          <p class="text-muted mb-4">Escolha o profissional, serviço e horário ideal para você.</p>
          <a href="agendamento.php" class="btn btn-outline-dark mt-auto">Agendar agora</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="card dashboard-card card-appointments shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-list-check"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Meus Agendamentos</h5>
          <p class="text-muted mb-4">Visualize detalhes, acompanhe status e cancele quando precisar.</p>
          <a href="meus_agendamentos.php" class="btn btn-outline-dark mt-auto">Ver agendamentos</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="card dashboard-card card-profile shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-person-lines-fill"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Atualizar Cadastro</h5>
          <p class="text-muted mb-4">Mantenha suas informações em dia para receber nossos avisos.</p>
          <a href="cadastro_cliente.php" class="btn btn-outline-dark mt-auto">Editar dados</a>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-5">

  <div class="text-center">
    <a href="logout.php" class="btn btn-danger px-4">
      <i class="bi bi-box-arrow-right"></i> Encerrar sessão
    </a>
  </div>
</div>

<?php include "footer.php"; ?>
