<?php
require_once __DIR__ . '/../app/bootstrap.php';

$token = $_GET['token'] ?? '';
$db = db()->getConnection();

$stmt = $db->prepare("SELECT * FROM tokens_cadastro WHERE token = ? AND used_at IS NULL AND expires_at > NOW()");
$stmt->execute([$token]);
$tokenData = $stmt->fetch();

if (!$tokenData) {
    flash('error', 'Token inválido ou expirado');
    redirect('login.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    
    if (strlen($senha) < 8) {
        flash('error', 'Senha deve ter no mínimo 8 caracteres');
        redirect('cadastrar.php?token=' . urlencode($token));
    }
    if ($senha !== $confirmar) {
        flash('error', 'Senhas não conferem');
        redirect('cadastrar.php?token=' . urlencode($token));
    }
    if (!isValidEmail($email)) {
        flash('error', 'E-mail inválido');
        redirect('cadastrar.php?token=' . urlencode($token));
    }
    
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
    $pdo = $db;
    $pdo->beginTransaction();
    try {
        $stmt = $pdo->prepare("UPDATE tokens_cadastro SET used_at = NOW() WHERE token = ? AND used_at IS NULL AND expires_at > NOW()");
        $stmt->execute([$token]);
        if ($stmt->rowCount() !== 1) {
            throw new Exception('Token já utilizado');
        }
        $cargo = $tokenData['cargo'];
        if (!in_array($cargo, ['admin', 'mecanico', 'vendedor'])) {
            throw new Exception('Cargo inválido');
        }
        $stmt = $pdo->prepare("INSERT INTO usuarios (nome, email, senha, cargo, ativo) VALUES (?, ?, ?, ?, 1)");
        $stmt->execute([$nome, $email, $senhaHash, $cargo]);
        $pdo->commit();
        flash('success', 'Cadastro realizado com sucesso');
        redirect('login.php');
    } catch (Exception $e) {
        $pdo->rollBack();
        flash('error', 'Não foi possível completar o cadastro');
        redirect('cadastrar.php?token=' . urlencode($token));
    }
}

$flash = getFlash('error') ?? getFlash('success');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Cadastro Funcionário</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Cadastro por Convite</h1>
            <p class="text-gray-600">Cargo: <?= e($tokenData['cargo']) ?></p>
        </div>

        <?php if ($flash): ?>
        <div class="mb-4 p-4 rounded <?= ($flash['type'] === 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST">
            <?php csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Token</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-500 bg-gray-100" 
                       type="text" value="<?= e($token) ?>" readonly>
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nome">Nome *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="nome" type="text" name="nome" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="email">E-mail *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="email" type="email" name="email" required maxlength="150">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="senha">Senha *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="senha" type="password" name="senha" required minlength="8">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirmar">Confirmar Senha *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="confirmar" type="password" name="confirmar" required minlength="8">
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                    type="submit">Cadastrar</button>
        </form>
    </div>
</body>
</html>
