<?php include "header_adm.php"; ?>

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

  .card-schedule {
    background: #f7f3ff;
    border-color: #cdbdf2;
  }

  .card-schedule .dashboard-icon {
    background: #e3d9ff;
    color: #5a3f9b;
  }

  .card-clients {
    background: #f4fbf7;
    border-color: #b7e2c9;
  }

  .card-clients .dashboard-icon {
    background: #d8f1e2;
    color: #2f7a53;
  }

  .card-services {
    background: #fff6f2;
    border-color: #f3c9b9;
  }

  .card-services .dashboard-icon {
    background: #ffe1d6;
    color: #c0572b;
  }

  .card-holidays {
    background: #f2f8ff;
    border-color: #bcd6f7;
  }

  .card-holidays .dashboard-icon {
    background: #d9e9ff;
    color: #1f5d9d;
  }
</style>

<div class="dashboard-section">
  <h2 class="text-center mb-4">💈 Painel do Administrador</h2>

  <div class="row row-cols-1 row-cols-md-2 g-4">
    <div class="col d-flex">
      <div class="card dashboard-card card-schedule shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar3"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Gerenciar Agendamentos</h5>
          <p class="text-muted mb-4">Visualize, confirme ou cancele agendamentos em andamento.</p>
          <a href="listar_agendamentos_adm.php" class="btn btn-outline-dark mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="card dashboard-card card-clients shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-people"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Gerenciar Clientes</h5>
          <p class="text-muted mb-4">Administre informações, cadastros e acesso dos clientes.</p>
          <a href="listar_clientes.php" class="btn btn-outline-dark mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="card dashboard-card card-services shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-scissors"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Gerenciar Serviços</h5>
          <p class="text-muted mb-4">Atualize valores, durações e disponibilidade de serviços.</p>
          <a href="listar_servicos.php" class="btn btn-outline-dark mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="card dashboard-card card-holidays shadow-sm w-100">
        <div class="card-body d-flex flex-column text-center">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar-x"></i>
          </div>
          <h5 class="card-title fw-semibold mb-3">Gerenciar Feriados</h5>
          <p class="text-muted mb-4">Defina feriados e bloqueios para manter o calendário organizado.</p>
          <a href="gerenciar_feriados.php" class="btn btn-outline-dark mt-auto">Acessar</a>
        </div>
      </div>
    </div>
  </div>

  <hr class="my-5">

  <div class="text-center">
    <a href="logout.php" class="btn btn-danger px-4">
      <i class="bi bi-box-arrow-right"></i> Sair
    </a>
  </div>
</div>

<?php include "footer.php"; ?>
