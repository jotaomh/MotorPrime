<?php

require_once __DIR__ . '/../helpers/helpers.php';

function requireAuth() {
    if (!auth()) {
        redirect('login.php');
    }
}

function requireClienteAuth() {
    if (!clienteAuth()) {
        redirect('cliente.php');
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        flash('error', 'Acesso restrito');
        redirect('dashboard.php');
    }
}

function checkPermission($cargo) {
    if (!auth() || ($_SESSION['usuario']['cargo'] ?? '') !== $cargo) {
        return false;
    }
    return true;
}

function hasPermission($requiredCargo) {
    $cargos = ['admin' => 1, 'mecanico' => 2, 'vendedor' => 3];
    $userCargo = auth()['cargo'] ?? '';
    if (($cargos[$userCargo] ?? 99) === 1) {
        return true;
    }
    $allowed = [
        'admin' => ['admin', 'mecanico', 'vendedor'],
        'mecanico' => ['mecanico'],
        'vendedor' => ['vendedor']
    ];
    return in_array($requiredCargo, $allowed[$userCargo] ?? []);
}
