<?php

namespace App\Helpers;

class Seguranca {
    public static function requireLogin() {
        if (empty($_SESSION['usuario'])) {
            redirect('login.php');
        }
    }

    public static function requireCliente() {
        if (empty($_SESSION['cliente'])) {
            redirect('cliente.php');
        }
    }

    public static function requirePermissao($acao) {
        self::requireLogin();
        if (!Permissao::pode($acao)) {
            flash('error', 'Acesso não autorizado');
            redirect('dashboard.php');
        }
    }

    public static function regenerateSession() {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }
}
