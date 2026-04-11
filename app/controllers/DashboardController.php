<?php

namespace App\Controllers;

class DashboardController {
    public function index() {
        requireAuth();
        
        $db = db();
        
        // Contagem de OS por status
        $osCounts = [
            'orcamento' => 0,
            'aprovado' => 0,
            'em_execucao' => 0,
            'finalizado' => 0,
            'entregue' => 0
        ];
        
        $stmt = $db->query("SELECT status, COUNT(*) as total FROM ordens_servico GROUP BY status");
        while ($row = $stmt->fetch()) {
            $osCounts[$row['status']] = (int)$row['total'];
        }
        
        // OS em andamento (não entregue)
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM ordens_servico 
            WHERE status IN ('orcamento', 'aprovado', 'em_execucao', 'finalizado')
        ");
        $osEmAndamento = $stmt->fetch()['total'] ?? 0;
        
        // Peças com estoque baixo
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM pecas 
            WHERE quantidade <= estoque_minimo AND ativo = 1
        ");
        $pecasEstoqueBaixo = $stmt->fetch()['total'] ?? 0;
        
        // Faturamento do dia
        $stmt = $db->query("
            SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM ordens_servico 
            WHERE DATE(data_entrada) = CURDATE() AND status IN ('finalizado', 'entregue')
        ");
        $faturamentoDia = $stmt->fetch()['total'] ?? 0;
        
        // Faturamento do mês
        $stmt = $db->query("
            SELECT COALESCE(SUM(valor_total), 0) as total 
            FROM ordens_servico 
            WHERE MONTH(data_entrada) = MONTH(CURDATE()) 
            AND YEAR(data_entrada) = YEAR(CURDATE()) 
            AND status IN ('finalizado', 'entregue')
        ");
        $faturamentoMes = $stmt->fetch()['total'] ?? 0;
        
        // OS recentes
        $stmt = $db->query("
            SELECT os.*, c.nome as cliente_nome, v.placa, v.modelo
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            ORDER BY os.created_at DESC
            LIMIT 5
        ");
        $osRecentes = $stmt->fetchAll();
        
        return [
            'osCounts' => $osCounts,
            'osEmAndamento' => $osEmAndamento,
            'pecasEstoqueBaixo' => $pecasEstoqueBaixo,
            'faturamentoDia' => $faturamentoDia,
            'faturamentoMes' => $faturamentoMes,
            'osRecentes' => $osRecentes
        ];
    }
}