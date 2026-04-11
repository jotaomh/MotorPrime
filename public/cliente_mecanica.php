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

// Get my vehicles
$stmt = $db->query(
    "SELECT * FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY placa",
    [$clienteId]
);
$veiculos = $stmt->fetchAll();

// Get my service requests
$stmt = $db->query(
    "SELECT pc.*, v.placa, v.marca, v.modelo 
    FROM pedidos_cliente pc
    JOIN veiculos v ON pc.veiculo_id = v.id
    WHERE pc.cliente_id = ? AND pc.tipo = 'servico' 
    ORDER BY pc.created_at DESC",
    [$clienteId]
);
$meusPedidos = $stmt->fetchAll();

// Request service
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'request') {
    $veiculoId = (int)$_POST['veiculo_id'];
    $descricao = sanitize($_POST['descricao']);
    $tipoServico = sanitize($_POST['tipo_servico']);
    
    if ($veiculoId > 0 && !empty($descricao)) {
        $stmt = $db->query("SELECT * FROM veiculos WHERE id = ?", [$veiculoId]);
        $veiculo = $stmt->fetch();
        
        $db->query(
            "INSERT INTO pedidos_cliente (cliente_id, tipo, veiculo_id, descricao, situacao, tipo_servico) VALUES (?, 'servico', ?, ?, ?, 'pendente', ?)",
            [$clienteId, $veiculoId, $descricao, $tipoServico]
        );
        flash('success', 'Serviço solicitado! Entraremos em contato para confirmar.');
    }
    header('Location: cliente_mecanica.php');
    exit;
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
                        <a href="cliente_mecanica.php" class="px-4 py-2 rounded-lg bg-accent text-white font-medium">
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
                        <a href="cliente_dashboard.php?acao=logout" class="text-red-400 hover:text-red-300 text-sm">Sair</a>
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

        <!-- My Service Requests -->
        <div class="bg-darker rounded-lg border border-gray-800 p-4 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Meus Serviços</h2>
            <?php if (!empty($meusPedidos)): ?>
                <div class="space-y-2">
                    <?php foreach ($meusPedidos as $p): ?>
                    <div class="p-3 bg-darkGray rounded">
                        <div class="flex justify-between">
                            <div>
                                <div class="text-white"><?= $p['descricao'] ?></div>
                                <div class="text-sm text-gray-400">
                                    <?= formatPlate($p['placa']) ?> - <?= $p['marca'] ?> <?= $p['modelo'] ?>
                                </div>
                                <div class="text-xs text-gray-500"><?= $p['tipo_servico'] ?></div>
                            </div>
                            <span class="px-2 py-1 rounded text-xs <?= 
                                $p['situacao'] === 'pendente' ? 'bg-yellow-900 text-yellow-400' : 
                                ($p['situacao'] === 'aprovado' ? 'bg-blue-900 text-blue-400' : 
                                ($p['situacao'] === 'em_andamento' ? 'bg-orange-900 text-orange-400' : 
                                ($p['situacao'] === 'concluido' ? 'bg-green-900 text-green-400' : 'bg-gray-900 text-gray-400')))
                            ?>">
                                <?= ucfirst($p['situacao']) ?>
                            </span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <p class="text-gray-500">Nenhum serviço solicitado</p>
            <?php endif; ?>
        </div>

        <!-- Request Service Form -->
        <h2 class="text-xl font-bold text-white mb-4">Solicitar Serviço</h2>
        
        <?php if (!empty($veiculos)): ?>
        <div class="bg-darker rounded-lg border border-gray-800 p-6">
            <form method="POST">
                <input type="hidden" name="action" value="request">
                
                <div class="space-y-4">
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Veículo *</label>
                        <select name="veiculo_id" required class="input-field w-full px-4 py-3 rounded-lg text-white">
                            <option value="">Selecione</option>
                            <?php foreach ($veiculos as $v): ?>
                            <option value="<?= $v['id'] ?>">
                                <?= formatPlate($v['placa']) ?> - <?= $v['marca'] ?> <?= $v['modelo'] ?> (<?= $v['ano'] ?>)
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Tipo de Serviço *</label>
                        <select name="tipo_servico" required class="input-field w-full px-4 py-3 rounded-lg text-white">
                            <option value="revisão">Revisão Geral</option>
                            <option value="freio">Freios</option>
                            <option value="motor">Motor</option>
                            <option value="suspensão">Suspensão</option>
                            <option value="elétrica">Elétrica</option>
                            <option value="troca_oleo">Troca de Óleo</option>
                            <option value="diagnóstico">Diagnóstico</option>
                            <option value="outro">Outro</option>
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-gray-400 text-sm mb-2">Descrição do Problema *</label>
                        <textarea name="descricao" required rows="4" 
                            class="input-field w-full px-4 py-3 rounded-lg text-white placeholder-gray-600"
                            placeholder="Descreva o problema ou serviço desejado..."></textarea>
                    </div>
                    
                    <button type="submit" class="w-full bg-accent hover:bg-blue-500 py-3 rounded-lg font-semibold text-white transition">
                        Solicitar Serviço
                    </button>
                </div>
            </form>
        </div>
        <?php else: ?>
        <div class="bg-darker rounded-lg border border-gray-800 p-6 text-center">
            <p class="text-gray-400 mb-4">Você não tem veículos cadastrados.</p>
            <p class="text-gray-500 text-sm">Para solicitar serviços, primeiro cadastre seu veículo na área do cliente.</p>
        </div>
        <?php endif; ?>

        <!-- Services Info -->
        <div class="mt-8 grid md:grid-cols-3 gap-4">
            <div class="bg-darker rounded-lg border border-gray-800 p-4">
                <svg class="w-8 h-8 text-accent mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 11-4 0m0 0a2 2 0 11-4 0m0 4V8m0 8a2 2 0 100 4m0-4a2 2 0 110-4"/>
                </svg>
                <h3 class="text-white font-semibold mb-1">Serviços Especializados</h3>
                <p class="text-gray-400 text-sm">Mecânicos experientes em diversas áreas.</p>
            </div>
            <div class="bg-darker rounded-lg border border-gray-800 p-4">
                <svg class="w-8 h-8 text-accent mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 10.97s9-4.38 9-10.97c0-.028-.001-.056-.003-.082z"/>
                </svg>
                <h3 class="text-white font-semibold mb-1">Garantia</h3>
                <p class="text-gray-400 text-sm">Serviços com garantia de qualidade.</p>
            </div>
            <div class="bg-darker rounded-lg border border-gray-800 p-4">
                <svg class="w-8 h-8 text-accent mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <h3 class="text-white font-semibold mb-1">Agilidade</h3>
                <p class="text-gray-400 text-sm">Atendimento rápido e eficiente.</p>
            </div>
        </div>
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