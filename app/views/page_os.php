<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Ordens de Serviço</h1>

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Nova OS</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="action_form" value="os_create">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Cliente *</label>
                <select name="cliente_id" id="os_cliente" required class="w-full border rounded px-3 py-2" onchange="loadVeiculos(this.value)">
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
                <label class="block text-sm text-gray-600 mb-1">Veículo *</label>
                <select name="veiculo_id" id="os_veiculo" required class="w-full border rounded px-3 py-2">
                    <option value="">Selecione o cliente primeiro</option>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Descrição</label>
                <input type="text" name="descricao" class="w-full border rounded px-3 py-2" placeholder="Descrição do serviço">
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-secondary">Criar OS</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 border-b flex flex-wrap gap-2">
        <a href="dashboard.php?page=os" class="px-3 py-1 rounded text-sm <?= empty($_GET['status']) ? 'bg-primary text-white' : 'bg-gray-200' ?>">Todos</a>
        <a href="dashboard.php?page=os&status=orcamento" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'orcamento' ? 'bg-yellow-500 text-white' : 'bg-gray-200' ?>">Orçamento</a>
        <a href="dashboard.php?page=os&status=aprovado" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'aprovado' ? 'bg-blue-500 text-white' : 'bg-gray-200' ?>">Aprovado</a>
        <a href="dashboard.php?page=os&status=em_execucao" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'em_execucao' ? 'bg-orange-500 text-white' : 'bg-gray-200' ?>">Em Execução</a>
        <a href="dashboard.php?page=os&status=finalizado" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'finalizado' ? 'bg-purple-500 text-white' : 'bg-gray-200' ?>">Finalizado</a>
        <a href="dashboard.php?page=os&status=entregue" class="px-3 py-1 rounded text-sm <?= ($_GET['status'] ?? '') === 'entregue' ? 'bg-green-500 text-white' : 'bg-gray-200' ?>">Entregue</a>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
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
                <tr class="border-b">
                    <td class="py-2 font-mono">#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-2"><?= formatDate($os['data_entrada']) ?></td>
                    <td class="py-2"><?= sanitize($os['cliente_nome']) ?></td>
                    <td class="py-2"><?= formatPlate($os['placa']) ?> - <?= sanitize($os['modelo']) ?></td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs <?= 
                            $os['status'] === 'orcamento' ? 'bg-yellow-100 text-yellow-800' : 
                            ($os['status'] === 'aprovado' ? 'bg-blue-100 text-blue-800' : 
                            ($os['status'] === 'em_execucao' ? 'bg-orange-100 text-orange-800' : 
                            ($os['status'] === 'finalizado' ? 'bg-purple-100 text-purple-800' : 'bg-green-100 text-green-800')))
                        ?>">
                            <?= ucfirst(str_replace('_', ' ', $os['status'])) ?>
                        </span>
                    </td>
                    <td class="py-2 font-semibold"><?= formatMoney($os['valor_total']) ?></td>
                    <td class="py-2">
                        <a href="dashboard.php?page=os_edit&id=<?= $os['id'] ?>" class="text-blue-600 hover:underline text-sm">Editar</a>
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