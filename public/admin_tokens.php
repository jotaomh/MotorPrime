<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();
requireAdmin();

// Generate token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'gerar') {
    $cargo = sanitize($_POST['cargo'] ?? '');
    
    if (empty($cargo)) {
        flash('error', 'Selecione o cargo');
        header('Location: admin_tokens.php');
        exit;
    }
    
    $token = bin2hex(random_bytes(16));
    $expires = date('Y-m-d H:i:s', strtotime('+7 days'));
    
    $db = db();
    $db->query(
        "INSERT INTO tokens_cadastro (token, cargo, created_by, expires_at) VALUES (?, ?, ?, ?)",
        [$token, $cargo, auth()['id'], $expires]
    );
    
    flash('success', 'Token gerado com sucesso!');
    header('Location: admin_tokens.php');
    exit;
}

// Delete token
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $db = db();
    $db->query("DELETE FROM tokens_cadastro WHERE id = ?", [$id]);
    flash('success', 'Token excluído');
    header('Location: admin_tokens.php');
    exit;
}

// Get all tokens
$db = db();
$stmt = $db->query("
    SELECT t.*, u.nome as creator_nome 
    FROM tokens_cadastro t
    JOIN usuarios u ON t.created_by = u.id
    ORDER BY t.created_at DESC
");
$tokens = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Gerenciar Tokens</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        accent: '#3b82f6',
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
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0a0a0a; min-height: 100vh; }
    </style>
</head>
<body class="text-gray-100">
    <nav class="bg-darker border-b border-gray-800">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="text-white text-sm hover:text-accent">← Voltar</a>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </div>
            <span class="text-sm text-gray-400"><?= auth()['nome'] ?></span>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6 max-w-4xl">
        <h1 class="text-2xl font-bold text-white mb-6">Gerenciar Tokens de Cadastro</h1>

        <!-- Generate Token Form -->
        <div class="bg-darker rounded-lg p-6 border border-gray-800 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Gerar Novo Token</h2>
            <form method="POST">
                <input type="hidden" name="action" value="gerar">
                <div class="flex gap-4">
                    <select name="cargo" required class="flex-1 bg-darkGray border border-gray-700 rounded px-4 py-3 text-white">
                        <option value="">Selecione o cargo</option>
                        <option value="mecanico">Mecânico</option>
                        <option value="vendedor">Vendedor</option>
                    </select>
                    <button type="submit" class="bg-accent hover:bg-blue-500 text-white px-6 py-3 rounded-lg font-semibold">
                        Gerar Token
                    </button>
                </div>
            </form>
        </div>

        <!-- Messages -->
        <?php if (flash('error')): ?>
            <div class="bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded mb-4"><?= flash('error') ?></div>
        <?php endif; ?>
        <?php if (flash('success')): ?>
            <div class="bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded mb-4"><?= flash('success') ?></div>
        <?php endif; ?>

        <!-- Tokens List -->
        <div class="bg-darker rounded-lg border border-gray-800">
            <div class="p-4 border-b border-gray-800">
                <h2 class="text-lg font-semibold text-white">Tokens Ativos</h2>
            </div>
            <div class="p-4 overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-gray-500 text-sm border-b border-gray-800">
                            <th class="pb-2 text-left">Token</th>
                            <th class="pb-2 text-left">Cargo</th>
                            <th class="pb-2 text-left">Criado por</th>
                            <th class="pb-2 text-left">Expira</th>
                            <th class="pb-2 text-left">Status</th>
                            <th class="pb-2 text-left">Ação</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($tokens as $t): ?>
                        <tr class="border-b border-gray-800">
                            <td class="py-3 font-mono text-accent"><?= $t['token'] ?></td>
                            <td class="py-3"><?= ucfirst($t['cargo']) ?></td>
                            <td class="py-3 text-gray-400"><?= $t['creator_nome'] ?></td>
                            <td class="py-3 text-gray-400"><?= formatDate($t['expires_at']) ?></td>
                            <td class="py-3">
                                <?php if ($t['used']): ?>
                                    <span class="text-gray-500">Usado</span>
                                <?php else: ?>
                                    <span class="text-green-400">Ativo</span>
                                <?php endif; ?>
                            </td>
                            <td class="py-3">
                                <a href="admin_tokens.php?action=delete&id=<?= $t['id'] ?>" class="text-red-400 hover:text-red-300 text-sm">Excluir</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                        <?php if (empty($tokens)): ?>
                        <tr><td colspan="6" class="py-4 text-center text-gray-500">Nenhum token gerado</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Instructions -->
        <div class="mt-6 bg-darker rounded-lg p-6 border border-gray-800">
            <h2 class="text-lg font-semibold text-white mb-2">Como usar</h2>
            <ol class="text-gray-400 text-sm space-y-2 list-decimal list-inside">
                <li>Ger um token para o cargo desejado (Mecânico ou Vendedor)</li>
                <li>Copie o token gerado</li>
                <li>Compartilhe o link: <code class="bg-darkGray px-2 py-1 rounded">seusite.com/login.php?token=TOKEN</code></li>
                <li>O funcionário poderá se cadastrar usando esse token</li>
                <li>Cada token pode ser usado apenas uma vez e expira em 7 dias</li>
            </ol>
        </div>
    </div>
</body>
</html>