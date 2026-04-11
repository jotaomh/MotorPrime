<?php

function requireAuth() {
    if (!auth()) {
        redirect('/public/login.php');
    }
}

function requireAdmin() {
    requireAuth();
    if (!isAdmin()) {
        flash('error', 'Acesso restrito');
        redirect('/public/dashboard.php');
    }
}

function checkPermission($cargo) {
    if (!auth() || $_SESSION['usuario']['cargo'] !== $cargo) {
        return false;
    }
    return true;
}

function hasPermission($requiredCargo) {
    $cargos = ['admin' => 1, 'mecanico' => 2, 'vendedor' => 3];
    $userCargo = auth()['cargo'] ?? '';
    return ($cargos[$userCargo] ?? 4) <= ($cargos[$requiredCargo] ?? 4);
}