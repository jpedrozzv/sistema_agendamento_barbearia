<?php
declare(strict_types=1);

require_once __DIR__ . '/../src/horarios.php';

define('HORARIOS_DISPO_TEST_MODE', true);

class FakeResult
{
    private array $rows;
    private int $index = 0;

    public function __construct(array $rows)
    {
        $this->rows = array_values($rows);
    }

    public function fetch_assoc(): ?array
    {
        if ($this->index >= count($this->rows)) {
            return null;
        }

        return $this->rows[$this->index++];
    }
}

class FakeStatement
{
    private string $sql;
    private array $fixtures;
    private array $boundRefs = [];
    private array $boundValues = [];

    public function __construct(string $sql, array $fixtures)
    {
        $this->sql = $sql;
        $this->fixtures = $fixtures;
    }

    public function bind_param(string $types, &...$vars): void
    {
        $this->boundRefs = &$vars;
    }

    public function execute(): bool
    {
        $this->boundValues = array_map(static fn (&$value) => $value, $this->boundRefs);
        return true;
    }

    public function get_result(): FakeResult
    {
        if (stripos($this->sql, 'from feriado') !== false) {
            $data = $this->boundValues[0] ?? null;
            $feriados = $this->fixtures['feriados'] ?? [];

            if ($data !== null && isset($feriados[$data])) {
                return new FakeResult([
                    ['descricao' => $feriados[$data]],
                ]);
            }

            return new FakeResult([]);
        }

        if (stripos($this->sql, 'from agendamento') !== false) {
            $idBarbeiro = (int) ($this->boundValues[0] ?? 0);
            $data = $this->boundValues[1] ?? null;

            $registros = [];
            foreach ($this->fixtures['agendamentos'] ?? [] as $agendamento) {
                if ((int) ($agendamento['id_barbeiro'] ?? 0) !== $idBarbeiro) {
                    continue;
                }

                if (($agendamento['data'] ?? null) !== $data) {
                    continue;
                }

                $registros[] = [
                    'hora' => $agendamento['hora'],
                    'duracao' => $agendamento['duracao'],
                ];
            }

            return new FakeResult($registros);
        }

        return new FakeResult([]);
    }

    public function close(): void
    {
    }
}

class FakeMysqli
{
    private array $fixtures;

    public function __construct(array $fixtures)
    {
        $this->fixtures = $fixtures;
    }

    public function prepare(string $sql): FakeStatement
    {
        return new FakeStatement($sql, $this->fixtures);
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
            ['id_barbeiro' => 1, 'data' => '2024-06-15', 'hora' => '10:00', 'duracao' => 60],
            ['id_barbeiro' => 1, 'data' => '2024-06-15', 'hora' => '13:30', 'duracao' => 30],
            ['id_barbeiro' => 2, 'data' => '2024-06-15', 'hora' => '09:00', 'duracao' => 30],
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
            '2024-12-25' => 'Natal',
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

$tests['data_invalida'] = function (): void {
    $fixtures = [
        'feriados' => [],
        'agendamentos' => [],
    ];

    $conn = new FakeMysqli($fixtures);
    $response = horarios_dispo_calcular($conn, 3, '2024-02-30');

    assertArrayHasKey('erro', $response);
    assertEquals('Data inválida.', $response['erro']);
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
