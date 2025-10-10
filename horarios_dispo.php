<?php
include("conexao.php");
header('Content-Type: application/json; charset=utf-8');

// Parâmetros obrigatórios
$id_barbeiro = isset($_GET['id_barbeiro']) ? (int)$_GET['id_barbeiro'] : 0;
$data = $_GET['data'] ?? '';

if (!$id_barbeiro || !$data) {
    echo json_encode([]); // Sem parâmetros => retorna lista vazia
    exit;
}

// Gera horários (09:00 até 18:00, de 30 em 30)
$horarios = [];
for ($h = 9; $h <= 17; $h++) {
    $horarios[] = sprintf("%02d:00", $h);
    $horarios[] = sprintf("%02d:30", $h);
}
$horarios[] = "18:00";

// Consulta agendamentos do dia para o barbeiro
$sql = "SELECT TIME_FORMAT(a.hora, '%H:%i') AS hora, s.duracao
        FROM Agendamento a
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_barbeiro = ? AND a.data = ? AND a.status IN ('pendente','confirmado')";
$stmt = $conn->prepare($sql);
if (!$stmt) {
    // Em caso de erro na preparação, retorna vazio (evita quebrar o JSON)
    echo json_encode([]);
    exit;
}
$stmt->bind_param("is", $id_barbeiro, $data);
if (!$stmt->execute()) {
    echo json_encode([]);
    exit;
}
$result = $stmt->get_result();

$ocupados = [];
while ($row = $result->fetch_assoc()) {
    $inicio = strtotime($row['hora']);               // “HH:MM”
    $fim    = strtotime("+{$row['duracao']} minutes", $inicio);

    foreach ($horarios as $h) {
        $ts = strtotime($h);
        if ($ts >= $inicio && $ts < $fim) {
            $ocupados[$h] = true;
        }
    }
}

$disponiveis = array_values(array_diff($horarios, array_keys($ocupados)));
echo json_encode($disponiveis);
