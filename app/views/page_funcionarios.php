<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Funcionários</h1>

<div class="bg-white rounded-lg shadow mb-6">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Novo Funcionário</h2>
    </div>
    <div class="p-4">
        <form method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <input type="hidden" name="action_form" value="funcionarios_store">
            <div>
                <label class="block text-sm text-gray-600 mb-1">Nome *</label>
                <input type="text" name="nome" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Email *</label>
                <input type="email" name="email" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Senha *</label>
                <input type="password" name="senha" required class="w-full border rounded px-3 py-2">
            </div>
            <div>
                <label class="block text-sm text-gray-600 mb-1">Cargo *</label>
                <select name="cargo" required class="w-full border rounded px-3 py-2">
                    <option value="vendedor">Vendedor</option>
                    <option value="mecanico">Mecânico</option>
                    <option value="admin">Administrador</option>
                </select>
            </div>
            <div class="md:col-span-2">
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded hover:bg-secondary">Cadastrar</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Lista de Funcionários</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-2">Nome</th>
                    <th class="pb-2">Email</th>
                    <th class="pb-2">Cargo</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($funcionarios as $f): ?>
                <tr class="border-b">
                    <td class="py-2"><?= sanitize($f['nome']) ?></td>
                    <td class="py-2"><?= sanitize($f['email']) ?></td>
                    <td class="py-2">
                        <span class="px-2 py-1 rounded text-xs <?= $f['cargo'] === 'admin' ? 'bg-purple-100 text-purple-800' : ($f['cargo'] === 'mecanico' ? 'bg-blue-100 text-blue-800' : 'bg-green-100 text-green-800') ?>">
                            <?= ucfirst($f['cargo']) ?>
                        </span>
                    </td>
                    <td class="py-2">
                        <button onclick="editFuncionario(<?= $f['id'] ?>, '<?= sanitize($f['nome']) ?>', '<?= sanitize($f['email']) ?>', '<?= $f['cargo'] ?>')" class="text-blue-600 hover:underline text-sm">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md">
        <h3 class="text-lg font-bold mb-4">Editar Funcionário</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="funcionarios_update">
            <input type="hidden" name="id" id="editId">
            <div class="space-y-3">
                <input type="text" name="nome" id="editNome" required class="w-full border rounded px-3 py-2">
                <input type="email" name="email" id="editEmail" required class="w-full border rounded px-3 py-2">
                <select name="cargo" id="editCargo" class="w-full border rounded px-3 py-2">
                    <option value="vendedor">Vendedor</option>
                    <option value="mecanico">Mecânico</option>
                    <option value="admin">Administrador</option>
                </select>
                <input type="password" name="nova_senha" class="w-full border rounded px-3 py-2" placeholder="Nova senha (opcional)">
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
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