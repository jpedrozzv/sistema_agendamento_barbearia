<?php
/**
 * 🔒 Controle de acesso do administrador (Barber La Mafia)
 * Compatível com páginas que já iniciaram sessão.
 */

// Inicia sessão apenas se ainda não existir
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 36000); // Sessão dura até 10 horas
    session_set_cookie_params(36000);
    session_start();
}

// Verifica se o admin está autenticado
if (!isset($_SESSION['admin_id']) || ($_SESSION['tipo'] ?? '') !== 'admin') {
    header("Location: login.php");
    exit;
}
?>
