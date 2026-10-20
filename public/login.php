<?php
require_once __DIR__ . '/../app/bootstrap.php';



$action = $_GET['action'] ?? 'show';

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
    $token = sanitize($_POST['token'] ?? '');
    $nome = sanitize($_POST['nome'] ?? '');
    $email = sanitize($_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';

    if (empty($token)) {
        flash('error', 'Token de convite necessário');
        header('Location: login.php?action=register');
        exit;
    }

    // Verify token
    $db = db();
    $stmt = $db->query(
        "SELECT * FROM tokens_cadastro WHERE token = ? AND used = 0 AND expires_at > NOW()",
        [$token]
    );
    $tokenData = $stmt->fetch();

    if (!$tokenData) {
        flash('error', 'Token inválido ou expirado');
        header('Location: login.php?action=register');
        exit;
    }

    $cargo = $tokenData['cargo']; // Use cargo from token

    if (empty($nome) || empty($email) || empty($senha)) {
        flash('error', 'Preencha todos os campos');
        header('Location: login.php?action=register');
        exit;
    }

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

    // Mark token as used
    $db->query("UPDATE tokens_cadastro SET used = 1 WHERE id = ?", [$tokenData['id']]);

    flash('success', 'Usuário cadastrado com sucesso! Faça login.');
    header('Location: login.php');
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
                        dark: '#0a0a0a',
                        darker: '#121212',
                        darkGray: '#1a1a1a',
                        darkMid: '#2a2a2a',
                        primary: '#1e3a5f',
                        primaryLight: '#2d5a87',
                        accent: '#3b82f6',
                        accentLight: '#60a5fa'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: 'Segoe UI', system-ui, sans-serif;
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 50%, #0a0a0a 100%);
            min-height: 100vh;
        }
        .login-bg {
            background: 
                radial-gradient(ellipse at 20% 80%, rgba(30, 58, 95, 0.4) 0%, transparent 50%),
                radial-gradient(ellipse at 80% 20%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
                linear-gradient(135deg, #0a0a0a 0%, #121212 50%, #0a0a0a 100%);
        }
        .glass {
            background: rgba(26, 26, 26, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }
        .input-field {
            background: #1a1a1a;
            border: 1px solid #2a2a2a;
            transition: all 0.3s ease;
        }
        .input-field:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2);
        }
        .btn-primary {
            background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 100%);
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.3);
        }
        .btn-secondary {
            background: transparent;
            border: 1px solid #2a2a2a;
            transition: all 0.3s ease;
        }
        .btn-secondary:hover {
            background: #1a1a1a;
            border-color: #3b82f6;
        }
        .tab-active {
            background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 100%);
        }
        .fade-in {
            animation: fadeIn 0.5s ease;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body class="login-bg min-h-screen flex flex-col">
    <!-- Header Simples -->
    <header class="p-6">
        <div class="flex items-center justify-between">
            <a href="index.php" class="flex items-center gap-3">
                <svg class="w-10 h-10 text-accent" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"/>
                </svg>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </a>
            <a href="index.php" class="text-gray-400 hover:text-white transition text-sm">← Voltar</a>
        </div>
    </header>

    <!-- Main - Login/Register Forms -->
    <main class="flex-1 flex items-center justify-center p-6">
        <div class="w-full max-w-md fade-in">
            <div class="glass rounded-2xl p-8">
                <!-- Logo -->
                <div class="text-center mb-8">
                    <svg class="w-16 h-16 text-accent mx-auto mb-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8zm-1-13h2v6h-2zm0 8h2v2h-2z"/>
                    </svg>
                    <h1 class="text-2xl font-bold text-white">Bem-vindo</h1>
                    <p class="text-gray-400 text-sm mt-1">Acesse sua conta para continuar</p>
</div>

                <!-- Alerts -->
                <?php if (flash('error')): ?>
                    <div class="bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded-lg mb-4 text-sm">
                        <?= flash('error') ?>
                    </div>
                <?php endif; ?>
                <?php if (flash('success')): ?>
                    <div class="bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded-lg mb-4 text-sm">
                        <?= flash('success') ?>
                    </div>
                <?php endif; ?>

                <!-- Login Form -->
                <form id="form-login" method="POST" action="login.php?action=login">
                    <div class="space-y-5">
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
                        <div class="flex justify-between items-center text-sm">
                            <label class="flex items-center text-gray-400">
                                <input type="checkbox" class="mr-2 rounded bg-darker border-gray-700">
                                Lembrar
                            </label>
                            <a href="#" class="text-accent hover:text-accentLight">Esqueceu a senha?</a>
                        </div>
                        <button type="submit" class="btn-primary w-full py-3 rounded-lg font-semibold text-white">
                            Entrar
                        </button>
                    </div>
                </form>

                <!-- Divider -->
                <div class="flex items-center my-6">
                    <div class="flex-1 border-t border-gray-800"></div>
                    <span class="px-4 text-gray-600 text-sm">ou</span>
                    <div class="flex-1 border-t border-gray-800"></div>
                </div>

                <!-- Cadastrar Button -->
                <div class="text-center">
                    <p class="text-gray-400 text-sm mb-4">Novo funcionário?</p>
                    <a href="cadastrar.php" class="block w-full border border-accent text-accent hover:bg-accent hover:text-white py-3 rounded-lg font-semibold text-center transition">
                        Criar Conta
                    </a>
                </div>

                <!-- Demo Access -->
                <div class="bg-darker rounded-lg p-4 text-center mt-6">
                    <p class="text-gray-500 text-sm mb-2">Acesso demonstração</p>
                    <p class="text-gray-400 text-xs">Email: admin@motorprime.com</p>
                    <p class="text-gray-400 text-xs">Senha: password</p>
                </div>
            </div>
            
            <!-- Back to Home -->
            <div class="text-center mt-6">
                <a href="index.php" class="text-gray-400 hover:text-white text-sm">
                    ← Voltar para página inicial
                </a>
            </div>
        </div>
    </main>

    <!-- Footer Simples -->
    <footer class="py-6 border-t border-gray-800">
        <div class="text-center text-gray-500 text-sm">
            Motor Prime © 2024 - Sistema de Gestão para Oficina Mecânica
        </div>
    </footer>

    <script>
        // Auto-hide messages after 5 seconds
        setTimeout(function() {
            var alerts = document.querySelectorAll('[class*="bg-red-900"], [class*="bg-green-900"]');
            alerts.forEach(function(alert) {
                alert.style.transition = 'opacity 0.5s';
                alert.style.opacity = '0';
                setTimeout(function() { alert.remove(); }, 500);
            });
        }, 5000);
    </script>
</body>
</html>