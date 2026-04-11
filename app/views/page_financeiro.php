<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Financeiro</h1>

<div class="bg-darker rounded-lg shadow mb-6 p-4 border border-gray-800">
    <form method="GET" class="flex flex-wrap gap-4 items-end">
        <input type="hidden" name="page" value="financeiro">
        <div>
            <label class="block text-sm text-gray-400 mb-1">Data Início</label>
            <input type="date" name="data_inicio" value="<?= $data_inicio ?>" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
        </div>
        <div>
            <label class="block text-sm text-gray-400 mb-1">Data Fim</label>
            <input type="date" name="data_fim" value="<?= $data_fim ?>" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
        </div>
        <button type="submit" class="bg-accent text-white px-4 py-2 rounded">Filtrar</button>
    </form>
</div>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Total OS no Período</div>
        <div class="text-3xl font-bold text-white"><?= $totais['total_os'] ?? 0 ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Faturamento Total</div>
        <div class="text-3xl font-bold text-green-400"><?= formatMoney($totais['faturamento'] ?? 0) ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Média por OS</div>
        <div class="text-3xl font-bold text-accent"><?= $totais['total_os'] > 0 ? formatMoney(($totais['faturamento'] ?? 0) / $totais['total_os']) : formatMoney(0) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-darker rounded-lg shadow border border-gray-800">
        <div class="p-4 border-b border-gray-800">
            <h2 class="text-lg font-semibold text-white">Venda Balcão</h2>
        </div>
        <div class="p-4">
            <form method="POST" class="space-y-3">
                <input type="hidden" name="action_form" value="financeiro_venda">
                <div class="grid grid-cols-1 gap-3">
                    <select name="peca_id" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                        <option value="">Selecione a peça</option>
                        <?php 
                        $db = db();
                        $stmt = $db->query("SELECT id, codigo, descricao, preco_venda, quantidade FROM pecas WHERE ativo = 1 AND quantidade > 0 ORDER BY descricao");
                        while ($p = $stmt->fetch()): ?>
                        <option value="<?= $p['id'] ?>"><?= sanitize($p['codigo']) ?> - <?= sanitize($p['descricao']) ?> (R$ <?= number_format($p['preco_venda'], 2, ',', '.') ?>) - Estoque: <?= $p['quantidade'] ?></option>
                        <?php endwhile; ?>
                    </select>
                    <div class="grid grid-cols-2 gap-3">
                        <input type="number" name="quantidade" value="1" min="1" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white" placeholder="Quantidade">
                        <select name="forma_pagamento" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                            <option value="dinheiro">Dinheiro</option>
                            <option value="pix">PIX</option>
                            <option value="cartao">Cartão</option>
                        </select>
                    </div>
                    <button type="submit" class="bg-green-600 hover:bg-green-500 text-white px-6 py-2 rounded">Finalizar Venda</button>
                </div>
            </form>
        </div>
    </div>
    
    <div class="bg-darker rounded-lg shadow border border-gray-800">
        <div class="p-4 border-b border-gray-800">
            <h2 class="text-lg font-semibold text-white">Faturamento por Dia</h2>
        </div>
        <div class="p-4 max-h-64 overflow-y-auto">
            <?php if (!empty($faturamento_diario)): ?>
            <table class="w-full text-sm">
                <thead>
                    <tr class="text-gray-500 border-b border-gray-800">
                        <th class="py-1 text-left">Data</th>
                        <th class="py-1 text-right">OS</th>
                        <th class="py-1 text-right">Valor</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($faturamento_diario as $fd): ?>
                    <tr class="border-b border-gray-800">
                        <td class="py-1 text-gray-300"><?= formatDate($fd['data']) ?></td>
                        <td class="py-1 text-right text-gray-400"><?= $fd['total_os'] ?></td>
                        <td class="py-1 text-right font-semibold text-accent"><?= formatMoney($fd['faturamento']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p class="text-gray-500 text-center">Nenhum faturamento no período</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Todas as OS Finalizadas no Período</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <?php 
        $stmt = db()->query("
            SELECT os.*, c.nome as cliente_nome, v.placa, v.modelo
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            WHERE DATE(os.data_entrada) BETWEEN ? AND ?
            AND os.status IN ('finalizado', 'entregue')
            ORDER BY os.data_entrada DESC
        ", [$data_inicio, $data_fim]);
        $todasOS = $stmt->fetchAll();
        ?>
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-800">
                    <th class="pb-2">OS</th>
                    <th class="pb-2">Data</th>
                    <th class="pb-2">Cliente</th>
                    <th class="pb-2">Veículo</th>
                    <th class="pb-2">Pagamento</th>
                    <th class="pb-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($todasOS as $os): ?>
                <tr class="border-b border-gray-800">
                    <td class="py-2 text-accent">#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-2"><?= formatDate($os['data_entrada']) ?></td>
                    <td class="py-2"><?= sanitize($os['cliente_nome']) ?></td>
                    <td class="py-2"><?= formatPlate($os['placa']) ?></td>
                    <td class="py-2"><?= ucfirst($os['forma_pagamento']) ?></td>
                    <td class="py-2 font-semibold text-accent"><?= formatMoney($os['valor_total']) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($todasOS)): ?>
                <tr><td colspan="6" class="py-4 text-center text-gray-500">Nenhuma OS finalizada no período</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>