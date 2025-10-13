<?php
include("conexao.php");

if (!isset($_GET['id_barbeiro']) || !isset($_GET['data'])) {
    echo json_encode(["vazio" => true, "erro" => "Parâmetros ausentes"]);
    exit;
}

$id_barbeiro = intval($_GET['id_barbeiro']);
$data = $_GET['data'];

// 🔍 Verifica se é feriado
$feriado_sql = "SELECT * FROM Feriado WHERE data = '$data'";
$feriado_res = $conn->query($feriado_sql);

if ($feriado_res && $feriado_res->num_rows > 0) {
    $feriado = $feriado_res->fetch_assoc();
    echo json_encode([
        "vazio" => true,
        "erro" => "Feriado: " . $feriado['descricao']
    ]);
    exit;
}

// 🕘 Horários padrão (9h às 18h, de meia em meia hora)
$horarios = [];
for ($h = 9; $h <= 18; $h++) {
    foreach ([0, 30] as $m) {
        $hora = sprintf("%02d:%02d", $h, $m);
        $horarios[] = $hora;
    }
}

// 🔎 Busca agendamentos do barbeiro nesse dia
$sql = "SELECT hora, s.duracao 
        FROM Agendamento a
        JOIN Servico s ON a.id_servico = s.id_servico
        WHERE a.id_barbeiro = $id_barbeiro
        AND a.data = '$data'
        AND a.status IN ('pendente','confirmado')";
$res = $conn->query($sql);

// ⛔ Remove horários ocupados
$ocupados = [];
while ($row = $res->fetch_assoc()) {
    $inicio = strtotime($row['hora']);
    $fim = strtotime("+{$row['duracao']} minutes", $inicio);
    foreach ($horarios as $h) {
        $t = strtotime($h);
        if ($t >= $inicio && $t < $fim) {
            $ocupados[] = $h;
        }
    }
}

// ✅ Retorna horários disponíveis
$disponiveis = array_values(array_diff($horarios, $ocupados));

echo json_encode([
    "vazio" => count($disponiveis) == 0,
    "horarios" => $disponiveis
]);
?>
