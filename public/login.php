<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\AuthService;

$action = $_GET['action'] ?? 'show';

// Handle logout via POST only
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['logout'])) {
    requireCsrf();
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    redirect('login.php');
}

if (auth()) {
    redirect('dashboard.php');
}
if (clienteAuth()) {
    redirect('cliente_loja.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($action === 'login' || isset($_POST['login']))) {
    requireCsrf();
    $identificador = trim($_POST['identificador'] ?? $_POST['email'] ?? '');
    $senha = $_POST['senha'] ?? '';
    
    $result = AuthService::attempt($identificador, $senha);
    if ($result['success']) {
        flash('success', 'Login realizado com sucesso');
        redirect($result['redirect']);
    } else {
        flash('error', $result['message']);
        redirect('login.php');
    }
}

$flash = getFlash('error') ?? getFlash('success');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Motor Prime</h1>
            <p class="text-gray-600">Acesse sua conta</p>
        </div>

        <?php if ($flash): ?>
        <div class="mb-4 p-4 rounded <?= ($flash['type'] === 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <?php csrf_field(); ?>
            <input type="hidden" name="login" value="1">
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="identificador">
                    E-mail ou CPF
                </label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="identificador" type="text" name="identificador" required autocomplete="username" maxlength="150">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha">
                    Senha
                </label>
                <div class="relative">
                    <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           id="senha" type="password" name="senha" required autocomplete="current-password" minlength="8">
                    <button type="button" onclick="togglePass()" class="absolute right-2 top-2 text-sm text-blue-600">Mostrar</button>
                </div>
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                    type="submit">
                Entrar
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="cadastro.php" class="text-blue-600 hover:text-blue-800 text-sm">Criar conta</a>
        </div>
        <div class="text-center mt-2">
            <a href="index.php" class="text-gray-600 hover:text-gray-800 text-sm">Voltar ao site</a>
        </div>
    </div>
    <script>
        function togglePass() {
            var p = document.getElementById('senha');
            if (p.type === 'password') { p.type = 'text'; } else { p.type = 'password'; }
        }
    </script>
</body>
</html>
