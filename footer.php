<?php // footer.php ?>
</div> <!-- .container -->

<!-- Modal de confirmação reutilizável -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content modal-confirm">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title"><i class="bi bi-exclamation-triangle"></i> Confirmar ação</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body" id="confirmModalBody">Tem certeza?</div>
      <div class="modal-footer">
        <form id="confirmForm" method="POST" class="m-0">
          <input type="hidden" name="__action" id="confirmAction">
          <input type="hidden" name="__id" id="confirmId">
          <button type="submit" class="btn btn-danger" id="confirmYesBtn">Confirmar</button>
        </form>
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
// Reutilizável: chama modal de confirmação sem 'confirm()' nativo
// Como usar no HTML: <button class="btn btn-danger"
//    data-confirm="remover_servico" data-id="123"
//    data-text="Deseja remover o serviço X?"
//    data-form="formRemoverServico">Remover</button>
(function(){
  const modalEl = document.getElementById('confirmModal');
  if (!modalEl) return;

  const modal = new bootstrap.Modal(modalEl);
  const modalBody = document.getElementById('confirmModalBody');
  const form = document.getElementById('confirmForm');
  const actionInput = document.getElementById('confirmAction');
  const idInput = document.getElementById('confirmId');
  const confirmBtn = document.getElementById('confirmYesBtn');

  let externalFormId = null;

  document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-confirm]');
    if (!button) {
      return;
    }

    event.preventDefault();

    externalFormId = button.getAttribute('data-form') || null;
    actionInput.value = button.getAttribute('data-confirm') || '';
    idInput.value = button.getAttribute('data-id') || '';
    form.method = button.getAttribute('data-method') || 'POST';
    form.action = button.getAttribute('data-action') || '';
    modalBody.innerHTML = button.getAttribute('data-text') || 'Tem certeza?';

    modal.show();
  });

  confirmBtn.addEventListener('click', () => {
    if (externalFormId) {
      const externalForm = document.getElementById(externalFormId);
      if (externalForm) {
        externalForm.submit();
        modal.hide();
        return;
      }
    }

    form.submit();
    modal.hide();
  });

  modalEl.addEventListener('hidden.bs.modal', () => {
    externalFormId = null;
  });
})();
</script>
</body>
</html>
