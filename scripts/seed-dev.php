<?php

if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via CLI\n");
}

require_once __DIR__ . '/../app/bootstrap.php';

if (strtolower(APP_ENV) !== 'local') {
    die("seed-dev.php só pode ser executado em APP_ENV=local\n");
}

$db = db()->getConnection();

function genCPF() {
    $n = [];
    for ($i = 0; $i < 9; $i++) $n[] = rand(0, 9);
    $d1 = 0; for ($i = 0; $i < 9; $i++) $d1 += $n[$i] * (10 - $i);
    $d1 = $d1 % 11 < 2 ? 0 : 11 - ($d1 % 11);
    $n[] = $d1;
    $d2 = 0; for ($i = 0; $i < 10; $i++) $d2 += $n[$i] * (11 - $i);
    $d2 = $d2 % 11 < 2 ? 0 : 11 - ($d2 % 11);
    $n[] = $d2;
    return implode('', $n);
}

$accounts = [];

function upsertUser($db, $nome, $email, $senha, $cargo) {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $stmt = $db->prepare("SELECT id FROM usuarios WHERE LOWER(email) = LOWER(?)");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE usuarios SET nome = ?, senha = ?, cargo = ?, ativo = 1 WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$nome, $hash, $cargo, $email]);
    } else {
        $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, cargo, ativo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$nome, $email, $hash, $cargo]);
    }
}

function upsertCliente($db, $nome, $email, $senha, $cpf) {
    $hash = password_hash($senha, PASSWORD_DEFAULT);
    $cpf = preg_replace('/[^0-9]/', '', $cpf);
    $stmt = $db->prepare("SELECT id FROM clientes WHERE cpf = ?");
    $stmt->execute([$cpf]);
    if ($stmt->fetch()) {
        $stmt = $db->prepare("UPDATE clientes SET nome = ?, email = ?, senha = ?, ativo = 1 WHERE cpf = ?");
        $stmt->execute([$nome, $email, $hash, $cpf]);
    } else {
        $stmt = $db->prepare("SELECT id FROM clientes WHERE LOWER(email) = LOWER(?)");
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $stmt = $db->prepare("UPDATE clientes SET nome = ?, cpf = ?, senha = ?, ativo = 1 WHERE LOWER(email) = LOWER(?)");
            $stmt->execute([$nome, $cpf, $hash, $email]);
        } else {
            $stmt = $db->prepare("INSERT INTO clientes (nome, email, senha, cpf, telefone) VALUES (?, ?, ?, ?, '11999999999')");
            $stmt->execute([$nome, $email, $hash, $cpf]);
        }
    }
}

upsertUser($db, 'Administrador', 'admin@motorprime.com', 'Admin@123', 'admin');
$accounts[] = ['tipo' => 'funcionario', 'cargo' => 'admin', 'email' => 'admin@motorprime.com', 'senha' => 'Admin@123'];

upsertUser($db, 'Mecanico', 'mecanico@motorprime.com', 'Mecanico@123', 'mecanico');
$accounts[] = ['tipo' => 'funcionario', 'cargo' => 'mecanico', 'email' => 'mecanico@motorprime.com', 'senha' => 'Mecanico@123'];

upsertUser($db, 'Vendedor', 'vendedor@motorprime.com', 'Vendedor@123', 'vendedor');
$accounts[] = ['tipo' => 'funcionario', 'cargo' => 'vendedor', 'email' => 'vendedor@motorprime.com', 'senha' => 'Vendedor@123'];

$clientes = [
    ['nome' => 'José da Silva', 'email' => 'jose@email.com'],
    ['nome' => 'Maria dos Santos', 'email' => 'maria@email.com'],
    ['nome' => 'Pedro Almeida', 'email' => 'pedro@email.com'],
];

foreach ($clientes as $c) {
    $cpf = genCPF();
    upsertCliente($db, $c['nome'], $c['email'], 'Cliente@123', $cpf);
    $accounts[] = ['tipo' => 'cliente', 'nome' => $c['nome'], 'email' => $c['email'], 'cpf' => $cpf, 'senha' => 'Cliente@123'];
}

echo "=== Contas de Teste (APP_ENV=local) ===\n";
foreach ($accounts as $a) {
    if ($a['tipo'] === 'funcionario') {
        echo "[FUNCIONARIO] {$a['cargo']} | email: {$a['email']} | senha: {$a['senha']}\n";
    } else {
        echo "[CLIENTE] {$a['nome']} | email: {$a['email']} | cpf: {$a['cpf']} | senha: {$a['senha']}\n";
    }
}
echo "=== Fim ===\n";
