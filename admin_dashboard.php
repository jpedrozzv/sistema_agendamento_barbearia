<?php include "header_adm.php"; ?>

<div class="dashboard-section">
  <h2 class="text-center mb-2">💈 Painel do Administrador</h2>
  <p class="section-lead text-center">Todos os atalhos essenciais em um único painel para acelerar a rotina da barbearia.</p>

  <div class="row row-cols-1 row-cols-md-2 g-4">
    <div class="col d-flex">
      <div class="dashboard-card card-accent-blue shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar3"></i>
          </div>
          <h5 class="card-title mb-3">Gerenciar Agendamentos</h5>
          <p class="text-muted mb-4">Visualize, confirme ou cancele agendamentos em andamento.</p>
          <a href="listar_agendamentos_adm.php" class="btn btn-primary mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="dashboard-card card-accent-green shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-people"></i>
          </div>
          <h5 class="card-title mb-3">Gerenciar Clientes</h5>
          <p class="text-muted mb-4">Administre informações, cadastros e acesso dos clientes.</p>
          <a href="listar_clientes.php" class="btn btn-primary mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="dashboard-card card-accent-amber shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-scissors"></i>
          </div>
          <h5 class="card-title mb-3">Gerenciar Serviços</h5>
          <p class="text-muted mb-4">Atualize valores, durações e disponibilidade de serviços.</p>
          <a href="listar_servicos.php" class="btn btn-primary mt-auto">Acessar</a>
        </div>
      </div>
    </div>

    <div class="col d-flex">
      <div class="dashboard-card card-accent-red shadow-sm w-100">
        <div class="d-flex flex-column h-100">
          <div class="dashboard-icon mx-auto">
            <i class="bi bi-calendar-x"></i>
          </div>
          <h5 class="card-title mb-3">Gerenciar Feriados</h5>
          <p class="text-muted mb-4">Defina bloqueios e mantenha a agenda organizada.</p>
          <a href="gerenciar_feriados.php" class="btn btn-primary mt-auto">Acessar</a>
        </div>
      </div>
    </div>
  </div>

  <hr class="hr-soft">

  <div class="text-center">
    <a href="logout.php" class="btn btn-danger px-4">
      <i class="bi bi-box-arrow-right"></i> Sair
    </a>
  </div>
</div>

<?php include "footer.php"; ?>
