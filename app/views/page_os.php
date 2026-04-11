<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Ordens de Serviço</h1>

<div class="bg-darker rounded-lg shadow mb-6 border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Nova OS</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="action_form" value="os_create">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cliente *</label>
                <select name="cliente_id" id="os_cliente" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent" onchange="loadVeiculos(this.value)">
                    <option value="">Selecione</option>
                    <?php 
                    $db = db();
                    $stmt = $db->query("SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome");
                    while ($c = $stmt->fetch()): ?>
                    <option value="<?= $c['id'] ?>"><?= sanitize($c['nome']) ?></option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Veículo *</label>
                <select name="veiculo_id" id="os_veiculo" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent">
                    <option value="">Selecione o cliente primeiro</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Descrição</label>
                <input type="text" name="descricao" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent" placeholder="Descrição do serviço">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="bg-accent hover:bg-accentLight text-white px-6 py-2 rounded font-semibold transition">Criar OS</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-darker rounded-lg shadow mb-6 border border-gray-800">
    <div class="p-4 border-b border-gray-800 flex flex-wrap gap-2">
        <a href="dashboard.php?page=os" class="px-3 py-1 rounded text-sm <?= empty($_GET['status']) ? 'bg-accent text-white' : 'bg-darkMid text-gray-400' ?>">Todos</a>
        <a href="dashboard.php?page=os&status=orcamento" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'orcamento' ? 'bg-yellow-600 text-white' : 'bg-darkMid text-gray-400' ?>">Orçamento</a>
        <a href="dashboard.php?page=os&status=aprovado" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'aprovado' ? 'bg-blue-600 text-white' : 'bg-darkMid text-gray-400' ?>">Aprovado</a>
        <a href="dashboard.php?page=os&status=em_execucao" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'em_execucao' ? 'bg-orange-600 text-white' : 'bg-darkMid text-gray-400' ?>">Em Execução</a>
        <a href="dashboard.php?page=os&status=finalizado" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'finalizado' ? 'bg-purple-600 text-white' : 'bg-darkMid text-gray-400' ?>">Finalizado</a>
        <a href="dashboard.php?page=os&status=entregue" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'entregue' ? 'bg-green-600 text-white' : 'bg-darkMid text-gray-400' ?>">Entregue</a>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-800">
                    <th class="pb-2">OS</th>
                    <th class="pb-2">Data</th>
                    <th class="pb-2">Cliente</th>
                    <th class="pb-2">Veículo</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Total</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($ordens as $os): ?>
                <tr class="border-b border-gray-800">
                    <td class="py-2 font-mono text-accent">#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-2"><?= formatDate($os['data_entrada']) ?></td>
                    <td class="py-2"><?= sanitize($os['cliente_nome']) ?></td>
                    <td class="py-2"><?= formatPlate($os['placa']) ?> - <?= sanitize($os['modelo']) ?></td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs <?= 
                            $os['status'] === 'orcamento' ? 'bg-yellow-900 text-yellow-400' : 
                            ($os['status'] === 'aprovado' ? 'bg-blue-900 text-blue-400' : 
                            ($os['status'] === 'em_execucao' ? 'bg-orange-900 text-orange-400' : 
                            ($os['status'] === 'finalizado' ? 'bg-purple-900 text-purple-400' : 'bg-green-900 text-green-400')))
                        ?>">
                            <?= ucfirst(str_replace('_', ' ', $os['status'])) ?>
                        </span>
                    </td>
                    <td class="py-2 font-semibold text-accent"><?= formatMoney($os['valor_total']) ?></td>
                    <td class="py-2">
                        <a href="dashboard.php?page=os_edit&id=<?= $os['id'] ?>" class="text-accent hover:text-accentLight text-sm">Editar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($ordens)): ?>
                <tr><td colspan="7" class="py-4 text-center text-gray-500">Nenhuma OS encontrada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function loadVeiculos(clienteId) {
    const select = document.getElementById('os_veiculo');
    if (!clienteId) {
        select.innerHTML = '<option value="">Selecione o cliente primeiro</option>';
        return;
    }
    fetch('api.php?action=veiculos&cliente_id=' + clienteId)
        .then(r => r.json())
        .then(data => {
            select.innerHTML = '<option value="">Selecione</option>';
            data.forEach(v => {
                select.innerHTML += '<option value="' + v.id + '">' + v.placa + ' - ' + v.marca + ' ' + v.modelo + '</option>';
            });
        });
}
</script>