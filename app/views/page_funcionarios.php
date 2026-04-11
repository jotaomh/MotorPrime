<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Funcionários</h1>

<div class="bg-darker rounded-lg shadow mb-6 border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Novo Funcionário</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action_form" value="funcionarios_store">
            <div>
                <label class="block text-sm text-gray-400 mb-1">Nome *</label>
                <input type="text" name="nome" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Email *</label>
                <input type="email" name="email" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Senha *</label>
                <input type="password" name="senha" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
            </div>
            <div>
                <label class="block text-sm text-gray-400 mb-1">Cargo *</label>
                <select name="cargo" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white focus:border-accent focus:outline-none">
                    <option value="vendedor">Vendedor</option>
                    <option value="mecanico">Mecânico</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-accent hover:bg-accentLight text-white px-6 py-2 rounded font-semibold transition">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Lista de Funcionários</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b border-gray-800">
                    <th class="pb-2">Nome</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Cargo</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($funcionarios as $f): ?>
                <tr class="border-b border-gray-800">
                    <td class="py-2"><?= sanitize($f['nome']) ?></td>
                    <td class="py-2"><?= sanitize($f['email']) ?></td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs <?= $f['cargo'] === 'admin' ? 'bg-purple-900 text-purple-400' : ($f['cargo'] === 'mecanico' ? 'bg-blue-900 text-blue-400' : 'bg-green-900 text-green-400') ?>">
                            <?= ucfirst($f['cargo']) ?>
                        </span>
                    </td>
                    <td class="py-2">
                        <button onclick="editFuncionario(<?= $f['id'] ?>, '<?= sanitize($f['nome']) ?>', '<?= sanitize($f['email']) ?>', '<?= $f['cargo'] ?>')" class="text-accent hover:text-accentLight text-sm">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50">
    <div class="bg-darker border border-gray-700 rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4 text-white">Editar Funcionário</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="funcionarios_update">
            <input type="hidden" name="id" id="editId">
            <div class="space-y-3">
                <input type="text" name="nome" id="editNome" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <input type="email" name="email" id="editEmail" required class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                <select name="cargo" id="editCargo" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white">
                    <option value="vendedor">Vendedor</option>
                    <option value="mecanico">Mecânico</option>
                    <option value="admin">Administrador</option>
                </select>
                <input type="password" name="nova_senha" class="w-full bg-darkGray border border-gray-700 rounded px-3 py-2 text-white" placeholder="Nova senha (opcional)">
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-accent text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-700 text-white px-4 py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editFuncionario(id, nome, email, cargo) {
    document.getElementById('editId').value = id;
    document.getElementById('editNome').value = nome;
    document.getElementById('editEmail').value = email;
    document.getElementById('editCargo').value = cargo;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>