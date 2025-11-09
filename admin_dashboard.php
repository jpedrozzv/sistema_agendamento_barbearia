<?php include "header_adm.php"; ?>

<h2 class="text-center mb-4">💈 Painel do Administrador</h2>

<div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
  <div class="col">
    <div class="card h-100 text-center shadow-sm">
      <div class="card-body d-flex flex-column align-items-center">
        <i class="bi bi-calendar3 display-6 mb-3 text-secondary"></i>
        <h5 class="card-title">Gerenciar Agendamentos</h5>
        <a href="listar_agendamentos_adm.php" class="btn btn-outline-dark mt-auto w-100">Acessar</a>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100 text-center shadow-sm">
      <div class="card-body d-flex flex-column align-items-center">
        <i class="bi bi-people display-6 mb-3 text-secondary"></i>
        <h5 class="card-title">Gerenciar Clientes</h5>
        <a href="listar_clientes.php" class="btn btn-outline-dark mt-auto w-100">Acessar</a>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100 text-center shadow-sm">
      <div class="card-body d-flex flex-column align-items-center">
        <i class="bi bi-scissors display-6 mb-3 text-secondary"></i>
        <h5 class="card-title">Gerenciar Serviços</h5>
        <a href="listar_servicos.php" class="btn btn-outline-dark mt-auto w-100">Acessar</a>
      </div>
    </div>
  </div>

  <div class="col">
    <div class="card h-100 text-center shadow-sm">
      <div class="card-body d-flex flex-column align-items-center">
        <i class="bi bi-calendar-x display-6 mb-3 text-secondary"></i>
        <h5 class="card-title">Gerenciar Feriados</h5>
        <a href="gerenciar_feriados.php" class="btn btn-outline-dark mt-auto w-100">Acessar</a>
      </div>
    </div>
  </div>
</div>

<hr class="my-4">

<div class="text-center">
  <a href="logout.php" class="btn btn-danger px-4">
    <i class="bi bi-box-arrow-right"></i> Sair
  </a>
</div>

<?php include "footer.php"; ?>
