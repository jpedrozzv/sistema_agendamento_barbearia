<?php
/**
 * 🔒 Controle de acesso do CLIENTE (Barber La Mafia)
 * Impede que páginas do cliente sejam acessadas sem login.
 */

// Inicia sessão apenas se ainda não existir
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.gc_maxlifetime', 36000); // Sessão dura até 10 horas
    session_set_cookie_params(36000);
    session_start();
}

// Verifica se o cliente está autenticado
if (!isset($_SESSION['cliente_id']) || ($_SESSION['tipo'] ?? '') !== 'cliente') {
    header("Location: login.php");
    exit;
}
?>
