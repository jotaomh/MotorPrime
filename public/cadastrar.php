<?php
session_start();

// Limpar mensagens antigas

require_once __DIR__ . '/../app/config/database.php';

$action = $_GET['action'] ?? 'show';

if ($action === 'register') {
    $token = sanitize($_POST['token'] ?? '');
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($token)) {
        flash('error', 'Token de convite necessário');
        header('Location:cadastrar.php');
        exit;
    }

    $db = db();
    $stmt = $db->query(
        "SELECT * FROM tokens_cadastro WHERE token = ? AND used = 0 AND expires_at > NOW()",
        [$token]
    );
    $tokenData = $stmt->fetch();

    if (!$tokenData) {
        flash('error', 'Token inválido ou expirado');
        header('Location:cadastrar.php');
        exit;
    }

    if (empty($nome) || empty($email) || empty($senha)) {
        flash('error', 'Preencha todos os campos');
        header('Location:cadastrar.php');
        exit;
    }

    $stmt = $db->query("SELECT id FROM usuarios WHERE email = ?", [$email]);
    if ($stmt->fetch()) {
        flash('error', 'Email já cadastrado');
        header('Location:cadastrar.php');
        exit;
    }

    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $cargo = $tokenData['cargo'];

    $db->query(
        "INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)",
        [$nome, $email, $senhaHash, $cargo]
    );

    $db->query("UPDATE tokens_cadastro SET used = 1 WHERE id = ?", [$tokenData['id']]);

    flash('success', 'Conta criada! Faça login.');
    header('Location:login.php');
    exit;
}

$token = $_GET['token'] ?? '';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Cadastrar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: '#3b82f6',
                        accentLight: '#60a5fa',
                        dark: '#0a0a0a',
                        darker: '#121212',
                        darkGray: '#1a1a1a'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%); min-height: 100vh; }
        .input-field { background: #1a1a1a; border: 1px solid #2a2a2a; transition: all 0.3s ease; }
        .input-field:focus { border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2); }
    </style>
</head>
<body class="min-h-screen flex flex-col">
    <!-- Header -->
    <header class="p-6">
        <div class="flex items-center justify-between max-w-md mx-auto w-full">
            <a href="index.php" class="flex items-center gap-3">
                <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                </svg>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </a>
            <a href="login.php" class="text-gray-400 hover:text-white text-sm">← Login</a>
        </div>
    </header>

    <!-- Main -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md">
            <div class="bg-darker rounded-2xl p-8 border border-gray-800">
                <!-- Title -->
                <div class="text-center mb-8">
                    <svg class="w-16 h-16 text-accent mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M15 7a2 2 0 012 2m4 0a6 6 0 01-10.468 7.63M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h6.586a1 1 0 01.707.293l6.414 6.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <h1 class="text-2xl font-bold text-white">Criar Conta</h1>
                    <p class="text-gray-400 text-sm mt-1">Preencha os dados abaixo</p>
                </div>

                <!-- Messages -->
                <?php if (flash('error')): ?>
                    <div class="flash-msg bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= flash('error') ?></div>
                <?php endif; ?>
                <?php if (flash('success')): ?>
                    <div class="flash-msg bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded-lg mb-4 text-sm"><?= flash('success') ?></div>
                <?php endif; ?>

                <!-- Register Form -->
                <form method="POST" action="cadastrar.php?action=register">
                    <div class="space-y-4">
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Token de Convite *</label>
                            <input type="text" name="token" required value="<?= $token ?>"
                                placeholder="Cole o token aqui"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600 font-mono">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Nome Completo</label>
                            <input type="text" name="nome" required placeholder="Seu nome"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Email</label>
                            <input type="email" name="email" required placeholder="seu@email.com"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <div>
                            <label class="block text-gray-400 text-sm mb-2">Senha</label>
                            <input type="password" name="senha" required placeholder="••••••••"
                                class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600">
                        </div>
                        <button type="submit" class="w-full bg-accent hover:bg-blue-500 py-3 rounded-lg font-semibold text-white transition">
                            Criar Conta
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="py-6 border-t border-gray-800">
        <div class="text-center text-gray-500 text-sm">
            © 2024 Motor Prime
        </div>
    </footer>

    <script>
        setTimeout(function() {
            var msgs = document.querySelectorAll('.flash-msg');
            msgs.forEach(function(m) {
                m.style.transition = 'opacity 0.5s';
                m.style.opacity = '0';
                setTimeout(function() { m.remove(); }, 500);
            });
        }, 5000);
    </script>
</body>
</html>