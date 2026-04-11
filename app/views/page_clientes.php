<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Clientes</h1>

<div class="bg-darker rounded-lg shadow mb-6 border border-gray-800">
    <div class="p-4 border-b border-gray-800 flex justify-between items-center">
        <h2 class="text-lg font-semibold text-white">Novo Cliente</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action_form" value="clientes_store">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Nome *</label>
                <input type="text" name="nome" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">CPF</label>
                <input type="text" name="cpf" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none cpf-mask">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">CNPJ</label>
                <input type="text" name="cnpj" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none cnpj-mask">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Telefone *</label>
                <input type="text" name="telefone" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none phone-mask">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Email</label>
                <input type="email" name="email" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Endereço</label>
                <input type="text" name="endereco" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-400 mb-1">Observações</label>
                <textarea name="observacoes" rows="2" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none"></textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-accent hover:bg-accentLight text-white px-6 py-2 rounded font-semibold transition">Cadastrar Cliente</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Lista de Clientes</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-800">
                    <th class="pb-2">Nome</th>
                    <th class="pb-2">CPF/CNPJ</th>
                    <th class="pb-2">Telefone</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr class="border-b border-gray-800">
                    <td class="py-2"><?= sanitize($c['nome']) ?></td>
                    <td class="py-2"><?= !empty($c['cpf']) ? formatCPF($c['cpf']) : (!empty($c['cnpj']) ? formatCNPJ($c['cnpj']) : '-') ?></td>
                    <td class="py-2"><?= formatPhone($c['telefone']) ?></td>
                    <td class="py-2"><?= sanitize($c['email'] ?? '-') ?></td>
                    <td class="py-2">
                        <button onclick="editCliente(<?= $c['id'] ?>, '<?= sanitize($c['nome']) ?>', '<?= sanitize($c['cpf'] ?? '') ?>', '<?= sanitize($c['cnpj'] ?? '') ?>', '<?= sanitize($c['telefone']) ?>', '<?= sanitize($c['email'] ?? '') ?>', '<?= sanitize($c['endereco'] ?? '') ?>', '<?= sanitize($c['observacoes'] ?? '') ?>')" class="text-accent hover:text-accentLight text-sm">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($clientes)): ?>
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Nenhum cliente encontrado</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
    <div class="bg-darker border border-gray-700 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4 text-white">Editar Cliente</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="clientes_update">
            <input type="hidden" name="id" id="editId">
            <div class="space-y-3">
                <input type="text" name="nome" id="editNome" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="text" name="cpf" id="editCpf" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white cpf-mask">
                <input type="text" name="cnpj" id="editCnpj" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white cnpj-mask">
                <input type="text" name="telefone" id="editTelefone" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white phone-mask">
                <input type="email" name="email" id="editEmail" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="text" name="endereco" id="editEndereco" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <textarea name="observacoes" id="editObs" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white" rows="2"></textarea>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-accent text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-700 text-white px-4 py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editCliente(id, nome, cpf, cnpj, telefone, email, endereco, obs) {
    document.getElementById('editId').value = id;
    document.getElementById('editNome').value = nome;
    document.getElementById('editCpf').value = cpf;
    document.getElementById('editCnpj').value = cnpj;
    document.getElementById('editTelefone').value = telefone;
    document.getElementById('editEmail').value = email;
    document.getElementById('editEndereco').value = endereco;
    document.getElementById('editObs').value = obs;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>