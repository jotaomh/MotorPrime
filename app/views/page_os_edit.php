<?php 
if (!isset($os)) {
    echo '<p class="text-red-500">OS não encontrada</p>';
    return;
}
extract($dados); 
?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Ordem de Serviço #<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></h1>

<div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-500 text-sm">Cliente</div>
        <div class="font-semibold"><?= sanitize($os['cliente_nome']) ?></div>
        <div class="text-sm"><?= formatPhone($os['cliente_telefone']) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-500 text-sm">Veículo</div>
        <div class="font-semibold"><?= formatPlate($os['placa']) ?></div>
        <div class="text-sm"><?= sanitize($os['marca']) ?> <?= sanitize($os['modelo']) ?> (<?= $os['ano'] ?>)</div>
    </div>
    <div class="bg-white rounded-lg shadow p-4">
        <div class="text-gray-500 text-sm">Status</div>
        <form method="POST" class="mt-1">
            <input type="hidden" name="action_form" value="os_update">
            <input type="hidden" name="id" value="<?= $os['id'] ?>">
            <input type="hidden" name="descricao" value="<?= sanitize($os['descricao'] ?? '') ?>">
            <input type="hidden" name="forma_pagamento" value="<?= $os['forma_pagamento'] ?>">
            <select name="status" onchange="this.form.submit()" class="border rounded px-2 py-1 w-full">
                <option value="orcamento" <?= $os['status'] === 'orcamento' ? 'selected' : '' ?>>Orçamento</option>
                <option value="aprovado" <?= $os['status'] === 'aprovado' ? 'selected' : '' ?>>Aprovado</option>
                <option value="em_execucao" <?= $os['status'] === 'em_execucao' ? 'selected' : '' ?>>Em Execução</option>
                <option value="finalizado" <?= $os['status'] === 'finalizado' ? 'selected' : '' ?>>Finalizado</option>
                <option value="entregue" <?= $os['status'] === 'entregue' ? 'selected' : '' ?>>Entregue</option>
            </select>
        </form>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-3">Adicionar Serviço</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="os_add_servico">
            <input type="hidden" name="os_id" value="<?= $os['id'] ?>">
            <div class="flex gap-2">
                <input type="text" name="descricao" placeholder="Descrição do serviço" required class="flex-1 border rounded px-3 py-2">
                <input type="number" name="valor" placeholder="Valor" step="0.01" required class="w-24 border rounded px-3 py-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Add</button>
            </div>
        </form>
        
        <div class="mt-4">
            <h4 class="text-sm text-gray-500 mb-2">Serviços</h4>
            <?php foreach ($servicos as $s): ?>
            <div class="flex justify-between items-center py-2 border-b">
                <span><?= sanitize($s['descricao']) ?></span>
                <div class="flex items-center gap-2">
                    <span class="font-semibold"><?= formatMoney($s['valor']) ?></span>
                    <a href="dashboard.php?page=os_edit&id=<?= $os['id'] ?>&action=delete_servico&sid=<?= $s['id'] ?>" class="text-red-500 text-sm" onclick="return confirm('Remover?')">×</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($servicos)): ?>
            <p class="text-gray-400 text-sm">Nenhum serviço adicionado</p>
            <?php endif; ?>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow p-4">
        <h3 class="font-semibold mb-3">Adicionar Peça</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="os_add_peca">
            <input type="hidden" name="os_id" value="<?= $os['id'] ?>">
            <div class="flex gap-2">
                <select name="peca_id" id="peca_select" required class="flex-1 border rounded px-3 py-2">
                    <option value="">Selecione</option>
                    <?php 
                    $db = db();
                    $stmt = $db->query("SELECT id, codigo, descricao, preco_venda, quantidade FROM pecas WHERE ativo = 1 AND quantidade > 0 ORDER BY descricao");
                    while ($p = $stmt->fetch()): ?>
                    <option value="<?= $p['id'] ?>"><?= sanitize($p['codigo']) ?> - <?= sanitize($p['descricao']) ?> (<?= $p['quantidade'] ?>)</option>
                    <?php endwhile; ?>
                </select>
                <input type="number" name="quantidade" value="1" min="1" class="w-16 border rounded px-3 py-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Add</button>
            </div>
        </form>
        
        <div class="mt-4">
            <h4 class="text-sm text-gray-500 mb-2">Peças</h4>
            <?php foreach ($pecas as $p): ?>
            <div class="flex justify-between items-center py-2 border-b">
                <span><?= sanitize($p['codigo']) ?> - <?= sanitize($p['peca_descricao']) ?></span>
                <div class="flex items-center gap-2">
                    <span class="text-sm">×<?= $p['quantidade'] ?></span>
                    <span class="font-semibold"><?= formatMoney($p['quantidade'] * $p['valor_unitario']) ?></span>
                    <a href="dashboard.php?page=os_edit&id=<?= $os['id'] ?>&action=delete_peca&pid=<?= $p['id'] ?>" class="text-red-500 text-sm" onclick="return confirm('Remover?')">×</a>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($pecas)): ?>
            <p class="text-gray-400 text-sm">Nenhuma peça adicionada</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow p-4 mb-6">
    <div class="flex flex-wrap gap-4 items-center">
        <div class="flex-1">
            <label class="block text-sm text-gray-600 mb-1">Descrição</label>
            <form method="POST" class="flex gap-2">
                <input type="hidden" name="action_form" value="os_update">
                <input type="hidden" name="id" value="<?= $os['id'] ?>">
                <input type="hidden" name="status" value="<?= $os['status'] ?>">
                <input type="hidden" name="forma_pagamento" value="<?= $os['forma_pagamento'] ?>">
                <input type="text" name="descricao" value="<?= sanitize($os['descricao'] ?? '') ?>" class="flex-1 border rounded px-3 py-2" placeholder="Descrição adicional">
                <button type="submit" class="bg-gray-500 text-white px-4 py-2 rounded">Salvar</button>
            </form>
        </div>
    </div>
