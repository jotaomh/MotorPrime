<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';

if (!isset($_SESSION['cliente'])) {
    header('Location: cliente.php');
    exit;
}

$clienteId = $_SESSION['cliente']['id'];
$db = db();

// Initialize cart
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}
$cartCount = count($_SESSION['carrinho']);

// Logout
if ($_GET['acao'] === 'logout') {
    session_destroy();
    header('Location: cliente.php');
    exit;
}

// Get client OS
$stmt = $db->query("
    SELECT os.*, v.placa, v.marca, v.modelo, v.ano
    FROM ordens_servico os
    JOIN veiculos v ON os.veiculo_id = v.id
    WHERE os.cliente_id = ?
    ORDER BY os.created_at DESC
", [$clienteId]);
$ordens = $stmt->fetchAll();

// Get my requests
$stmt = $db->query("
    SELECT * FROM pedidos_cliente 
    WHERE cliente_id = ? 
    ORDER BY created_at DESC
    LIMIT 10
", [$clienteId]);
$pedidos = $stmt->fetchAll();

function getStatusClass($status) {
    $classes = [
        'orcamento' => 'bg-yellow-900 text-yellow-400',
        'aprovado' => 'bg-blue-900 text-blue-400',
        'em_execucao' => 'bg-orange-900 text-orange-400',
        'finalizado' => 'bg-purple-900 text-purple-400',
        'entregue' => 'bg-green-900 text-green-400'
    ];
    return $classes[$status] ?? 'bg-gray-900 text-gray-400';
}

function getSituacaoClass($situacao) {
    $classes = [
        'pendente' => 'bg-yellow-900 text-yellow-400',
        'aprovado' => 'bg-blue-900 text-blue-400',
        'em_andamento' => 'bg-orange-900 text-orange-400',
        'concluido' => 'bg-green-900 text-green-400',
        'cancelado' => 'bg-red-900 text-red-400'
    ];
    return $classes[$situacao] ?? 'bg-gray-900 text-gray-400';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Minha Conta</title>
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
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
    </style>
</head>
<body class="bg-dark min-h-screen text-gray-100">
    <!-- Header -->
    <nav class="bg-darker border-b border-gray-800 sticky top-0 z-50">
        <div class="container mx-auto px-4 py-3">
            <div class="flex justify-between items-center">
                <div class="flex items-center gap-6">
                    <a href="cliente_dashboard.php" class="flex items-center gap-2 group">
                        <svg class="w-8 h-8 text-accent" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                        <span class="text-lg font-bold text-white group-hover:text-accent transition">Motor Prime</span>
                    </a>
                    
                    <div class="flex items-center gap-1">
                        <a href="cliente_loja.php" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-darkGray transition font-medium">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c1.957.691 4.124.691 6.082 0M17 13l2.293-2.293c-.957-.691-2.124-.691-3.082 0M12 20v-8m0 0l3-3m-3 3l3-3"/>
                                </svg>
                                Loja
                            </span>
                        </a>
                        <a href="cliente_mecanica.php" class="px-4 py-2 rounded-lg text-gray-400 hover:text-white hover:bg-darkGray transition font-medium">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Serviços
                            </span>
                        </a>
                    </div>
                </div>
                
                <div class="flex items-center gap-4">
                    <a href="cliente_loja.php?acao=ver_carrinho" class="relative p-2 rounded-lg hover:bg-darkGray transition group">
                        <svg class="w-6 h-6 text-gray-400 group-hover:text-accent transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c1.957.691 4.124.691 6.082 0M17 13l2.293-2.293c-.957-.691-2.124-.691-3.082 0M12 20v-8m0 0l3-3m-3 3l3-3"/>
                        </svg>
                        <?php if ($cartCount > 0): ?>
                        <span class="absolute -top-1 -right-1 bg-accent text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold"><?= $cartCount ?></span>
                        <?php endif; ?>
                    </a>
                    <div class="flex items-center gap-3">
                        <span class="text-sm text-gray-400"><?= $_SESSION['cliente']['nome'] ?></span>
                        <a href="?acao=logout" class="text-red-400 hover:text-red-300 text-sm">Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <h1 class="text-2xl font-bold text-white mb-6">Minha Conta</h1>

        <!-- Quick Actions -->
        <div class="grid md:grid-cols-2 gap-4 mb-8">
            <!-- Loja -->
            <a href="cliente_loja.php" class="bg-darker rounded-xl p-6 border border-gray-800 hover:border-accent transition group">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-accent/20 rounded-lg flex items-center justify-center group-hover:bg-accent/30 transition">
                        <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c1.957.691 4.124.691 6.082 0M17 13l2.293-2.293c-.957-.691-2.124-.691-3.082 0M12 20v-8m0 0l3-3m-3 3l3-3"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Loja de Peças</h2>
                        <p class="text-gray-400 text-sm">Compre peças para seu veículo</p>
                    </div>
                </div>
            </a>

            <!-- Mecânica -->
            <a href="cliente_mecanica.php" class="bg-darker rounded-xl p-6 border border-gray-800 hover:border-accent transition group">
                <div class="flex items-center gap-4">
                    <div class="w-14 h-14 bg-accent/20 rounded-lg flex items-center justify-center group-hover:bg-accent/30 transition">
                        <svg class="w-7 h-7 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-lg font-semibold text-white">Mecânica</h2>
                        <p class="text-gray-400 text-sm">Solicite serviços para seu carro</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- My Orders / OS -->
        <h2 class="text-xl font-bold text-white mb-4">Minhas Ordens de Serviço</h2>
        <?php if (!empty($ordens)): ?>
            <div class="space-y-4 mb-8">
                <?php foreach ($ordens as $os): ?>
                <div class="bg-darker rounded-lg p-4 border border-gray-800">
                    <div class="flex justify-between items-start">
                        <div>
                            <div class="font-bold text-white">OS #<?php echo str_pad($os['id'], 4, '0', STR_PAD_LEFT); ?></div>
                            <div class="text-gray-400 text-sm"><?php echo formatPlate($os['placa']); ?> - <?php echo $os['marca']; ?> <?php echo $os['modelo']; ?></div>
                        </div>
                        <span class="px-2 py-1 rounded text-xs <?php echo getStatusClass($os['status']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $os['status'])); ?>
                        </span>
                    </div>
                    <div class="mt-2 flex justify-between text-sm">
                        <span class="text-gray-500">Entrada: <?php echo formatDate($os['data_entrada']); ?></span>
                        <span class="text-accent font-bold"><?php echo formatMoney($os['valor_total']); ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-darker rounded-lg p-6 border border-gray-800 text-center mb-8">
                <p class="text-gray-400">Nenhuma ordem de serviço</p>
            </div>
        <?php endif; ?>

        <!-- My Requests -->
        <h2 class="text-xl font-bold text-white mb-4">Meus Pedidos</h2>
        <?php if (!empty($pedidos)): ?>
            <div class="space-y-3">
                <?php foreach ($pedidos as $p): ?>
                <div class="bg-darker rounded-lg p-4 border border-gray-800">
                    <div class="flex justify-between items-center">
                        <div>
                            <span class="text-xs px-2 py-1 rounded <?php echo $p['tipo'] === 'peca' ? 'bg-blue-900 text-blue-400' : 'bg-purple-900 text-purple-400'; ?>">
                                <?php echo $p['tipo'] === 'pedido' ? 'Peça' : 'Serviço'; ?>
                            </span>
                            <div class="text-white mt-1"><?php echo htmlspecialchars($p['descricao']); ?></div>
                            <?php if($p['tipo'] === 'pedido'): ?>
                            <div class="text-gray-400 text-sm"><?= $p['quantidade'] ?> item(s) - R$ <?= number_format($p['valor_total'], 2, ',', '.') ?></div>
                            <?php endif; ?>
                        </div>
                        <span class="px-2 py-1 rounded text-xs <?php echo getSituacaoClass($p['situacao']); ?>">
                            <?php echo ucfirst($p['situacao']); ?>
                        </span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="bg-darker rounded-lg p-6 border border-gray-800 text-center">
                <p class="text-gray-400">Nenhum pedido</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>