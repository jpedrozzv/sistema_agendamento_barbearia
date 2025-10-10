<?php
include("conexao.php");
session_start();

// Verifica se é admin
if (!isset($_SESSION['admin_id']) || $_SESSION['tipo'] != "admin") {
    header("Location: login.php");
    exit;
}

// Verifica se recebeu o ID do cliente
if (isset($_GET['id'])) {
    $id_cliente = intval($_GET['id']);

    // Nova senha padrão
    $nova_senha = "1234";
    $hash = password_hash($nova_senha, PASSWORD_DEFAULT);

    // Atualiza no banco
    $sql = "UPDATE Cliente SET senha = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $hash, $id_cliente);

    if ($stmt->execute()) {
        header("Location: listar_clientes.php?msg=senha_ok");
        exit;
    } else {
        header("Location: listar_clientes.php?msg=senha_erro");
        exit;
    }
} else {
    header("Location: listar_clientes.php");
    exit;
}
?>
