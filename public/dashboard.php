<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

$allowedPages = ['dashboard', 'clientes', 'veiculos', 'funcionarios', 'fornecedores', 'os', 'os_edit', 'os_view', 'estoque', 'financeiro', 'pedidos', 'tokens'];
if (!in_array($page, $allowedPages)) {
    http_response_code(404);
    die('Página não encontrada');
}

$dados = [];
$db = db();
switch ($page) {
    case 'dashboard':
        $osCounts = ['orcamento' => 0, 'aprovado' => 0, 'em_execucao' => 0, 'finalizado' => 0, 'entregue' => 0];
        $stmt = $db->query("SELECT status, COUNT(*) as total FROM ordens_servico WHERE ativo = 1 GROUP BY status");
        while ($row = $stmt->fetch()) {
            if (isset($osCounts[$row['status']])) {
                $osCounts[$row['status']] = (int)$row['total'];
            }
        }
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM ordens_servico WHERE ativo = 1 AND status IN ('orcamento', 'aprovado', 'em_execucao', 'finalizado')");
        $osEmAndamento = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM pecas WHERE quantidade <= estoque_minimo AND ativo = 1");
        $pecasEstoqueBaixo = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico WHERE ativo = 1 AND (data_entrega = CURDATE() OR (data_fim = CURDATE() AND status IN ('finalizado', 'entregue'))) AND status IN ('finalizado', 'entregue')");
        $faturamentoDia = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico WHERE ativo = 1 AND MONTH(COALESCE(data_entrega, data_fim, data_entrada)) = MONTH(CURDATE()) AND YEAR(COALESCE(data_entrega, data_fim, data_entrada)) = YEAR(CURDATE()) AND status IN ('finalizado', 'entregue')");
        $faturamentoMes = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT os.*, c.nome as cliente_nome, v.placa, v.modelo FROM ordens_servico os JOIN clientes c ON os.cliente_id = c.id JOIN veiculos v ON os.veiculo_id = v.id WHERE os.ativo = 1 ORDER BY os.created_at DESC LIMIT 5");
        $osRecentes = $stmt->fetchAll();
        
        $dados = compact('osCounts', 'osEmAndamento', 'pecasEstoqueBaixo', 'faturamentoDia', 'faturamentoMes', 'osRecentes');
        break;
        
    case 'clientes':
        $stmt = $db->query("SELECT * FROM clientes WHERE ativo = 1 ORDER BY nome");
        $clientes = $stmt->fetchAll();
        $dados = compact('clientes');
        break;
        
    case 'veiculos':
        $stmt = $db->query("SELECT v.*, c.nome as cliente_nome FROM veiculos v JOIN clientes c ON v.cliente_id = c.id WHERE v.ativo = 1 ORDER BY v.placa");
        $veiculos = $stmt->fetchAll();
        $stmt = $db->query("SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome");
        $clientes = $stmt->fetchAll();
        $dados = compact('veiculos', 'clientes');
        break;
        
    case 'funcionarios':
        if (!isAdmin()) {
            flash('error', 'Acesso restrito');
            redirect('dashboard.php');
        }
        $stmt = $db->query("SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome");
        $funcionarios = $stmt->fetchAll();
        $dados = compact('funcionarios');
        break;
        
    case 'fornecedores':
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        $dados = compact('fornecedores');
        break;
        
    case 'os':
        $status = $_GET['status'] ?? '';
        $where = "WHERE os.ativo = 1";
        $params = [];
        if (!empty($status) && in_array($status, ['orcamento', 'aprovado', 'em_execucao', 'finalizado', 'entregue'])) {
            $where .= " AND os.status = ?";
            $params[] = $status;
        }
        $stmt = $db->query("
            SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                   v.placa, v.marca, v.modelo
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            $where
            ORDER BY os.created_at DESC
        ", $params);
        $ordens = $stmt->fetchAll();
        $dados = compact('ordens');
        break;
        
    case 'os_edit':
    case 'os_view':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $stmt = $db->query("
                SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, c.cpf as cliente_cpf,
                       v.placa, v.marca, v.modelo, v.ano, v.cor
                FROM ordens_servico os
                JOIN clientes c ON os.cliente_id = c.id
                JOIN veiculos v ON os.veiculo_id = v.id
                WHERE os.id = ?
            ", [$id]);
            $os = $stmt->fetch();
            
            if ($os) {
                $stmt = $db->query("SELECT * FROM servicos_os WHERE os_id = ?", [$id]);
                $servicos = $stmt->fetchAll();
                $stmt = $db->query("
                    SELECT po.*, p.codigo, p.descricao as peca_descricao, p.marca
                    FROM pecas_os po
                    JOIN pecas p ON po.peca_id = p.id
                    WHERE po.os_id = ?
                ", [$id]);
                $pecas = $stmt->fetchAll();
                $dados = compact('os', 'servicos', 'pecas');
            }
        }
        break;
        
    case 'estoque':
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.ativo = 1
            ORDER BY p.descricao
        ");
        $pecas = $stmt->fetchAll();
        
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.quantidade <= p.estoque_minimo AND p.ativo = 1
            ORDER BY p.quantidade ASC
        ");
        $estoqueBaixo = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        
        $dados = compact('pecas', 'estoqueBaixo', 'fornecedores');
        break;
        
    case 'financeiro':
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $stmt = $db->query("
            SELECT COALESCE(data_entrega, data_fim, data_entrada) as data_ref, COUNT(*) as total_os, SUM(valor_total) as faturamento
            FROM ordens_servico
            WHERE COALESCE(data_entrega, data_fim, data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue') AND ativo = 1
            GROUP BY data_ref
            ORDER BY data_ref DESC
        ", [$data_inicio, $data_fim]);
        $faturamento_diario = $stmt->fetchAll();
        
        $stmt = $db->query("
            SELECT COUNT(*) as total_os, COALESCE(SUM(valor_total), 0) as faturamento
            FROM ordens_servico
            WHERE COALESCE(data_entrega, data_fim, data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue') AND ativo = 1
        ", [$data_inicio, $data_fim]);
        $totais = $stmt->fetch();
        
        $stmt = $db->query("
            SELECT COUNT(*) as total_vendas, COALESCE(SUM(valor_total), 0) as total_faturado
            FROM vendas
            WHERE DATE(created_at) BETWEEN ? AND ?
        ", [$data_inicio, $data_fim]);
        $vendas_balco = $stmt->fetch();
        
        $dados = compact('faturamento_diario', 'totais', 'vendas_balco', 'data_inicio', 'data_fim');
        break;
}

extract($dados);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100">
    <?php include __DIR__ . '/../app/views/layouts/header.php'; ?>
    
    <div class="flex">
        <?php include __DIR__ . '/../app/views/layouts/sidebar.php'; ?>
        
        <main class="flex-1 p-6">
            <?php
            $flash = getFlash('success') ?? getFlash('error') ?? getFlash('info');
            if ($flash): 
            ?>
            <div class="mb-4 p-4 rounded <?php echo ($flash['type'] === 'error') ? 'bg-red-100 text-red-700' : (($flash['type'] === 'success') ? 'bg-green-100 text-green-700' : 'bg-blue-100 text-blue-700'); ?>">
                <?php echo e($flash['message']); ?>
            </div>
            <?php endif; ?>
            
            <?php include __DIR__ . '/../app/views/page_' . $page . '.php'; ?>
        </main>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>
