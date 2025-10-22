<?php include "header_adm.php"; ?>

<h2 class="text-center mb-4">💈 Painel do Administrador</h2>

<div class="row text-center g-3">
  <div class="col-md-3">
    <a href="listar_agendamentos_adm.php" class="btn btn-outline-dark w-100 p-4 shadow-sm">
      <i class="bi bi-calendar3 fs-2 mb-2"></i><br>Gerenciar Agendamentos
    </a>
  </div>

  <div class="col-md-3">
    <a href="listar_clientes.php" class="btn btn-outline-dark w-100 p-4 shadow-sm">
      <i class="bi bi-people fs-2 mb-2"></i><br>Gerenciar Clientes
    </a>
  </div>

  <div class="col-md-3">
    <a href="listar_servicos.php" class="btn btn-outline-dark w-100 p-4 shadow-sm">
      <i class="bi bi-scissors fs-2 mb-2"></i><br>Gerenciar Serviços
    </a>
  </div>

  <div class="col-md-3">
    <a href="gerenciar_feriados.php" class="btn btn-outline-dark w-100 p-4 shadow-sm">
      <i class="bi bi-calendar-x fs-2 mb-2"></i><br>Gerenciar Feriados
    </a>
  </div>
</div>

<hr class="my-4">

<div class="text-center">
  <a href="logout.php" class="btn btn-danger px-4">
    <i class="bi bi-box-arrow-right"></i> Sair
  </a>
</div>

<?php include "footer.php"; ?>
