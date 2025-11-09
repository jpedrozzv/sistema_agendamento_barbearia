<?php
if (!defined('HORARIOS_DISPO_TEST_MODE')) {
    define('HORARIOS_DISPO_TEST_MODE', false);
}

/**
 * Retorna a grade padrão de horários (9h às 18h, de 30 em 30 minutos).
 */
function horarios_dispo_gerar_padrao(): array
{
    $horarios = [];

    for ($h = 9; $h <= 18; $h++) {
        foreach ([0, 30] as $m) {
            $horarios[] = sprintf("%02d:%02d", $h, $m);
        }
    }

    return $horarios;
}

const HORARIOS_DISPO_DOMINGO_MSG = '🚫 Não é possível agendar aos domingos.';

/**
 * Calcula a resposta de horários disponíveis para um barbeiro em uma data específica.
 */
function horarios_dispo_calcular($conn, int $id_barbeiro, string $data): array
{
    $dataObj = \DateTime::createFromFormat('Y-m-d', $data);

    if (!$dataObj || $dataObj->format('Y-m-d') !== $data) {
        return [
            'vazio' => true,
            'erro' => 'Data inválida informada.',
        ];
    }

    if ((int)$dataObj->format('w') === 0) {
        return [
            'vazio' => true,
            'erro' => HORARIOS_DISPO_DOMINGO_MSG,
        ];
    }

    $feriado_sql = "SELECT * FROM Feriado WHERE data = '$data'";
    $feriado_res = $conn->query($feriado_sql);

    if ($feriado_res && $feriado_res->num_rows > 0) {
        $feriado = $feriado_res->fetch_assoc();

        return [
            "vazio" => true,
            "erro" => "Feriado: " . $feriado['descricao'],
        ];
    }

    $horarios = horarios_dispo_gerar_padrao();
    $sql = "SELECT hora, s.duracao
            FROM Agendamento a
            JOIN Servico s ON a.id_servico = s.id_servico
            WHERE a.id_barbeiro = $id_barbeiro
            AND a.data = '$data'
            AND a.status IN ('pendente','confirmado')";
    $res = $conn->query($sql);

    $ocupados = [];

    if ($res) {
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
    }

    $disponiveis = array_values(array_diff($horarios, $ocupados));

    return [
        "vazio" => count($disponiveis) === 0,
        "horarios" => $disponiveis,
    ];
}

if (!HORARIOS_DISPO_TEST_MODE) {
    if (!isset($conn)) {
        include "conexao.php";
    }

    if (!isset($_GET['id_barbeiro']) || !isset($_GET['data'])) {
        echo json_encode(["vazio" => true, "erro" => "Parâmetros ausentes"]);
        exit;
    }

    $id_barbeiro = intval($_GET['id_barbeiro']);
    $data = $_GET['data'];
    $response = horarios_dispo_calcular($conn, $id_barbeiro, $data);

    echo json_encode($response);
    exit;
}
