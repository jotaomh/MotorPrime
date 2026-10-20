<?php
require_once __DIR__ . '/../app/bootstrap.php';

if (!isset($_SESSION['cliente'])) {
    redirect('cliente.php');
}

$clienteId = $_SESSION['cliente']['id'];
$db = db();

// Initialize cart
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
$cartCount = count($_SESSION['carrinho']);

// Logout
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    unset($_SESSION['cliente']);
    session_regenerate_id(true);
    redirect('cliente.php');
}

// Get my vehicles
$stmt = $db->query(
    "SELECT * FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY placa",
    [$clienteId]
);
$veiculos = $stmt->fetchAll();

// Get my service requests
$stmt = $db->query(
    "SELECT s.*, v.placa, v.marca, v.modelo 
    FROM solicitacoes_servico s
    JOIN veiculos v ON s.veiculo_id = v.id
    WHERE s.cliente_id = ? AND s.ativo = 1
    ORDER BY s.created_at DESC",
    [$clienteId]
);
$meusPedidos = $stmt->fetchAll();

// Request service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    requireCsrf();
    $veiculoId = (int)$_POST['veiculo_id'];
    $descricao = cleanInput($_POST['descricao'] ?? '');
    $tipoServico = cleanInput($_POST['tipo_servico'] ?? '');
    
    if ($veiculoId > 0 && !empty($descricao)) {
        $stmt = $db->query("SELECT id FROM veiculos WHERE id = ? AND cliente_id = ? AND ativo = 1", [$veiculoId, $clienteId]);
        $veiculo = $stmt->fetch();
        if ($veiculo) {
            $stmt = $db->query(
                "INSERT INTO solicitacoes_servico (cliente_id, veiculo_id, tipo_servico, descricao, situacao) VALUES (?, ?, ?, ?, 'pendente')",
                [$clienteId, $veiculoId, $tipoServico, $descricao]
            );
            flash('success', 'Serviço solicitado! Entraremos em contato para confirmar.');
        } else {
            flash('error', 'Veículo inválido.');
        }
    } else {
        flash('error', 'Preencha todos os campos obrigatórios.');
    }
    redirect('cliente_mecanica.php');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Mecânica</title>
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
                        darkGray: '#1a1a1a',
                        darkMid: '#2a2a2a'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; background: #0a0a0a; min-height: 100vh; }
        .input-field { background: #1a1a1a; border: 1px solid #2a2a2a; }
        .input-field:focus { border-color: #3b82f6; }
    </style>
</head>
<body class="text-gray-100">
    <nav class="bg-darker border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-6">
                    <a href="cliente_dashboard.php" class="flex items-center gap-2 group">
                        <span class="text-lg font-bold text-white group-hover:text-accent transition">Motor Prime</span>
                    </a>
                    <div class="flex items-center gap-1">
                        <a href="cliente_loja.php" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-darkGray transition font-medium">Loja</a>
                        <a href="cliente_mecanica.php" class="px-4 py-2 rounded-lg bg-accent text-white font-medium">Serviços</a>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <a href="cliente_loja.php?acao=ver_carrinho" class="relative p-2 rounded-lg hover:bg-darkGray transition group">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-accent transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c1.957.691 4.124.691 6.082 0M17 13l2.293-2.293c-.957-.691-2.124-.691-3.082 0M12 20v-8"/></svg>
                        <?php if ($cartCount > 0): ?><span class="absolute -top-1 -right-1 bg-accent text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span><?php endif; ?>
                    </a>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400"><?= e($_SESSION['cliente']['nome']) ?></span>
                        <a href="cliente_mecanica.php?acao=logout" class="text-red-400 hover:text-red-300 text-sm">Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <?php $f = getFlash('success') ?? getFlash('error'); if ($f): ?>
            <div class="mb-4 p-4 rounded <?= $f['type']==='error'?'bg-red-900/30 border border-red-800 text-red-400':'bg-green-900/30 border border-green-800 text-green-400' ?>"><?= e($f['message']) ?></div>
        <?php endif; ?>

        <div class="bg-darker rounded-lg border border-gray-800 p-4 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Minhas Solicitações de Serviço</h2>
            <?php if (!empty($meusPedidos)): ?>
                <div class="space-y-2">
                    <?php foreach ($meusPedidos as $p): ?>
                    <div class="p-3 bg-darkGray rounded">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-white"><?= e($p['descricao']) ?></div>
                                <div class="text-sm text-gray-400"><?= formatPlate($p['placa']) ?> - <?= e($p['marca']) ?> <?= e($p['modelo']) ?></div>
                                <div class="text-xs text-gray-500"><?= e($p['tipo_servico']) ?></div>
                            </div>
                            <span class="px-2 py-1 rounded text-xs bg-yellow-900 text-yellow-400"><?= e(ucfirst($p['situacao'])) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Nenhuma solicitação</p>
            <?php endif; ?>
        </div>

        <h2 class="text-xl font-bold text-white mb-4">Solicitar Serviço</h2>
        <?php if (!empty($veiculos)): ?>
        <div class="bg-darker rounded-lg border border-gray-800 p-6">
            <form method="POST">
                <?php csrf_field(); ?>
                <input type="hidden" name="action" value="request">
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Veículo *</label>
                        <select name="veiculo_id" required class="input-field w-full px-4 py-3 rounded-lg text-white">
                            <option value="">Selecione</option>
                            <?php foreach ($veiculos as $v): ?>
                            <option value="<?= $v['id'] ?>"><?= formatPlate($v['placa']) ?> - <?= e($v['marca']) ?> <?= e($v['modelo']) ?> (<?= e($v['ano']) ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Tipo de Serviço</label>
                        <input type="text" name="tipo_servico" class="input-field w-full px-4 py-3 rounded-lg text-white" placeholder="Ex.: Troca de óleo, Revisão">
                    </div>
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Descrição do Problema *</label>
                        <textarea name="descricao" required rows="4" class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600" placeholder="Descreva o problema ou serviço desejado..."></textarea>
                    </div>
                    <button type="submit" class="w-full bg-accent hover:bg-blue-500 py-3 rounded-lg font-semibold text-white transition">Solicitar Serviço</button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-darker rounded-lg border border-gray-800 p-6 text-center">
            <p class="text-gray-400 mb-4">Você não tem veículos cadastrados.</p>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
