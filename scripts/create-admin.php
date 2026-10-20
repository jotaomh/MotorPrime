<?php

if (php_sapi_name() !== 'cli') {
    die("Este script deve ser executado via CLI\n");
}

require_once __DIR__ . '/../app/bootstrap.php';

$db = db()->getConnection();

echo "=== Criar/Atualizar Administrador ===\n";
echo "Email: ";
$email = trim(fgets(STDIN));
if ($email === '') {
    die("Email é obrigatório\n");
}

echo "Nome: ";
$nome = trim(fgets(STDIN));
if ($nome === '') {
    die("Nome é obrigatório\n");
}

echo "Senha (mínimo 8 caracteres): ";
$senha = trim(fgets(STDIN));
if (strlen($senha) < 8) {
    die("Senha deve ter no mínimo 8 caracteres\n");
}

$senhaHash = password_hash($senha, PASSWORD_DEFAULT);

$stmt = $db->prepare("SELECT id FROM usuarios WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->fetch()) {
    $stmt = $db->prepare("UPDATE usuarios SET nome = ?, senha = ?, cargo = 'admin', ativo = 1 WHERE email = ?");
    $stmt->execute([$nome, $senhaHash, $email]);
    echo "Administrador atualizado com sucesso!\n";
} else {
    $stmt = $db->prepare("INSERT INTO usuarios (nome, email, senha, cargo, ativo) VALUES (?, ?, ?, 'admin', 1)");
    $stmt->execute([$nome, $email, $senhaHash]);
    echo "Administrador criado com sucesso!\n";
}
