<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-gray-800 mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm">OS em Andamento</div>
        <div class="text-3xl font-bold text-primary"><?= $osEmAndamento ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm">Peças com Estoque Baixo</div>
        <div class="text-3xl font-bold text-red-500"><?= $pecasEstoqueBaixo ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm">Faturamento Hoje</div>
        <div class="text-3xl font-bold text-green-600"><?= formatMoney($faturamentoDia) ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-gray-500 text-sm">Faturamento Mês</div>
        <div class="text-3xl font-bold text-green-600"><?= formatMoney($faturamentoMes) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <div class="text-gray-500 text-sm">Orçamento</div>
        <div class="text-2xl font-bold text-yellow-600"><?= $osCounts['orcamento'] ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <div class="text-gray-500 text-sm">Aprovado</div>
        <div class="text-2xl font-bold text-blue-600"><?= $osCounts['aprovado'] ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <div class="text-gray-500 text-sm">Em Execução</div>
        <div class="text-2xl font-bold text-orange-600"><?= $osCounts['em_execucao'] ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <div class="text-gray-500 text-sm">Finalizado</div>
        <div class="text-2xl font-bold text-purple-600"><?= $osCounts['finalizado'] ?></div>
    </div>
    <div class="bg-white rounded-lg shadow p-4 text-center">
        <div class="text-gray-500 text-sm">Entregue</div>
        <div class="text-2xl font-bold text-green-600"><?= $osCounts['entregue'] ?></div>
    </div>
</div>

<div class="bg-white rounded-lg shadow">
    <div class="p-4 border-b">
        <h2 class="text-lg font-semibold">Ordens de Serviço Recentes</h2>
    </div>
    <div class="p-4">
        <table class="w-full">
            <thead>
                <tr class="text-left text-gray-500 text-sm">
                    <th class="pb-2">OS</th>
                    <th class="pb-2">Cliente</th>
                    <th class="pb-2">Veículo</th>
                    <th class="pb-2">Status</th>
                    <th class="pb-2">Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($osRecentes as $os): ?>
                <tr class="border-t">
                    <td class="py-2">#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
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
                </tr>
                <?php endforeach; ?>
                <?php if (empty($osRecentes)): ?>
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Nenhuma OS encontrada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>