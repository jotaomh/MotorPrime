<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';

$action = $_GET['action'] ?? 'login';

if ($action === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'login') {
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($email) || empty($senha)) {
        flash('error', 'Preencha todos os campos');
        header('Location: login.php');
        exit;
    }

    $db = db();
    $stmt = $db->query("SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]);
    $usuario = $stmt->fetch();

    if ($usuario && password_verify($senha, $usuario['senha'])) {
        $_SESSION['usuario'] = [
            'id' => $usuario['id'],
            'nome' => $usuario['nome'],
            'email' => $usuario['email'],
            'cargo' => $usuario['cargo']
        ];
        flash('success', 'Bem-vindo, ' . $usuario['nome']);
        header('Location: dashboard.php');
        exit;
    } else {
        flash('error', 'Email ou senha inválidos');
        header('Location: login.php');
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action === 'register') {
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['cargo'] !== 'admin') {
        flash('error', 'Acesso restrito');
        header('Location: login.php');
        exit;
    }

    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    $cargo = sanitize($_POST['cargo'] ?? 'vendedor');

    if (empty($nome) || empty($email) || empty($senha)) {
        flash('error', 'Preencha todos os campos');
        header('Location: login.php?action=register');
        exit;
    }

    $db = db();
    $stmt = $db->query("SELECT id FROM usuarios WHERE email = ?", [$email]);
    if ($stmt->fetch()) {
        flash('error', 'Email já cadastrado');
        header('Location: login.php?action=register');
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $db->query(
        "INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)",
        [$nome, $email, $senhaHash, $cargo]
    );

    flash('success', 'Usuário cadastrado com sucesso');
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#1e3a5f',
                        secondary: '#2d5a87',
                        accent: '#f59e0b'
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="w-full max-w-md">
        <div class="bg-white rounded-lg shadow-xl p-8">
            <div class="text-center mb-8">
                <h1 class="text-3xl font-bold text-primary">Motor Prime</h1>
                <p class="text-gray-500 mt-2">Oficina Mecânica + Autopeças</p>
            </div>

            <?php if (flash('error')): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <?= flash('error') ?>
                </div>
            <?php endif; ?>

            <?php if (flash('success')): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    <?= flash('success') ?>
                </div>
            <?php endif; ?>

            <?php if ($action === 'register'): ?>
                <form method="POST" action="login.php?action=register">
                    <input type="hidden" name="nome" value="<?= $_POST['nome'] ?? '' ?>">
                    <input type="hidden" name="email" value="<?= $_POST['email'] ?? '' ?>">
                    <input type="hidden" name="senha" value="<?= $_POST['senha'] ?? '' ?>">
                    <input type="hidden" name="cargo" value="<?= $_POST['cargo'] ?? 'vendedor' ?>">
                    
                    <div class="text-center mb-4">
                        <span class="text-sm text-gray-600">Confirmar cadastro de usuário?</span>
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-secondary transition">
                        Confirmar Cadastro
                    </button>
                    
                    <a href="login.php" class="block text-center mt-4 text-gray-600 hover:text-primary">
                        Cancelar
                    </a>
                </form>
            <?php else: ?>
                <form method="POST" action="login.php?action=login">
                    <div class="mb-4">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                            Email
                        </label>
                        <input type="email" id="email" name="email" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                            placeholder="seu@email.com">
                    </div>
                    
                    <div class="mb-6">
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="senha">
                            Senha
                        </label>
                        <input type="password" id="senha" name="senha" required
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-primary"
                            placeholder="••••••••">
                    </div>
                    
                    <button type="submit" class="w-full bg-primary text-white py-3 rounded-lg font-semibold hover:bg-secondary transition">
                        Entrar
                    </button>
                </form>
            <?php endif; ?>
        </div>
        
        <div class="text-center mt-4 text-gray-500 text-sm">
            <p>Usuário padrão: admin@motorprime.com</p>
            <p>Senha: password</p>
        </div>
    </div>
</body>
</html>