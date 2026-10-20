<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';

if (!isset($_SESSION['cliente'])) {
    header('Location: cliente.php');
    exit;
}

$clienteId = $_SESSION['cliente']['id'];

// Initialize cart
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Logout
if (isset($_GET['acao']) && $_GET['acao'] === 'logout') {
    $_SESSION['carrinho'] = [];
    session_destroy();
    header('Location: cliente.php');
    exit;
}

$db = db();

// Add to cart
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_cart') {
    $pecaId = (int)$_POST['peca_id'];
    $quantidade = (int)$_POST['quantidade'];
    
    if ($pecaId > 0 && $quantidade > 0) {
        $stmt = $db->query("SELECT * FROM pecas WHERE id = ?", [$pecaId]);
        $peca = $stmt->fetch();
        
        if ($peca) {
            if (isset($_SESSION['carrinho'][$pecaId])) {
                $_SESSION['carrinho'][$pecaId]['quantidade'] += $quantidade;
            } else {
                $_SESSION['carrinho'][$pecaId] = [
                    'id' => $peca['id'],
                    'codigo' => $peca['codigo'],
                    'descricao' => $peca['descricao'],
                    'marca' => $peca['marca'],
                    'preco' => $peca['preco_venda'],
                    'quantidade' => $quantidade,
                    'estoque' => $peca['quantidade']
                ];
            }
            flash('success', 'Item adicionado ao carrinho');
        }
    }
    header('Location: cliente_loja.php');
    exit;
}

// Remove from cart
if (isset($_GET['acao']) && $_GET['acao'] === 'remover_item') {
    $pecaId = (int)$_GET['id'];
    if (isset($_SESSION['carrinho'][$pecaId])) {
        unset($_SESSION['carrinho'][$pecaId]);
        flash('success', 'Item removido do carrinho');
    }
    header('Location: cliente_loja.php');
    exit;
}

// Update cart quantity
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'atualizar_qty') {
    $pecaId = (int)$_POST['peca_id'];
    $quantidade = (int)$_POST['quantidade'];
    
    if (isset($_SESSION['carrinho'][$pecaId]) && $quantidade > 0) {
        if ($quantidade > $_SESSION['carrinho'][$pecaId]['estoque']) {
            $quantidade = $_SESSION['carrinho'][$pecaId]['estoque'];
        }
        $_SESSION['carrinho'][$pecaId]['quantidade'] = $quantidade;
    }
    header('Location: cliente_loja.php');
    exit;
}

// Finalize order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'finalizar_pedido') {
    if (!empty($_SESSION['carrinho'])) {
        $itens = [];
        $total = 0;
        
        foreach ($_SESSION['carrinho'] as $item) {
            $subtotal = $item['preco'] * $item['quantidade'];
            $total += $subtotal;
            $itens[] = $item['codigo'] . ' - ' . $item['descricao'] . ' (x' . $item['quantidade'] . ')';
        }
        
        $db->query(
            "INSERT INTO pedidos_cliente (cliente_id, tipo, descricao, quantidade, valor_total, situacao) VALUES (?, 'pedido', ?, ?, ?, 'pendente')",
            [$clienteId, implode(' | ', $itens), count($_SESSION['carrinho']), $total]
        );
        
        // Decrease stock
        foreach ($_SESSION['carrinho'] as $item) {
            $db->query("UPDATE pecas SET quantidade = quantidade - ? WHERE id = ?", [$item['quantidade'], $item['id']]);
        }
        
        $_SESSION['carrinho'] = [];
        flash('success', 'Pedido realizado com sucesso! Status: Pendente');
    }
    header('Location: cliente_loja.php');
    exit;
}

