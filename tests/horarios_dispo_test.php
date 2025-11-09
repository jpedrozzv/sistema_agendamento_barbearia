<?php
declare(strict_types=1);

define('HORARIOS_DISPO_TEST_MODE', true);

require_once __DIR__ . '/../horarios_dispo.php';

class FakeResult
{
    private array $rows;
    private int $index = 0;
    public int $num_rows;

    public function __construct(array $rows)
    {
        $this->rows = array_values($rows);
        $this->num_rows = count($rows);
    }

    public function fetch_assoc(): ?array
    {
        if ($this->index >= $this->num_rows) {
            return null;
        }

        return $this->rows[$this->index++];
    }
}

class FakeMysqli
{
    private array $fixtures;

    public function __construct(array $fixtures)
    {
        $this->fixtures = $fixtures;
    }

    public function query(string $sql): FakeResult
    {
        if (stripos($sql, 'from feriado') !== false) {
            return new FakeResult($this->fixtures['feriados'] ?? []);
        }

        if (stripos($sql, 'from agendamento') !== false) {
            return new FakeResult($this->fixtures['agendamentos'] ?? []);
        }

        throw new RuntimeException('Consulta inesperada: ' . $sql);
    }
}

function assertEquals(mixed $expected, mixed $actual, string $message = ''): void
{
    if ($expected != $actual) {
        $msg = $message !== '' ? $message . '\n' : '';
        $msg .= 'Esperado: ' . var_export($expected, true) . '\nObtido: ' . var_export($actual, true);
        throw new RuntimeException($msg);
    }
}

function assertArrayHasKey(string $key, array $array, string $message = ''): void
{
    if (!array_key_exists($key, $array)) {
        $msg = $message !== '' ? $message . '\n' : '';
        $msg .= "Chave ausente: {$key}";
        throw new RuntimeException($msg);
    }
}

$tests = [];

$tests['horarios_disponiveis_com_agendamentos'] = function (): void {
    $fixtures = [
        'feriados' => [],
        'agendamentos' => [
            ['hora' => '10:00', 'duracao' => 60],
            ['hora' => '13:30', 'duracao' => 30],
        ],
    ];

    $conn = new FakeMysqli($fixtures);
    $response = horarios_dispo_calcular($conn, 1, '2024-06-15');

    assertArrayHasKey('vazio', $response);
    assertArrayHasKey('horarios', $response);
    assertEquals(false, $response['vazio'], 'Deve haver horários disponíveis.');

    $todos_horarios = horarios_dispo_gerar_padrao();
    $esperados = array_values(array_diff($todos_horarios, ['10:00', '10:30', '13:30']));

    assertEquals($esperados, $response['horarios'], 'Horários livres não conferem com o esperado.');
};

$tests['data_em_feriado'] = function (): void {
    $fixtures = [
        'feriados' => [
            ['descricao' => 'Natal'],
        ],
        'agendamentos' => [],
    ];

    $conn = new FakeMysqli($fixtures);
    $response = horarios_dispo_calcular($conn, 2, '2024-12-25');

    assertArrayHasKey('vazio', $response);
    assertEquals(true, $response['vazio']);
    assertArrayHasKey('erro', $response);
    assertEquals('Feriado: Natal', $response['erro']);
};

$tests['data_em_domingo'] = function (): void {
    $fixtures = [
        'feriados' => [],
        'agendamentos' => [],
    ];

    $conn = new FakeMysqli($fixtures);
    $response = horarios_dispo_calcular($conn, 4, '2024-06-16');

    assertArrayHasKey('vazio', $response);
    assertEquals(true, $response['vazio']);
    assertArrayHasKey('erro', $response);
    assertEquals(HORARIOS_DISPO_DOMINGO_MSG, $response['erro']);
};

$tests['json_estrutura_padrao'] = function (): void {
    $fixtures = [
        'feriados' => [],
        'agendamentos' => [],
    ];

    $conn = new FakeMysqli($fixtures);
    $response = horarios_dispo_calcular($conn, 3, '2024-08-20');

    $json = json_encode($response);
    $decoded = json_decode($json, true);

    assertEquals($response, $decoded, 'JSON deve ser válido e manter a estrutura original.');
    assertEquals(20, count($response['horarios']), 'Grade padrão deve conter 20 horários.');
};

$failures = 0;
foreach ($tests as $name => $test) {
    try {
        $test();
        echo ".";
    } catch (Throwable $e) {
        $failures++;
        echo "F\nFalha em {$name}: \n" . $e->getMessage() . "\n";
    }
}

echo "\n";

if ($failures > 0) {
    echo "{$failures} teste(s) falhou/falharam.\n";
    exit(1);
}

echo "Todos os testes passaram.\n";
