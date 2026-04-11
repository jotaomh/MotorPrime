<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Clientes</h1>

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 border-b flex justify-between items-center">
        <h2 class="text-lg font-semibold">Novo Cliente</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action_form" value="clientes_store">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nome *</label>
                <input type="text" name="nome" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">CPF</label>
                <input type="text" name="cpf" class="w-full border rounded px-3 py-2 cpf-mask" placeholder="000.000.000-00">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">CNPJ</label>
                <input type="text" name="cnpj" class="w-full border rounded px-3 py-2 cnpj-mask" placeholder="00.000.000/0001-00">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Telefone *</label>
                <input type="text" name="telefone" required class="w-full border rounded px-3 py-2 phone-mask" placeholder="(00) 00000-0000">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email</label>
                <input type="email" name="email" class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Endereço</label>
                <input type="text" name="endereco" class="w-full border rounded px-3 py-2">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm text-gray-600 mb-1">Observações</label>
                <textarea name="observacoes" rows="2" class="w-full border rounded px-3 py-2"></textarea>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-secondary">Cadastrar Cliente</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Lista de Clientes</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-2">Nome</th>
                    <th class="pb-2">CPF/CNPJ</th>
                    <th class="pb-2">Telefone</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clientes as $c): ?>
                <tr class="border-b">
                    <td class="py-2"><?= sanitize($c['nome']) ?></td>
                    <td class="py-2"><?= !empty($c['cpf']) ? formatCPF($c['cpf']) : (!empty($c['cnpj']) ? formatCNPJ($c['cnpj']) : '-') ?></td>
                    <td class="py-2"><?= formatPhone($c['telefone']) ?></td>
                    <td class="py-2"><?= sanitize($c['email'] ?? '-') ?></td>
                    <td class="py-2">
                        <button onclick="editCliente(<?= $c['id'] ?>, '<?= sanitize($c['nome']) ?>', '<?= sanitize($c['cpf'] ?? '') ?>', '<?= sanitize($c['cnpj'] ?? '') ?>', '<?= sanitize($c['telefone']) ?>', '<?= sanitize($c['email'] ?? '') ?>', '<?= sanitize($c['endereco'] ?? '') ?>', '<?= sanitize($c['observacoes'] ?? '') ?>')" class="text-blue-600 hover:underline text-sm">Editar</button>
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

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Editar Cliente</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="clientes_update">
            <input type="hidden" name="id" id="editId">
            <div class="space-y-3">
                <input type="text" name="nome" id="editNome" required class="w-full border rounded px-3 py-2" placeholder="Nome">
                <input type="text" name="cpf" id="editCpf" class="w-full border rounded px-3 py-2 cpf-mask" placeholder="CPF">
                <input type="text" name="cnpj" id="editCnpj" class="w-full border rounded px-3 py-2 cnpj-mask" placeholder="CNPJ">
                <input type="text" name="telefone" id="editTelefone" required class="w-full border rounded px-3 py-2 phone-mask">
                <input type="email" name="email" id="editEmail" class="w-full border rounded px-3 py-2">
                <input type="text" name="endereco" id="editEndereco" class="w-full border rounded px-3 py-2">
                <textarea name="observacoes" id="editObs" class="w-full border rounded px-3 py-2" rows="2"></textarea>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
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