</div>

<div class="bg-primary text-white rounded-lg shadow p-6">
    <div class="grid grid-cols-3 gap-4 text-center">
        <div>
            <div class="text-sm opacity-80">Mão de Obra</div>
            <div class="text-2xl font-bold"><?= formatMoney($os['valor_mao_obra']) ?></div>
        </div>
        <div>
            <div class="text-sm opacity-80">Peças</div>
            <div class="text-2xl font-bold"><?= formatMoney($os['valor_pecas']) ?></div>
        </div>
        <div>
            <div class="text-sm opacity-80">Total</div>
            <div class="text-3xl font-bold"><?= formatMoney($os['valor_total']) ?></div>
        </div>
    </div>
    
    <?php if ($os['status'] === 'finalizado' || $os['status'] === 'entregue'): ?>
    <div class="mt-4 pt-4 border-t border-white border-opacity-20">
        <div class="grid grid-cols-2 gap-4">
            <div>
                <div class="text-sm opacity-80">Forma de Pagamento</div>
                <form method="POST" class="mt-1">
                    <input type="hidden" name="action_form" value="os_update">
                    <input type="hidden" name="id" value="<?= $os['id'] ?>">
                    <input type="hidden" name="status" value="<?= $os['status'] ?>">
                    <input type="hidden" name="descricao" value="<?= sanitize($os['descricao'] ?? '') ?>">
                    <select name="forma_pagamento" onchange="this.form.submit()" class="bg-primary bg-opacity-20 border border-white border-opacity-30 rounded px-3 py-1 text-white">
                        <option value="dinheiro" <?= $os['forma_pagamento'] === 'dinheiro' ? 'selected' : '' ?>>Dinheiro</option>
                        <option value="pix" <?= $os['forma_pagamento'] === 'pix' ? 'selected' : '' ?>>PIX</option>
                        <option value="cartao" <?= $os['forma_pagamento'] === 'cartao' ? 'selected' : '' ?>>Cartão</option>
                    </select>
                </form>
            </div>
            <div>
                <div class="text-sm opacity-80">Data de Entrega</div>
                <div class="text-lg"><?= $os['data_entrega'] ? formatDate($os['data_entrega']) : '-' ?></div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<div class="mt-6">
    <a href="dashboard.php?page=os" class="text-primary hover:underline">← Voltar para lista de OS</a>
</div>