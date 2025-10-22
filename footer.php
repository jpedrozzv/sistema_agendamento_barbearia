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

  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-confirm]');
    if (!btn) return;

    const action = btn.getAttribute('data-confirm') || '';
    const id     = btn.getAttribute('data-id') || '';
    const text   = btn.getAttribute('data-text') || 'Tem certeza?';
    const formId = btn.getAttribute('data-form') || '';
    const method = btn.getAttribute('data-method') || 'POST';

    document.getElementById('confirmModalBody').innerHTML = text;
    document.getElementById('confirmAction').value = action;
    document.getElementById('confirmId').value = id;

    const form = document.getElementById('confirmForm');
    form.method = method;

    if (formId) {
      // Se quiser enviar num form específico (ex.: com mais campos hidden)
      const externalForm = document.getElementById(formId);
      if (externalForm) {
        // Copia os hidden para o form do modal
        form.innerHTML = externalForm.innerHTML + form.innerHTML;
      }
    }

    modal.show();
  });
})();
</script>
</body>
</html>
