<?php

require_once __DIR__ . '/../config/database.php';

function url($path = '') {
    $path = ltrim($path, '/');
    if ($path === '') {
        return BASE_URL === '' ? '/' : BASE_URL . '/';
    }
    $base = BASE_URL === '' ? '' : BASE_URL;
    return $base . '/' . $path;
}

function redirect($url) {
    if (strpos($url, 'http://') === 0 || strpos($url, 'https://') === 0 || strpos($url, '/') === 0) {
        header("Location: $url");
        exit;
    }
    header("Location: " . url($url));
    exit;
}

function e($string) {
    if (is_null($string)) {
        return '';
    }
    return htmlspecialchars((string)$string, ENT_QUOTES, 'UTF-8');
}

function flash($key, $message = null, $type = 'info') {
    if ($message === null) {
        if (isset($_SESSION['flash'][$key])) {
            $msg = $_SESSION['flash'][$key];
            unset($_SESSION['flash'][$key]);
            if (isset($_SESSION['flash_type'][$key])) {
                unset($_SESSION['flash_type'][$key]);
            }
            return $msg;
        }
        return null;
    }
    $_SESSION['flash'][$key] = $message;
    $_SESSION['flash_type'][$key] = $type;
}

function getFlash($key) {
    $msg = $_SESSION['flash'][$key] ?? null;
    $type = $_SESSION['flash_type'][$key] ?? 'info';
    unset($_SESSION['flash'][$key], $_SESSION['flash_type'][$key]);
    return $msg ? ['message' => $msg, 'type' => $type] : null;
}

function auth() {
    return $_SESSION['usuario'] ?? null;
}

function clienteAuth() {
    return $_SESSION['cliente'] ?? null;
}

function isAdmin() {
    return isset($_SESSION['usuario']['cargo']) && $_SESSION['usuario']['cargo'] === 'admin';
}

function isMecanico() {
    return isset($_SESSION['usuario']['cargo']) && $_SESSION['usuario']['cargo'] === 'mecanico';
}

function isVendedor() {
    return isset($_SESSION['usuario']['cargo']) && $_SESSION['usuario']['cargo'] === 'vendedor';
}

function sanitize($data) {
    if (is_array($data)) {
        return array_map('sanitize', $data);
    }
    if (is_string($data)) {
        return trim($data);
    }
    return $data;
}

function cleanInput($data) {
    if (is_array($data)) {
        return array_map('cleanInput', $data);
    }
    if (is_string($data)) {
        return trim($data);
    }
    return $data;
}

function onlyDigits($value) {
    if ($value === null) {
        return null;
    }
    return preg_replace('/[^0-9]/', '', (string)$value);
}

function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function csrf_token() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_field() {
    echo '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

function csrf_verify($token = null) {
    if ($token === null && isset($_POST['csrf_token'])) {
        $token = $_POST['csrf_token'];
    }
    if (empty($token) || empty($_SESSION['csrf_token'])) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

function requireCsrf() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (!csrf_verify()) {
            http_response_code(419);
            die('Token CSRF inválido');
        }
    }
}

function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function isValidCPF($cpf) {
    $cpf = onlyDigits($cpf);
    if (strlen($cpf) != 11) return false;
    if (preg_match('/(\d)\1{10}/', $cpf)) return false;
    for ($t = 9; $t < 11; $t++) {
        for ($d = 0, $c = 0; $c < $t; $c++) {
            $d += $cpf[$c] * (($t + 1) - $c);
        }
        $d = ((10 * $d) % 11) % 10;
        if ($cpf[$c] != $d) return false;
    }
    return true;
}

function isValidCNPJ($cnpj) {
    $cnpj = onlyDigits($cnpj);
    if (strlen($cnpj) != 14) return false;
    if (preg_match('/(\d)\1{13}/', $cnpj)) return false;
    $b = [6,5,4,3,2,9,8,7,6,5,4,3,2];
    for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);
    if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return false;
    for ($i = 0, $n = 0; $i < 13; $n += $cnpj[$i] * $b[++$i]);
    if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) return false;
    return true;
}

function isValidPlate($plate) {
    $plate = strtoupper(onlyDigits(str_replace(['-'], '', $plate)));
    // allow with letters? better check format
    $p = strtoupper(preg_replace('/[^A-Z0-9]/', '', $plate));
    if (strlen($p) == 7) {
        // antiga AAA9999 or mercosul AAA9A99 simplified
        if (preg_match('/^[A-Z]{3}[0-9]{4}$/', $p)) return true;
        if (preg_match('/^[A-Z]{3}[0-9][A-Z][0-9]{2}$/', $p)) return true;
    }
    return false;
}
