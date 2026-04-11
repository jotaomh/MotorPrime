<?php
session_start();

// Limpar mensagens antigas
unset($_SESSION['flash'], $_SESSION['flash_type']);

require_once __DIR__ . '/../app/config/database.php';

$action = $_GET['action'] ?? '';

if ($action === 'logout') {
    unset($_SESSION['cliente']);
    header('Location: cliente.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $login = sanitize($_POST['login'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($login) || empty($senha)) {
        header('Location: cliente.php?erro=Preencha todos os campos');
        exit;
    }

    $db = db();
    
    // Try CPF or email
    $stmt = $db->query(
        "SELECT * FROM clientes WHERE (cpf = ? OR email = ?) AND ativo = 1",
        [$login, $login]
    );
    $cliente = $stmt->fetch();

    if ($cliente && password_verify($senha, $cliente['senha'])) {
        $_SESSION['cliente'] = [
            'id' => $cliente['id'],
            'nome' => $cliente['nome'],
            'email' => $cliente['email']
        ];
        header('Location: cliente_loja.php');
        exit;
    } else {
        header('Location: cliente.php?erro=CPF/Email ou senha inválidos');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    $nome = sanitize($_POST['nome'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', $_POST['cpf'] ?? '');
    $telefone = sanitize($_POST['telefone'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $endereco = sanitize($_POST['endereco'] ?? '');

    if (empty($nome) || empty($cpf) || empty($telefone) || empty($email) || empty($senha)) {
        header('Location: cliente.php?erro=Preencha todos os campos obrigatórios');
        exit;
    }

    $db = db();
    
    // Check if CPF or email exists
    $stmt = $db->query("SELECT id FROM clientes WHERE cpf = ? OR email = ?", [$cpf, $email]);
    if ($stmt->fetch()) {
        header('Location: cliente.php?erro=CPF ou email já cadastrado');
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    
    $db->query(
        "INSERT INTO clientes (nome, cpf, telefone, email, endereco, senha) VALUES (?, ?, ?, ?, ?, ?)",
        [$nome, $cpf, $telefone, $email, $endereco, $senhaHash]
    );

    header('Location: cliente.php?msg=Conta criada com sucesso! Faça login.');
    exit;
}