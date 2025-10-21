<?php
session_start();
include("conexao.php");
include("verifica_adm.php");

// Verifica se o ID foi informado
if (!isset($_GET['id'])) {
    header("Location: listar_clientes.php?msg=senha_erro");
    exit;
}

$id = intval($_GET['id']);
$novaSenha = password_hash("1234", PASSWORD_DEFAULT);

$sql = "UPDATE Cliente SET senha='$novaSenha' WHERE id_cliente=$id";

if ($conn->query($sql)) {
    header("Location: listar_clientes.php?msg=senha_ok");
    exit;
} else {
    header("Location: listar_clientes.php?msg=senha_erro");
    exit;
}
?>
