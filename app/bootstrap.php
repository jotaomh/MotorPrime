<?php

$autoload = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoload)) {
    require_once $autoload;
} else {
    die("Erro: vendor/autoload.php não encontrado. Rode 'composer install' na raiz do projeto.");
}

$dotenvPath = dirname(__DIR__);
if (file_exists($dotenvPath . '/.env')) {
    if (class_exists('Dotenv\\Dotenv')) {
        try {
            $dotenv = Dotenv\Dotenv::createImmutable($dotenvPath);
            $dotenv->load();
        } catch (Exception $e) {
            // ignore
        }
    }
}

$required = ['DB_HOST', 'DB_NAME', 'DB_USER'];
foreach ($required as $key) {
    if (!isset($_ENV[$key]) || $_ENV[$key] === '') {
        die("Erro de configuração: variável obrigatória ausente no .env: $key");
    }
}

if (!isset($_ENV['DB_PASS'])) {
    $_ENV['DB_PASS'] = '';
}
if (!isset($_ENV['APP_ENV'])) {
    $_ENV['APP_ENV'] = 'local';
}

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', __DIR__);
define('PUBLIC_PATH', BASE_PATH . '/public');
define('STORAGE_PATH', BASE_PATH . '/storage');
define('LOG_PATH', STORAGE_PATH . '/logs');
define('APP_ENV', $_ENV['APP_ENV']);

if (!is_dir(STORAGE_PATH)) {
    @mkdir(STORAGE_PATH, 0755, true);
}
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
}

$debug = $_ENV['DEBUG'] ?? 'false';
if (is_string($debug)) {
    $debug = strtolower($debug) === 'true' || $debug === '1';
} else {
    $debug = (bool)$debug;
}
define('DEBUG', $debug);

$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$baseUrl = rtrim(str_replace(basename($scriptName), '', dirname($scriptName)), '/\\');
if ($baseUrl === DIRECTORY_SEPARATOR || $baseUrl === '.') {
    $baseUrl = '';
}
define('BASE_URL', $baseUrl);

if (session_status() === PHP_SESSION_NONE) {
    session_name($_ENV['SESSION_NAME'] ?? 'MOTOR_PRIME');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $_ENV['SESSION_PATH'] ?? '/',
        'httponly' => true,
        'samesite' => 'Lax',
        'secure' => $protocol === 'https'
    ]);
    session_start();
}

error_reporting(E_ALL);
ini_set('display_errors', DEBUG ? '1' : '0');
ini_set('log_errors', $_ENV['LOG_ERRORS'] ?? '1');
ini_set('error_log', LOG_PATH . '/error.log');

set_exception_handler(function($exception) {
    $message = get_class($exception) . ': ' . $exception->getMessage() . ' in ' . $exception->getFile() . ':' . $exception->getLine();
    @file_put_contents(LOG_PATH . '/exception.log', date('Y-m-d H:i:s') . ' ' . $message . PHP_EOL . $exception->getTraceAsString() . PHP_EOL . PHP_EOL, FILE_APPEND);
    if (defined('DEBUG') && DEBUG) {
        echo "<pre>" . htmlspecialchars($message) . "</pre>";
    } else {
        echo "Ocorreu um erro inesperado.";
    }
    exit;
});

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/helpers/helpers.php';
require_once __DIR__ . '/middleware/auth.php';
require_once __DIR__ . '/helpers/Permissao.php';
require_once __DIR__ . '/helpers/Seguranca.php';
