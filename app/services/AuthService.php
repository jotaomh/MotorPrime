<?php

namespace App\Services;

class AuthService {
    public static function attempt($identificador, $senha) {
        $identificador = trim($identificador);
        if (empty($identificador) || empty($senha)) {
            return ['success' => false, 'message' => 'E-mail/CPF ou senha inválidos'];
        }
        
        $db = \db()->getConnection();
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        
        // Rate limit by identificador + IP
        $stmt = $db->prepare("SELECT COUNT(*) as c FROM login_attempts WHERE identificador = ? AND ip = ? AND created_at >= DATE_SUB(NOW(), INTERVAL 15 MINUTE)");
        $stmt->execute([strtolower($identificador), $ip]);
        if ((int)$stmt->fetch()['c'] >= 5) {
            return ['success' => false, 'message' => 'Muitas tentativas. Tente novamente em 15 minutos.'];
        }
        
        $usuario = null;
        $tipo = null;
        
        if (strpos($identificador, '@') !== false) {
            $email = strtolower($identificador);
            $stmt = $db->prepare("SELECT * FROM usuarios WHERE LOWER(email) = ? AND ativo = 1");
            $stmt->execute([$email]);
            $usuario = $stmt->fetch();
            if ($usuario) {
                $tipo = 'usuario';
            } else {
                $stmt = $db->prepare("SELECT * FROM clientes WHERE LOWER(email) = ?");
                $stmt->execute([$email]);
                $cliente = $stmt->fetch();
                if ($cliente) {
                    $usuario = $cliente;
                    $tipo = 'cliente';
                }
            }
        } else {
            $cpf = preg_replace('/[^0-9]/', '', $identificador);
            $stmt = $db->prepare("SELECT * FROM clientes WHERE cpf = ?");
            $stmt->execute([$cpf]);
            $cliente = $stmt->fetch();
            if ($cliente) {
                $usuario = $cliente;
                $tipo = 'cliente';
            }
        }
        
        // constant time-ish check
        $hash = '$2y$10$' . str_repeat('A', 53);
        if ($usuario && isset($usuario['senha']) && $usuario['senha']) {
            $hash = $usuario['senha'];
        }
        $valid = password_verify($senha, $hash);
        
        if (!$usuario || !$valid || empty($usuario['senha'])) {
            $stmt = $db->prepare("INSERT INTO login_attempts (identificador, ip) VALUES (?, ?)");
            $stmt->execute([strtolower($identificador), $ip]);
            return ['success' => false, 'message' => 'E-mail/CPF ou senha inválidos'];
        }
        
        if (isset($usuario['senha']) && password_needs_rehash($usuario['senha'], PASSWORD_DEFAULT)) {
            $newHash = password_hash($senha, PASSWORD_DEFAULT);
            if ($tipo === 'usuario') {
                $stmt = $db->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
                $stmt->execute([$newHash, $usuario['id']]);
            } else {
                $stmt = $db->prepare("UPDATE clientes SET senha = ? WHERE id = ?");
                $stmt->execute([$newHash, $usuario['id']]);
            }
        }
        
        // clear attempts
        $stmt = $db->prepare("DELETE FROM login_attempts WHERE identificador = ? AND ip = ?");
        $stmt->execute([strtolower($identificador), $ip]);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        unset($_SESSION['usuario'], $_SESSION['cliente']);
        
        if ($tipo === 'usuario') {
            $_SESSION['usuario'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'],
                'cargo' => $usuario['cargo']
            ];
            $redirect = 'dashboard.php';
        } else {
            $_SESSION['cliente'] = [
                'id' => $usuario['id'],
                'nome' => $usuario['nome'],
                'email' => $usuario['email'] ?? '',
                'cpf' => $usuario['cpf'] ?? ''
            ];
            $redirect = 'cliente_loja.php';
        }
        
        return ['success' => true, 'redirect' => $redirect, 'tipo' => $tipo];
    }
}
