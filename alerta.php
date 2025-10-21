<?php
function mostrarAlerta($tipo, $mensagem, $icone = "ℹ️") {
    echo '
    <div class="alert alert-' . $tipo . ' alert-dismissible fade show mt-3" role="alert">
        ' . $icone . ' ' . $mensagem . '
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>';
}
?>
