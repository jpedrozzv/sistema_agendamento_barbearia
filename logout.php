<?php
session_start();

// Limpa todas as variáveis de sessão
$_SESSION = [];

// Destroi a sessão completamente
session_destroy();

// Redireciona para a tela de login
header("Location: login.php");
exit;
?>
