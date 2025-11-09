<?php
declare(strict_types=1);

require_once __DIR__ . '/src/horarios.php';

if (defined('HORARIOS_DISPO_TEST_MODE') && HORARIOS_DISPO_TEST_MODE === true) {
    return;
}

require_once __DIR__ . '/conexao.php';

header('Content-Type: application/json; charset=utf-8');

$idBarbeiro = isset($_GET['id_barbeiro']) ? (int) $_GET['id_barbeiro'] : 0;
$data = $_GET['data'] ?? '';

if ($idBarbeiro <= 0 || trim($data) === '') {
    http_response_code(400);
    echo json_encode([
        'vazio' => true,
        'horarios' => [],
        'erro' => 'Parâmetros obrigatórios ausentes.',
    ]);
    exit;
}

try {
    $payload = horarios_dispo_calcular($conn, $idBarbeiro, $data);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE);
} catch (Throwable $exception) {
    error_log('Erro ao calcular horários disponíveis: ' . $exception->getMessage());
    http_response_code(500);
    echo json_encode([
        'vazio' => true,
        'horarios' => [],
        'erro' => 'Erro interno ao calcular horários.',
    ]);
}
