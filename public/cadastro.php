<?php
require_once __DIR__ . '/../app/bootstrap.php';

use App\Services\AuthService;

if (auth()) {
    redirect('dashboard.php');
}
if (clienteAuth()) {
    redirect('cliente_loja.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrf();
    $db = db()->getConnection();
    $nome = trim($_POST['nome'] ?? '');
    $cpf = preg_replace('/[^0-9]/', '', trim($_POST['cpf'] ?? ''));
    $telefone = preg_replace('/[^0-9]/', '', trim($_POST['telefone'] ?? ''));
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmar = $_POST['confirmar'] ?? '';
    $endereco = trim($_POST['endereco'] ?? '');
    
    $errors = [];
    if (empty($nome)) $errors[] = 'Nome obrigatório';
    if (!isValidCPF($cpf)) $errors[] = 'CPF inválido';
    if (!isValidEmail($email)) $errors[] = 'E-mail inválido';
    if (strlen($senha) < 8) $errors[] = 'Senha deve ter no mínimo 8 caracteres';
    if ($senha !== $confirmar) $errors[] = 'Senhas não conferem';
    
    if (empty($errors)) {
        $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
        $db->beginTransaction();
        try {
            // check existing cliente with senha NULL by cpf
            $stmt = $db->prepare("SELECT id, email, senha FROM clientes WHERE cpf = ?");
            $stmt->execute([$cpf]);
            $existing = $stmt->fetch();
            if ($existing) {
                if (!empty($existing['senha'])) {
                    $db->rollBack();
                    flash('error', 'CPF já cadastrado. Se já tem acesso, use a tela de login.');
                    redirect('cadastro.php');
                } else {
                    // check email match
                    if (strtolower($existing['email'] ?? '') !== $email && !empty($existing['email'])) {
                        $db->rollBack();
                        flash('error', 'Dados não conferem com o cadastro existente na oficina. Procure a oficina.');
                        redirect('cadastro.php');
                    }
                    $stmt = $db->prepare("UPDATE clientes SET nome = ?, telefone = ?, email = ?, senha = ?, endereco = ? WHERE id = ?");
                    $stmt->execute([$nome, $telefone, $email, $senhaHash, $endereco, $existing['id']]);
                    $clienteId = $existing['id'];
                }
            } else {
                // insert new
                $stmt = $db->prepare("INSERT INTO clientes (nome, cpf, telefone, email, senha, endereco) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$nome, $cpf, $telefone, $email, $senhaHash, $endereco]);
                $clienteId = $db->lastInsertId();
            }
            $db->commit();
            // auto login
            session_regenerate_id(true);
            unset($_SESSION['usuario']);
            $_SESSION['cliente'] = ['id' => $clienteId, 'nome' => $nome, 'email' => $email, 'cpf' => $cpf];
            flash('success', 'Cadastro realizado com sucesso');
            redirect('cliente_loja.php');
        } catch (Exception $e) {
            $db->rollBack();
            if ($e->getCode() == '23000') {
                flash('error', 'E-mail já cadastrado');
            } else {
                flash('error', 'Erro ao cadastrar');
            }
            redirect('cadastro.php');
        }
    } else {
        flash('error', implode('; ', $errors));
        redirect('cadastro.php');
    }
}

$flash = getFlash('error') ?? getFlash('success');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Cadastro</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/imask/6.6.1/imask.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">
    <div class="bg-white p-8 rounded-lg shadow-md w-full max-w-md">
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-gray-800">Criar Conta</h1>
        </div>

        <?php if ($flash): ?>
        <div class="mb-4 p-4 rounded <?= ($flash['type'] === 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <form method="POST" action="cadastro.php">
            <?php csrf_field(); ?>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="nome">Nome *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="nome" type="text" name="nome" required maxlength="100">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="cpf">CPF *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="cpf" type="text" name="cpf" required maxlength="14" placeholder="000.000.000-00">
            </div>
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="telefone">Telefone</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="telefone" type="text" name="telefone" maxlength="15" placeholder="(00) 00000-0000">
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
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="confirmar">Confirmar Senha *</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="confirmar" type="password" name="confirmar" required minlength="8">
            </div>
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2" for="endereco">Endereço</label>
                <input class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                       id="endereco" type="text" name="endereco" maxlength="255">
            </div>
            <button class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline" 
                    type="submit">Cadastrar</button>
        </form>

        <div class="text-center mt-4">
            <a href="login.php" class="text-blue-600 hover:text-blue-800 text-sm">Já tenho conta</a>
        </div>
    </div>
    <script>
        if (typeof IMask !== 'undefined') {
            IMask(document.getElementById('cpf'), { mask: '000.000.000-00' });
            IMask(document.getElementById('telefone'), { mask: '(00) 00000-0000' });
        }
    </script>
</body>
</html>
