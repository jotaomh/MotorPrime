<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Veículos</h1>

<div class="bg-darker rounded-lg shadow mb-6 border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Novo Veículo</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <input type="hidden" name="action_form" value="veiculos_store">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cliente *</label>
                <select name="cliente_id" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
                    <option value="">Selecione</option>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= sanitize($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Placa *</label>
                <input type="text" name="placa" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white uppercase focus:border-accent focus:outline-none plate-mask" placeholder="ABC-1234" maxlength="8">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Marca *</label>
                <input type="text" name="marca" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Modelo *</label>
                <input type="text" name="modelo" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Ano *</label>
                <input type="number" name="ano" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cor</label>
                <input type="text" name="cor" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">KM</label>
                <input type="number" name="km" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Observações</label>
                <textarea name="observacoes" rows="1" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none"></textarea>
            </div>
            <div class="md:col-span-3">
                <button type="submit" class="bg-accent hover:bg-accentLight text-white px-6 py-2 rounded font-semibold transition">Cadastrar Veículo</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Lista de Veículos</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-800">
                    <th class="pb-2">Placa</th>
                    <th class="pb-2">Marca/Modelo</th>
                    <th class="pb-2">Ano</th>
                    <th class="pb-2">Cor</th>
                    <th class="pb-2">KM</th>
                    <th class="pb-2">Cliente</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($veiculos as $v): ?>
                <tr class="border-b border-gray-800">
                    <td class="py-2 font-mono text-accent"><?= formatPlate($v['placa']) ?></td>
                    <td class="py-2"><?= sanitize($v['marca']) ?> <?= sanitize($v['modelo']) ?></td>
                    <td class="py-2"><?= $v['ano'] ?></td>
                    <td class="py-2"><?= sanitize($v['cor'] ?? '-') ?></td>
                    <td class="py-2"><?= number_format($v['km'], 0, ',', '.') ?></td>
                    <td class="py-2"><?= sanitize($v['cliente_nome']) ?></td>
                    <td class="py-2">
                        <button onclick="editVeiculo(<?= $v['id'] ?>, <?= $v['cliente_id'] ?>, '<?= sanitize($v['placa']) ?>', '<?= sanitize($v['marca']) ?>', '<?= sanitize($v['modelo']) ?>', <?= $v['ano'] ?>, '<?= sanitize($v['cor'] ?? '') ?>', <?= $v['km'] ?>, '<?= sanitize($v['observacoes'] ?? '') ?>')" class="text-accent hover:text-accentLight text-sm">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($veiculos)): ?>
                <tr><td colspan="7" class="py-4 text-center text-gray-500">Nenhum veículo encontrado</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
    <div class="bg-darker border border-gray-700 rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4 text-white">Editar Veículo</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="veiculos_update">
            <input type="hidden" name="id" id="editId">
            <div class="grid grid-cols-2 gap-3">
                <select name="cliente_id" id="editClienteId" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                    <option value="">Selecione</option>
                    <?php foreach ($clientes as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= sanitize($c['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="placa" id="editPlaca" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white uppercase">
                <input type="text" name="marca" id="editMarca" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="text" name="modelo" id="editModelo" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="number" name="ano" id="editAno" required class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="text" name="cor" id="editCor" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="number" name="km" id="editKm" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="text" name="observacoes" id="editObs" class="bg-darkGray border border-gray-700 rounded px-3 py-2 text-white col-span-2">
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-accent text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-700 text-white px-4 py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editVeiculo(id, clienteId, placa, marca, modelo, ano, cor, km, obs) {
    document.getElementById('editId').value = id;
    document.getElementById('editClienteId').value = clienteId;
    document.getElementById('editPlaca').value = placa;
    document.getElementById('editMarca').value = marca;
    document.getElementById('editModelo').value = modelo;
    document.getElementById('editAno').value = ano;
    document.getElementById('editCor').value = cor;
    document.getElementById('editKm').value = km;
    document.getElementById('editObs').value = obs;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>