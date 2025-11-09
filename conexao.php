<?php
declare(strict_types=1);

if (!function_exists('carregarVariaveisAmbiente')) {
    function carregarVariaveisAmbiente(string $caminho): void
    {
        if (!is_readable($caminho)) {
            return;
        }

        $linhas = file($caminho, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($linhas === false) {
            return;
        }

        foreach ($linhas as $linha) {
            $linha = trim($linha);

            if ($linha === '' || str_starts_with($linha, '#')) {
                continue;
            }

            [$chave, $valor] = array_pad(explode('=', $linha, 2), 2, '');
            $chave = trim($chave);
            $valor = trim($valor);

            if ($chave === '') {
                continue;
            }

            if (getenv($chave) === false) {
                putenv("{$chave}={$valor}");
                $_ENV[$chave] = $valor;
            }
        }
    }
}

carregarVariaveisAmbiente(__DIR__ . '/.env');

$host = getenv('DB_HOST') !== false ? (string) getenv('DB_HOST') : '127.0.0.1';
$port = getenv('DB_PORT') !== false ? (int) getenv('DB_PORT') : 3306;
$database = getenv('DB_DATABASE') !== false ? (string) getenv('DB_DATABASE') : 'barbearia';
$username = getenv('DB_USERNAME') !== false ? (string) getenv('DB_USERNAME') : 'root';
$password = getenv('DB_PASSWORD') !== false ? (string) getenv('DB_PASSWORD') : '';

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $username, $password, $database, $port);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log('Erro na conexão com o banco: ' . $exception->getMessage());
    http_response_code(500);
    exit('Erro ao conectar ao banco de dados.');
}
