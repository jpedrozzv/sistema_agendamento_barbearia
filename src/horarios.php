<?php
declare(strict_types=1);

const HORARIOS_DISPO_DOMINGO_MSG = '🚫 Não é possível agendar aos domingos.';

/**
 * @return string[] Lista de horários no formato HH:MM.
 */
function horarios_dispo_gerar_padrao(): array
{
    $horarios = [];
    $inicio = new DateTimeImmutable('09:00');
    for ($i = 0; $i < 20; $i++) {
        $horarios[] = $inicio->add(new DateInterval('PT' . ($i * 30) . 'M'))->format('H:i');
    }

    return $horarios;
}

function horarios_dispo_validar_data(string $data): ?DateTimeImmutable
{
    $data = trim($data);
    $dateTime = DateTimeImmutable::createFromFormat('Y-m-d', $data);

    if (!$dateTime || $dateTime->format('Y-m-d') !== $data) {
        return null;
    }

    return $dateTime;
}

function horarios_dispo_buscar_feriado(object $conn, string $data): ?string
{
    if (!method_exists($conn, 'prepare')) {
        return null;
    }

    $stmt = $conn->prepare('SELECT descricao FROM Feriado WHERE data = ? LIMIT 1');
    if ($stmt === false) {
        return null;
    }

    $stmt->bind_param('s', $data);
    $stmt->execute();
    $result = $stmt->get_result();
    $descricao = null;

    if ($result && ($row = $result->fetch_assoc())) {
        $descricao = $row['descricao'] ?? '';
    }

    $stmt->close();

    return $descricao;
}

/**
 * @param object $conn Conexão com banco de dados (ou fakes para testes).
 * @param int $idBarbeiro Identificador do barbeiro.
 * @param string $data Data no formato Y-m-d.
 * @param string[] $horariosPadrao Grade padrão de horários.
 *
 * @return string[] Horários ocupados formatados como HH:MM.
 */
function horarios_dispo_buscar_ocupados(object $conn, int $idBarbeiro, string $data, array $horariosPadrao): array
{
    if (!method_exists($conn, 'prepare')) {
        return [];
    }

    $stmt = $conn->prepare(
        "SELECT a.hora, s.duracao
           FROM Agendamento a
           INNER JOIN Servico s ON a.id_servico = s.id_servico
          WHERE a.id_barbeiro = ?
            AND a.data = ?
            AND a.status IN ('pendente', 'confirmado')"
    );

    if ($stmt === false) {
        return [];
    }

    $stmt->bind_param('is', $idBarbeiro, $data);
    $stmt->execute();
    $result = $stmt->get_result();

    $ocupados = [];
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $hora = $row['hora'] ?? '';
            $duracao = max(0, (int) ($row['duracao'] ?? 0));
            if ($hora === '' || $duracao === 0) {
                continue;
            }

            $inicio = strtotime($hora);
            if ($inicio === false) {
                $inicio = strtotime($hora . ':00');
            }
            if ($inicio === false) {
                continue;
            }

            $fim = strtotime('+' . $duracao . ' minutes', $inicio);

            foreach ($horariosPadrao as $slot) {
                $slotTs = strtotime($slot);
                if ($slotTs === false) {
                    continue;
                }

                if ($slotTs >= $inicio && $slotTs < $fim) {
                    $ocupados[$slot] = true;
                }
            }
        }
    }

    $stmt->close();

    return array_keys($ocupados);
}

function horarios_dispo_calcular(object $conn, int $idBarbeiro, string $data): array
{
    $gradePadrao = horarios_dispo_gerar_padrao();
    $respostaBase = [
        'vazio' => true,
        'horarios' => [],
    ];

    $dataValida = horarios_dispo_validar_data($data);
    if (!$dataValida) {
        return $respostaBase + ['erro' => 'Data inválida.'];
    }

    if ((int) $dataValida->format('w') === 0) {
        return $respostaBase + ['erro' => HORARIOS_DISPO_DOMINGO_MSG];
    }

    $descricaoFeriado = horarios_dispo_buscar_feriado($conn, $dataValida->format('Y-m-d'));
    if ($descricaoFeriado) {
        return $respostaBase + ['erro' => 'Feriado: ' . $descricaoFeriado];
    }

    $ocupados = horarios_dispo_buscar_ocupados($conn, $idBarbeiro, $dataValida->format('Y-m-d'), $gradePadrao);
    $disponiveis = array_values(array_diff($gradePadrao, $ocupados));

    return [
        'vazio' => count($disponiveis) === 0,
        'horarios' => $disponiveis,
    ];
}
