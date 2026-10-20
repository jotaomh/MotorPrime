<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();
if (!isAdmin()) {
    flash('error', 'Acesso restrito');
    redirect('dashboard.php');
}

$db = db();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'gerar') {
    requireCsrf();
    $cargo = $_POST['cargo'] ?? 'vendedor';
    if (!in_array($cargo, ['admin', 'mecanico', 'vendedor'])) {
        $cargo = 'vendedor';
    }
    $token = bin2hex(random_bytes(32));
    $expira = date('Y-m-d H:i:s', strtotime('+24 hours'));
    $createdBy = auth()['id'] ?? null;
    $stmt = $db->query("INSERT INTO tokens_cadastro (token, cargo, created_by, expires_at) VALUES (?, ?, ?, ?)", [$token, $cargo, $createdBy, $expira]);
    flash('success', 'Token gerado');
    redirect('admin_tokens.php');
}

// Delete token
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['id'])) {
    requireCsrf();
    $id = (int)$_POST['id'];
    $db->query("DELETE FROM tokens_cadastro WHERE id = ?", [$id]);
    flash('success', 'Token excluído');
    redirect('admin_tokens.php');
}

$stmt = $db->query("SELECT * FROM tokens_cadastro ORDER BY created_at DESC");
$tokens = $stmt->fetchAll();
$flash = getFlash('success') ?? getFlash('error');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Tokens</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-900 text-white p-4">
        <div class="container mx-auto flex justify-between items-center">
            <h1 class="text-xl font-bold">Motor Prime</h1>
            <a href="dashboard.php" class="hover:underline">Voltar</a>
        </div>
    </nav>

    <div class="container mx-auto p-6">
        <h2 class="text-2xl font-bold mb-6">Gerenciar Tokens de Cadastro</h2>

        <?php if ($flash): ?>
        <div class="mb-4 p-4 rounded <?= ($flash['type'] === 'error') ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' ?>">
            <?= e($flash['message']) ?>
        </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6 mb-6">
            <h3 class="text-lg font-semibold mb-4">Gerar Novo Token</h3>
            <form method="POST" class="flex gap-4">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="gerar">
                <select name="cargo" class="border rounded px-4 py-2">
                    <option value="vendedor">Vendedor</option>
                    <option value="mecanico">Mecânico</option>
                    <option value="admin">Administrador</option>
                </select>
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Gerar Token</button>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Token</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Cargo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Expira em</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ações</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php foreach ($tokens as $t): ?>
                    <tr>
                        <td class="px-6 py-4 text-sm font-mono text-gray-900">
                            <span class="token-text"><?= e($t['token']) ?></span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= e($t['cargo']) ?></td>
                        <td class="px-6 py-4 text-sm text-gray-500"><?= e($t['expires_at']) ?></td>
                        <td class="px-6 py-4 text-sm">
                            <?php if ($t['used_at']): ?>
                                <span class="text-red-600">Usado</span>
                            <?php elseif (strtotime($t['expires_at']) < time()): ?>
                                <span class="text-yellow-600">Expirado</span>
                            <?php else: ?>
                                <span class="text-green-600">Válido</span>
                            <?php endif; ?>
                        </td>
                        <td class="px-6 py-4 text-sm flex gap-2">
                            <?php if (!$t['used_at'] && strtotime($t['expires_at']) > time()): ?>
                            <button onclick="copiarToken('<?= e($t['token']) ?>')" class="text-blue-600 hover:text-blue-900">Copiar link</button>
                            <?php endif; ?>
                            <?php if (!$t['used_at']): ?>
                            <form method="POST" class="inline" onsubmit="return confirm('Excluir token?')">
                                <?php csrf_field(); ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $t['id'] ?>">
                                <button class="text-red-600 hover:text-red-900">Excluir</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
        function copiarToken(token) {
            var url = window.location.origin + '/cadastrar.php?token=' + token;
            navigator.clipboard.writeText(url).then(function() {
                alert('Link copiado!');
            });
        }
    </script>
</body>
</html>
