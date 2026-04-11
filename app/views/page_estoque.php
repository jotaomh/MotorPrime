<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Estoque de Peças</h1>

<?php if (!empty($estoqueBaixo)): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
    <h3 class="font-semibold text-red-800 mb-2">⚠️ Estoque Baixo</h3>
    <div class="grid grid-cols-2 md:grid-cols-4 gap-2">
        <?php foreach ($estoqueBaixo as $p): ?>
        <div class="bg-white rounded p-2 text-sm">
            <div class="font-medium"><?= sanitize($p['codigo']) ?></div>
            <div class="text-gray-500"><?= sanitize($p['descricao']) ?></div>
            <div class="text-red-600 font-bold"><?= $p['quantidade'] ?> un</div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Nova Peça</h2>
        </div>
        <div class="p-4">
            <form method="POST" class="space-y-3">
                <input type="hidden" name="action_form" value="estoque_store">
                <div class="grid grid-cols-2 gap-3">
                    <input type="text" name="codigo" required placeholder="Código" class="border rounded px-3 py-2">
                    <input type="text" name="descricao" required placeholder="Descrição" class="border rounded px-3 py-2">
                    <input type="text" name="marca" placeholder="Marca" class="border rounded px-3 py-2">
                    <input type="text" name="categoria" placeholder="Categoria" class="border rounded px-3 py-2">
                    <input type="number" name="preco_custo" step="0.01" placeholder="Preço custo" class="border rounded px-3 py-2">
                    <input type="number" name="preco_venda" step="0.01" placeholder="Preço venda" class="border rounded px-3 py-2">
                    <input type="number" name="quantidade" placeholder="Quantidade" class="border rounded px-3 py-2">
                    <input type="number" name="estoque_minimo" value="5" placeholder="Estoque mínimo" class="border rounded px-3 py-2">
                    <select name="fornecedor_id" class="border rounded px-3 py-2">
                        <option value="">Fornecedor</option>
                        <?php foreach ($fornecedores as $f): ?>
                        <option value="<?= $f['id'] ?>"><?= sanitize($f['nome']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="bg-primary text-white px-6 py-2 rounded">Cadastrar</button>
            </form>
        </div>
    </div>
    
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b">
            <h2 class="text-lg font-semibold">Entrada de Peças</h2>
        </div>
        <div class="p-4">
            <form method="POST" class="space-y-3">
                <input type="hidden" name="action_form" value="estoque_entrada">
                <div class="grid grid-cols-2 gap-3">
                    <select name="peca_id" required class="border rounded px-3 py-2">
                        <option value="">Selecione a peça</option>
                        <?php foreach ($pecas as $p): ?>
                        <option value="<?= $p['id'] ?>"><?= sanitize($p['codigo']) ?> - <?= sanitize($p['descricao']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="quantidade" required placeholder="Quantidade" class="border rounded px-3 py-2">
                    <input type="number" name="preco_unitario" step="0.01" placeholder="Preço unitário" class="border rounded px-3 py-2">
                    <input type="text" name="observacoes" placeholder="Observações" class="border rounded px-3 py-2">
                </div>
                <button type="submit" class="bg-green-600 text-white px-6 py-2 rounded">Registrar Entrada</button>
            </form>
        </div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Lista de Peças</h2>
    </div>
    <div class="p-4 overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm border-b">
                    <th class="pb-2">Código</th>
                    <th class="pb-2">Descrição</th>
                    <th class="pb-2">Marca</th>
                    <th class="pb-2">Categoria</th>
                    <th class="pb-2">Custo</th>
                    <th class="pb-2">Venda</th>
                    <th class="pb-2">Estoque</th>
                    <th class="pb-2">Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pecas as $p): ?>
                <tr class="border-b <?= $p['quantidade'] <= $p['estoque_minimo'] ? 'bg-red-50' : '' ?>">
                    <td class="py-2 font-mono"><?= sanitize($p['codigo']) ?></td>
                    <td class="py-2"><?= sanitize($p['descricao']) ?></td>
                    <td class="py-2"><?= sanitize($p['marca'] ?? '-') ?></td>
                    <td class="py-2"><?= sanitize($p['categoria'] ?? '-') ?></td>
                    <td class="py-2"><?= formatMoney($p['preco_custo']) ?></td>
                    <td class="py-2 font-semibold"><?= formatMoney($p['preco_venda']) ?></td>
                    <td class="py-2">
                        <span class="<?= $p['quantidade'] <= $p['estoque_minimo'] ? 'text-red-600 font-bold' : '' ?>"><?= $p['quantidade'] ?></span>
                    </td>
                    <td class="py-2">
                        <button onclick="editPeca(<?= $p['id'] ?>, '<?= sanitize($p['codigo']) ?>', '<?= sanitize($p['descricao']) ?>', '<?= sanitize($p['marca'] ?? '') ?>', '<?= sanitize($p['categoria'] ?? '') ?>', <?= $p['preco_custo'] ?>, <?= $p['preco_venda'] ?>, <?= $p['estoque_minimo'] ?>, '<?= $p['fornecedor_id'] ?? '' ?>')" class="text-blue-600 hover:underline text-sm">Editar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($pecas)): ?>
                <tr><td colspan="8" class="py-4 text-center text-gray-500">Nenhuma peça encontrada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-lg">
        <h3 class="text-lg font-bold mb-4">Editar Peça</h3>
        <form method="POST">
            <input type="hidden" name="action_form" value="estoque_update">
            <input type="hidden" name="id" id="editId">
            <div class="grid grid-cols-2 gap-3">
                <input type="text" name="codigo" id="editCodigo" required class="border rounded px-3 py-2">
                <input type="text" name="descricao" id="editDescricao" required class="border rounded px-3 py-2">
                <input type="text" name="marca" id="editMarca" class="border rounded px-3 py-2">
                <input type="text" name="categoria" id="editCategoria" class="border rounded px-3 py-2">
                <input type="number" name="preco_custo" id="editPrecoCusto" step="0.01" class="border rounded px-3 py-2">
                <input type="number" name="preco_venda" id="editPrecoVenda" step="0.01" class="border rounded px-3 py-2">
                <input type="number" name="estoque_minimo" id="editEstoqueMinimo" class="border rounded px-3 py-2">
                <select name="fornecedor_id" id="editFornecedor" class="border rounded px-3 py-2">
                    <option value="">Fornecedor</option>
                    <?php foreach ($fornecedores as $f): ?>
                    <option value="<?= $f['id'] ?>"><?= sanitize($f['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mt-4 flex gap-2">
                <button type="submit" class="bg-primary text-white px-4 py-2 rounded">Salvar</button>
                <button type="button" onclick="closeModal()" class="bg-gray-300 px-4 py-2 rounded">Cancelar</button>
            </div>
        </form>
    </div>
</div>

<script>
function editPeca(id, codigo, descricao, marca, categoria, precoCusto, precoVenda, estoqueMinimo, fornecedorId) {
    document.getElementById('editId').value = id;
    document.getElementById('editCodigo').value = codigo;
    document.getElementById('editDescricao').value = descricao;
    document.getElementById('editMarca').value = marca;
    document.getElementById('editCategoria').value = categoria;
    document.getElementById('editPrecoCusto').value = precoCusto;
    document.getElementById('editPrecoVenda').value = precoVenda;
    document.getElementById('editEstoqueMinimo').value = estoqueMinimo;
    document.getElementById('editFornecedor').value = fornecedorId;
    document.getElementById('editModal').classList.remove('hidden');
}
function closeModal() {
    document.getElementById('editModal').classList.add('hidden');
}
</script>