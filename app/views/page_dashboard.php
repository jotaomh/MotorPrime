<?php extract($dados); ?>
<h1 class="text-2xl font-bold text-white mb-6">Dashboard</h1>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">OS em Andamento</div>
        <div class="text-3xl font-bold text-accent"><?= $osEmAndamento ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Peças com Estoque Baixo</div>
        <div class="text-3xl font-bold text-red-400"><?= $pecasEstoqueBaixo ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Faturamento Hoje</div>
        <div class="text-3xl font-bold text-green-400"><?= formatMoney($faturamentoDia) ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-6 border border-gray-800">
        <div class="text-gray-500 text-sm">Faturamento Mês</div>
        <div class="text-3xl font-bold text-green-400"><?= formatMoney($faturamentoMes) ?></div>
    </div>
</div>

<div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-8">
    <div class="bg-darker rounded-lg shadow p-4 text-center border border-gray-800">
        <div class="text-gray-500 text-sm">Orçamento</div>
        <div class="text-2xl font-bold text-yellow-400"><?= $osCounts['orcamento'] ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-4 text-center border border-gray-800">
        <div class="text-gray-500 text-sm">Aprovado</div>
        <div class="text-2xl font-bold text-blue-400"><?= $osCounts['aprovado'] ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-4 text-center border border-gray-800">
        <div class="text-gray-500 text-sm">Em Execução</div>
        <div class="text-2xl font-bold text-orange-400"><?= $osCounts['em_execucao'] ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-4 text-center border border-gray-800">
        <div class="text-gray-500 text-sm">Finalizado</div>
        <div class="text-2xl font-bold text-purple-400"><?= $osCounts['finalizado'] ?></div>
    </div>
    <div class="bg-darker rounded-lg shadow p-4 text-center border border-gray-800">
        <div class="text-gray-500 text-sm">Entregue</div>
        <div class="text-2xl font-bold text-green-400"><?= $osCounts['entregue'] ?></div>
    </div>
</div>

<div class="bg-darker rounded-lg shadow border border-gray-800">
    <div class="p-4 border-b border-gray-800">
        <h2 class="text-lg font-semibold text-white">Ordens de Serviço Recentes</h2>
    </div>
    <div class="p-4 overflow-x-auto">
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
                <tr class="border-t border-gray-800">
                    <td class="py-2">#<?= str_pad($os['id'], 4, '0', STR_PAD_LEFT) ?></td>
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
                </tr>
                <?php endforeach; ?>
                <?php if (empty($osRecentes)): ?>
                <tr><td colspan="5" class="py-4 text-center text-gray-500">Nenhuma OS encontrada</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>