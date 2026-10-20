<?php

$debug = $_ENV['DEBUG'] ?? 'false';
if (is_string($debug)) {
    $debug = strtolower($debug) === 'true' || $debug === '1';
} else {
    $debug = (bool)$debug;
}

define('DB_HOST', $_ENV['DB_HOST'] ?? 'localhost');
define('DB_NAME', $_ENV['DB_NAME'] ?? 'motor_prime');
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('DB_CHARSET', $_ENV['DB_CHARSET'] ?? 'utf8mb4');

define('SESSION_NAME', $_ENV['SESSION_NAME'] ?? 'MOTOR_PRIME');
define('SESSION_LIFETIME', (int)($_ENV['SESSION_LIFETIME'] ?? 3600));

define('DEBUG_MODE', $debug);

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        try {
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            @file_put_contents((defined('LOG_PATH') ? LOG_PATH : __DIR__ . '/../../storage/logs') . '/error.log', date('Y-m-d H:i:s') . ' Erro na conexão: ' . $e->getMessage() . PHP_EOL, FILE_APPEND);
            if (defined('DEBUG_MODE') && DEBUG_MODE) {
                die("Erro na conexão: " . $e->getMessage());
            } else {
                die("Erro ao conectar ao banco de dados");
            }
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->pdo;
    }

    public function query($sql, $params = []) {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

function db() {
    return Database::getInstance();
}

function formatMoney($value) {
    return 'R$ ' . number_format((float)$value, 2, ',', '.');
}

function formatDate($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    return date('d/m/Y', $timestamp);
}

function formatDateTime($date) {
    if (empty($date)) return '';
    $timestamp = strtotime($date);
    if ($timestamp === false) return '';
    return date('d/m/Y H:i', $timestamp);
}

function formatPlate($plate) {
    $plate = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $plate));
    if (strlen($plate) === 7) {
        return substr($plate, 0, 3) . '-' . substr($plate, 3);
    }
    return $plate;
}

function formatCPF($cpf) {
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    if (strlen($cpf) === 11) {
        return substr($cpf, 0, 3) . '.' . substr($cpf, 3, 3) . '.' . substr($cpf, 6, 3) . '-' . substr($cpf, 9);
    }
    return $cpf;
}

function formatCNPJ($cnpj) {
    $cnpj = preg_replace('/[^0-9]/', '', $cnpj);
    if (strlen($cnpj) === 14) {
        return substr($cnpj, 0, 2) . '.' . substr($cnpj, 2, 3) . '.' . substr($cnpj, 5, 3) . '/' . substr($cnpj, 8, 4) . '-' . substr($cnpj, 12);
    }
    return $cnpj;
}

function formatPhone($phone) {
    $phone = preg_replace('/[^0-9]/', '', $phone);
    if (strlen($phone) === 11) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
    } elseif (strlen($phone) === 10) {
        return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 4) . '-' . substr($phone, 6);
    }
    return $phone;
}
