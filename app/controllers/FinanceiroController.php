<?php

namespace App\Controllers;

class FinanceiroController {
    public function index() {
        requireAuth();
        
        $data_inicio = sanitize($_GET['data_inicio'] ?? date('Y-m-01'));
        $data_fim = sanitize($_GET['data_fim'] ?? date('Y-m-d'));
        
        $db = db();
        
        // Faturamento por período (OS finalizadas)
        $stmt = $db->query("
            SELECT 
                DATE(data_entrada) as data,
                COUNT(*) as total_os,
                SUM(valor_total) as faturamento,
                SUM(valor_mao_obra) as mao_obra,
                SUM(valor_pecas) as pecas
            FROM ordens_servico
            WHERE DATE(data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue')
            GROUP BY DATE(data_entrada)
            ORDER BY data DESC
        ", [$data_inicio, $data_fim]);
        $faturamento_diario = $stmt->fetchAll();
        
        // Total geral do período
        $stmt = $db->query("
            SELECT 
                COUNT(*) as total_os,
                COALESCE(SUM(valor_total), 0) as faturamento,
                COALESCE(SUM(valor_mao_obra), 0) as mao_obra,
                COALESCE(SUM(valor_pecas), 0) as pecas
            FROM ordens_servico
            WHERE DATE(data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue')
        ", [$data_inicio, $data_fim]);
        $totais = $stmt->fetch();
        
        // Faturamento por forma de pagamento
        $stmt = $db->query("
            SELECT forma_pagamento, SUM(valor_total) as total
            FROM ordens_servico
            WHERE DATE(data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue')
            GROUP BY forma_pagamento
        ", [$data_inicio, $data_fim]);
        $por_pagamento = $stmt->fetchAll();
        
        // Vendas diretas (balcão)
        $stmt = $db->query("
            SELECT 
                DATE(created_at) as data,
                COUNT(*) as total_vendas,
                SUM(valor_total) as faturamento
            FROM vendas
            WHERE DATE(created_at) BETWEEN ? AND ?
            GROUP BY DATE(created_at)
            ORDER BY data DESC
        ", [$data_inicio, $data_fim]);
        $vendas_diretas = $stmt->fetchAll();
        
        return [
            'faturamento_diario' => $faturamento_diario,
            'totais' => $totais,
            'por_pagamento' => $por_pagamento,
            'vendas_diretas' => $vendas_diretas,
            'data_inicio' => $data_inicio,
            'data_fim' => $data_fim
        ];
    }
    
    public function vendaBalcao() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 1);
            $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? 'dinheiro');
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            
            if ($peca_id === 0 || $quantidade <= 0) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=financeiro');
            }
            
            $db = db();
            
            // Buscar peça
            $stmt = $db->query("SELECT quantidade, preco_venda FROM pecas WHERE id = ?", [$peca_id]);
            $peca = $stmt->fetch();
            
            if (!$peca) {
                flash('error', 'Peça não encontrada');
                redirect('/public/dashboard.php?page=financeiro');
            }
            
            if ($peca['quantidade'] < $quantidade) {
                flash('error', 'Estoque insuficiente');
                redirect('/public/dashboard.php?page=financeiro');
            }
            
            $valor_unitario = $peca['preco_venda'];
            $valor_total = $valor_unitario * $quantidade;
            
            // Registrar venda
            $db->query(
                "INSERT INTO vendas (peca_id, quantidade, valor_unitario, valor_total, forma_pagamento, cliente_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$peca_id, $quantidade, $valor_unitario, $valor_total, $forma_pagamento, $cliente_id ?: null, auth()['id']]
            );
            
            // Descontar do estoque
            $db->query(
                "UPDATE pecas SET quantidade = quantidade - ? WHERE id = ?",
                [$quantidade, $peca_id]
            );
            
            // Registrar movimentação
            $db->query(
                "INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, usuario_id, observacoes) VALUES (?, 'venda', ?, ?, ?, ?)",
                [$peca_id, $quantidade, $valor_unitario, auth()['id'], 'Venda balcão']
            );
            
            flash('success', 'Venda realizada: ' . formatMoney($valor_total));
            redirect('/public/dashboard.php?page=financeiro');
        }
    }
}