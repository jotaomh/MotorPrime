<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();

$db = db();

$allowedSituacao = ['pendente', 'aprovado', 'em_andamento', 'concluido', 'cancelado'];

// Update order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_status') {
    requireCsrf();
    $id = (int)$_POST['id'];
    $situacao = cleanInput($_POST['situacao'] ?? '');
    if ($id > 0 && in_array($situacao, $allowedSituacao)) {
        $db->query("UPDATE pedidos_cliente SET situacao = ? WHERE id = ?", [$situacao, $id]);
        flash('success', 'Status atualizado');
    }
    redirect('admin_pedidos.php');
}

// Filter
$tipo = $_GET['tipo'] ?? '';
$where = "WHERE 1=1";
$params = [];
if (isset($_GET['tipo']) && $tipo !== '') {
    if ($tipo === 'peca' || $tipo === 'servico') {
        $where .= " AND pc.tipo = ?";
        $params[] = $tipo;
    }
}

$stmt = $db->query("
    SELECT pc.*, c.nome as cliente_nome, c.telefone as cliente_telefone, v.placa, v.marca, v.modelo
    FROM pedidos_cliente pc
    JOIN clientes c ON pc.cliente_id = c.id
    LEFT JOIN veiculos v ON pc.veiculo_id = v.id
    $where
    ORDER BY pc.created_at DESC
", $params);
$pedidos = $stmt->fetchAll();

function getStatusClass($status) {
    $classes = [
        'pendente' => 'bg-yellow-900 text-yellow-400',
        'aprovado' => 'bg-blue-900 text-blue-400',
        'em_andamento' => 'bg-orange-900 text-orange-400',
        'concluido' => 'bg-green-900 text-green-400',
        'cancelado' => 'bg-red-900 text-red-400'
    ];
    return $classes[$status] ?? 'bg-gray-900 text-gray-400';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Pedidos</title>
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
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0a0a0a; }
    </style>
</head>
<body class="text-gray-100 min-h-screen">
    <nav class="bg-darker border-b border-gray-800">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <a href="dashboard.php" class="text-white text-sm hover:text-accent">← Voltar</a>
                <span class="text-xl font-bold text-white">Motor Prime</span>
            </div>
            <span class="text-sm text-gray-400"><?= e(auth()['nome'] ?? '') ?></span>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-white mb-6">Pedidos de Clientes</h1>

        <div class="flex gap-2 mb-6">
            <a href="admin_pedidos.php" class="px-4 py-2 rounded <?= $tipo === '' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300' ?>">Todos</a>
            <a href="admin_pedidos.php?tipo=peca" class="px-4 py-2 rounded <?= $tipo === 'peca' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300' ?>">Peças</a>
            <a href="admin_pedidos.php?tipo=servico" class="px-4 py-2 rounded <?= $tipo === 'servico' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300' ?>">Serviços</a>
        </div>

        <?php $f = getFlash('success'); if ($f): ?>
            <div class="bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded mb-4"><?= e($f['message']) ?></div>
        <?php endif; ?>

        <div class="space-y-4">
            <?php foreach ($pedidos as $p): ?>
            <div class="bg-darker rounded-lg p-4 border border-gray-800">
                <div class="flex justify-between items-start mb-3">
                    <div>
                        <span class="text-xs px-2 py-1 rounded <?= $p['tipo'] === 'peca' ? 'bg-blue-900 text-blue-400' : 'bg-purple-900 text-purple-400' ?>">
                            <?= $p['tipo'] === 'peca' ? 'Peça' : 'Serviço' ?>
                        </span>
                        <span class="ml-2 text-gray-400 text-sm"><?= formatDate($p['created_at']) ?></span>
                    </div>
                    <form method="POST" class="flex gap-2">
                        <?php csrf_field(); ?>
                        <input type="hidden" name="action" value="update_status">
                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
                        <select name="situacao" onchange="this.form.submit()" class="bg-darkGray border border-gray-700 rounded px-2 py-1 text-white text-sm">
                            <?php foreach ($allowedSituacao as $st): ?>
                            <option value="<?= e($st) ?>" <?= $p['situacao'] === $st ? 'selected' : '' ?>><?= e(ucfirst($st)) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </form>
                </div>
                
                <div class="text-white font-semibold"><?= e($p['descricao']) ?></div>
                
                <?php if ($p['tipo'] === 'peca'): ?>
                <div class="text-gray-400 text-sm">Quantidade: <?= (int)$p['quantidade'] ?></div>
                <?php else: ?>
                <div class="text-gray-400 text-sm">Veículo: <?= formatPlate($p['placa'] ?? '') ?> - <?= e($p['marca'] ?? '') ?> <?= e($p['modelo'] ?? '') ?></div>
                <div class="text-gray-400 text-sm">Tipo: <?= e($p['tipo_servico'] ?? '') ?></div>
                <?php endif; ?>
                
                <div class="mt-2 pt-2 border-t border-gray-800 text-sm text-gray-400">
                    Cliente: <?= e($p['cliente_nome']) ?> - <?= formatPhone($p['cliente_telefone'] ?? '') ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