// Get available parts
$stmt = $db->query("
    SELECT * FROM pecas 
    WHERE ativo = 1 AND quantidade > 0 
    ORDER BY descricao
");
$pecas = $stmt->fetchAll();

// Get categories
$stmt = $db->query("
    SELECT DISTINCT categoria FROM pecas 
    WHERE categoria IS NOT NULL AND categoria != '' AND ativo = 1
    ORDER BY categoria
");
$categorias = $stmt->fetchAll();

// Get my pending orders (pieces only, not service)
$stmt = $db->query(
    "SELECT * FROM pedidos_cliente WHERE cliente_id = ? AND tipo = 'pedido' ORDER BY created_at DESC",
    [$clienteId]
);
$meusPedidos = $stmt->fetchAll();

// Cart count
$cartCount = count($_SESSION['carrinho']);
$cartTotal = 0;
foreach ($_SESSION['carrinho'] as $item) {
    $cartTotal += $item['preco'] * $item['quantidade'];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Loja de Peças</title>
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
                        <a href="cliente_loja.php" class="px-4 py-2 rounded-lg bg-accent text-white font-medium">
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
                        <a href="cliente_loja.php?acao=logout" class="text-red-400 hover:text-red-300 text-sm">Sair</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-6">
        <!-- Messages -->
        <?php if (flash('success')): ?>
            <div class="flash-msg bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded mb-4"><?= flash('success') ?></div>
        <?php endif; ?>
        <?php if (flash('error')): ?>
            <div class="flash-msg bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded mb-4"><?= flash('error') ?></div>
        <?php endif; ?>

        <!-- Show Cart if requested -->
        <?php if (isset($_GET['acao']) && $_GET['acao'] === 'ver_carrinho'): ?>
            <div class="bg-darker rounded-xl border border-gray-800 p-6 mb-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-xl font-bold text-white">Carrinho de Compras</h2>
                    <a href="cliente_loja.php" class="text-gray-400 hover:text-white">← Voltar à loja</a>
                </div>
                
                <?php if (!empty($_SESSION['carrinho'])): ?>
                    <div class="space-y-4 mb-6">
                        <?php foreach ($_SESSION['carrinho'] as $item): ?>
                        <div class="flex items-center justify-between p-4 bg-darkGray rounded-lg">
                            <div class="flex items-center gap-4">
                                <div class="text-accent font-mono text-sm"><?= $item['codigo'] ?></div>
                                <div>
                                    <div class="text-white font-medium"><?= $item['descricao'] ?></div>
                                    <div class="text-gray-400 text-sm"><?= $item['marca'] ?? '' ?></div>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <form method="POST" class="flex items-center gap-2">
                                    <input type="hidden" name="action" value="atualizar_qty">
                                    <input type="hidden" name="peca_id" value="<?= $item['id'] ?>">
                                    <input type="number" name="quantidade" value="<?= $item['quantidade'] ?>" min="1" max="<?= $item['estoque'] ?>" 
                                        class="w-16 bg-darker border border-gray-700 rounded px-2 py-1 text-white text-center">
                                    <button type="submit" class="text-gray-400 hover:text-accent">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                        </svg>
                                    </button>
                                </form>
                                <div class="text-accent font-bold">R$ <?= number_format($item['preco'] * $item['quantidade'], 2, ',', '.') ?></div>
                                <a href="cliente_loja.php?acao=remover_item&id=<?= $item['id'] ?>" class="text-red-400 hover:text-red-300">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="flex justify-between items-center pt-4 border-t border-gray-800">
                        <div class="text-gray-400">Total: <span class="text-white text-xl font-bold">R$ <?= number_format($cartTotal, 2, ',', '.') ?></span></div>
                        <form method="POST">
                            <input type="hidden" name="action" value="finalizar_pedido">
                            <button type="submit" class="bg-accent hover:bg-accentLight px-6 py-3 rounded-lg font-semibold text-white transition">
                                Finalizar Pedido
                            </button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c1.957.691 4.124.691 6.082 0M17 13l2.293-2.293c-.957-.691-2.124-.691-3.082 0M12 20v-8m0 0l3-3m-3 3l3-3"/>
                        </svg>
                        <p class="text-gray-400">Carrinho vazio</p>
                        <a href="cliente_loja.php" class="text-accent hover:text-accentLight mt-2 inline-block">Ver peças disponíveis</a>
                    </div>
                <?php endif; ?>
            </div>
        <?php else: ?>
            
            <!-- My Orders -->
            <?php if (!empty($meusPedidos)): ?>
            <div class="bg-darker rounded-lg border border-gray-800 p-4 mb-6">
                <h2 class="text-lg font-semibold text-white mb-4">Meus Pedidos</h2>
                <div class="space-y-2">
                    <?php foreach ($meusPedidos as $p): ?>
                    <div class="flex justify-between items-center p-3 bg-darkGray rounded">
                        <div>
                            <div class="text-white"><?= $p['descricao'] ?></div>
                            <div class="text-sm text-gray-400"><?= $p['quantidade'] ?> item(s) - R$ <?= number_format($p['valor_total'], 2, ',', '.') ?></div>
                            <div class="text-xs text-gray-500"><?= date('d/m/Y H:i', strtotime($p['created_at'])) ?></div>
                        </div>
                        <span class="px-2 py-1 rounded text-xs <?= 
                            $p['situacao'] === 'pendente' ? 'bg-yellow-900 text-yellow-400' : 
                            ($p['situacao'] === 'aprovado' ? 'bg-green-900 text-green-400' : 'bg-red-900 text-red-400')
                        ?>">
                            <?= ucfirst($p['situacao']) ?>
                        </span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <!-- Parts Catalog -->
            <h2 class="text-xl font-bold text-white mb-4">Catálogo de Peças</h2>
            
            <?php if (!empty($pecas)): ?>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    <?php foreach ($pecas as $p): ?>
                    <div class="bg-darker rounded-lg border border-gray-800 p-4 hover:border-accent/50 transition">
                        <div class="flex justify-between items-start mb-2">
                            <span class="text-accent font-mono text-sm"><?= $p['codigo'] ?></span>
                            <span class="text-xs text-gray-500">Est: <?= $p['quantidade'] ?></span>
                        </div>
                        <h3 class="text-white font-semibold mb-1"><?= $p['descricao'] ?></h3>
                        <p class="text-gray-400 text-sm mb-3"><?= $p['marca'] ?? '' ?> - <?= $p['categoria'] ?? '' ?></p>
                        <div class="flex justify-between items-center">
                            <span class="text-accent font-bold text-lg">R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?></span>
                            <form method="POST" class="flex gap-2">
                                <input type="hidden" name="action" value="add_cart">
                                <input type="hidden" name="peca_id" value="<?= $p['id'] ?>">
                                <input type="number" name="quantidade" value="1" min="1" max="<?= $p['quantidade'] ?>" 
                                    class="w-16 bg-darkGray border border-gray-700 rounded px-2 py-2 text-white text-sm text-center">
                                <button type="submit" class="bg-accent hover:bg-accentLight px-4 py-2 rounded text-sm font-medium text-white transition flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                    </svg>
                                    Add
                                </button>
                            </form>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="bg-darker rounded-lg p-8 border border-gray-800 text-center">
                    <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                    </svg>
                    <p class="text-gray-400">Nenhuma peça disponível no momento</p>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>

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