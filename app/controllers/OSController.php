<?php

namespace App\Controllers;

class OSController {
    public function index() {
        requireAuth();
        
        $status = sanitize($_GET['status'] ?? '');
        $cliente_id = (int)($_GET['cliente_id'] ?? 0);
        $placa = sanitize($_GET['placa'] ?? '');
        
        $where = "WHERE 1=1";
        $params = [];
        
        if (!empty($status)) {
            $where .= " AND os.status = ?";
            $params[] = $status;
        }
        
        if ($cliente_id > 0) {
            $where .= " AND os.cliente_id = ?";
            $params[] = $cliente_id;
        }
        
        if (!empty($placa)) {
            $where .= " AND v.placa LIKE ?";
            $params[] = '%' . $placa . '%';
        }
        
        $db = db();
        $stmt = $db->query("
            SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                   v.placa, v.marca, v.modelo, v.ano
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            $where
            ORDER BY os.created_at DESC
        ", $params);
        $ordens = $stmt->fetchAll();
        
        return ['ordens' => $ordens];
    }
    
    public function create() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            $veiculo_id = (int)($_POST['veiculo_id'] ?? 0);
            $descricao = sanitize($_POST['descricao'] ?? '');
            
            if (empty($cliente_id) || empty($veiculo_id)) {
                flash('error', 'Selecione o cliente e veículo');
                redirect('/public/dashboard.php?page=os');
            }
            
            $db = db();
            $db->query(
                "INSERT INTO ordens_servico (cliente_id, veiculo_id, usuario_id, descricao, data_entrada) VALUES (?, ?, ?, ?, CURDATE())",
                [$cliente_id, $veiculo_id, auth()['id'], $descricao]
            );
            
            $os_id = $db->lastInsertId();
            
            flash('success', 'Ordem de serviço criada');
            redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
        }
    }
    
    public function edit() {
        requireAuth();
        
        $id = (int)($_GET['id'] ?? 0);
        
        if ($id === 0) {
            redirect('/public/dashboard.php?page=os');
        }
        
        $db = db();
        
        // OS principal
        $stmt = $db->query("
            SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, c.cpf as cliente_cpf,
                   v.placa, v.marca, v.modelo, v.ano, v.cor
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            WHERE os.id = ?
        ", [$id]);
        $os = $stmt->fetch();
        
        if (!$os) {
            flash('error', 'OS não encontrada');
            redirect('/public/dashboard.php?page=os');
        }
        
        // Serviços
        $stmt = $db->query("SELECT * FROM servicos_os WHERE os_id = ?", [$id]);
        $servicos = $stmt->fetchAll();
        
        // Peças
        $stmt = $db->query("
            SELECT po.*, p.codigo, p.descricao as peca_descricao, p.marca
            FROM pecas_os po
            JOIN pecas p ON po.peca_id = p.id
            WHERE po.os_id = ?
        ", [$id]);
        $pecas = $stmt->fetchAll();
        
        return [
            'os' => $os,
            'servicos' => $servicos,
            'pecas' => $pecas
        ];
    }
    
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            $descricao = sanitize($_POST['descricao'] ?? '');
            $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? 'dinheiro');
            
            if ($id === 0) {
                flash('error', 'OS inválida');
                redirect('/public/dashboard.php?page=os');
            }
            
            $db = db();
            
            // Atualiza status e data fim se estiver finalizando
            $dataFim = ', data_fim = CURDATE()';
            $dataEntrega = '';
            
            if ($status === 'entregue') {
                $dataEntrega = ', data_entrega = CURDATE()';
            }
            
            $db->query("
                UPDATE ordens_servico 
                SET status = ?, descricao = ?, forma_pagamento = ? $dataFim $dataEntrega
                WHERE id = ?
            ", [$status, $descricao, $forma_pagamento, $id]);
            
            flash('success', 'OS atualizada');
            redirect('/public/dashboard.php?page=os&action=edit&id=' . $id);
        }
    }
    
    public function addServico() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $os_id = (int)($_POST['os_id'] ?? 0);
            $descricao = sanitize($_POST['descricao'] ?? '');
            $valor = (float)($_POST['valor'] ?? 0);
            
            if ($os_id === 0 || empty($descricao)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=os');
            }
            
            $db = db();
            $db->query(
                "INSERT INTO servicos_os (os_id, descricao, valor) VALUES (?, ?, ?)",
                [$os_id, $descricao, $valor]
            );
            
            $this->recalcularTotal($os_id);
            
            flash('success', 'Serviço adicionado');
            redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
        }
    }
    
    public function removeServico() {
        requireAuth();
        
        $id = (int)($_GET['id'] ?? 0);
        $os_id = (int)($_GET['os_id'] ?? 0);
        
        if ($id > 0 && $os_id > 0) {
            $db = db();
            $db->query("DELETE FROM servicos_os WHERE id = ?", [$id]);
            $this->recalcularTotal($os_id);
            flash('success', 'Serviço removido');
        }
        
        redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
    }
    
    public function addPeca() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $os_id = (int)($_POST['os_id'] ?? 0);
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 1);
            
            if ($os_id === 0 || $peca_id === 0) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=os');
            }
            
            $db = db();
            
            // Verificar estoque
            $stmt = $db->query("SELECT quantidade, preco_venda FROM pecas WHERE id = ?", [$peca_id]);
            $peca = $stmt->fetch();
            
            if ($peca['quantidade'] < $quantidade) {
                flash('error', 'Estoque insuficiente');
                redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
            }
            
            // Adicionar peça na OS
            $db->query(
                "INSERT INTO pecas_os (os_id, peca_id, quantidade, valor_unitario) VALUES (?, ?, ?, ?)",
                [$os_id, $peca_id, $quantidade, $peca['preco_venda']]
            );
            
            // Descontar do estoque
            $db->query(
                "UPDATE pecas SET quantidade = quantidade - ? WHERE id = ?",
                [$quantidade, $peca_id]
            );
            
            // Registrar movimentação
            $db->query(
                "INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, usuario_id, observacoes) VALUES (?, 'saida', ?, ?, ?, ?)",
                [$peca_id, $quantidade, $peca['preco_venda'], auth()['id'], 'Uso em OS #' . $os_id]
            );
            
            $this->recalcularTotal($os_id);
            
            flash('success', 'Peça adicionada');
            redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
        }
    }
    
    public function removePeca() {
        requireAuth();
        
        $id = (int)($_GET['id'] ?? 0);
        $os_id = (int)($_GET['os_id'] ?? 0);
        
        if ($id > 0 && $os_id > 0) {
            $db = db();
            
            // Buscar peça para devolver ao estoque
            $stmt = $db->query("SELECT peca_id, quantidade FROM pecas_os WHERE id = ?", [$id]);
            $pecaOs = $stmt->fetch();
            
            if ($pecaOs) {
                // Devolver ao estoque
                $db->query(
                    "UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?",
                    [$pecaOs['quantidade'], $pecaOs['peca_id']]
                );
                
                // Remover registro
                $db->query("DELETE FROM pecas_os WHERE id = ?", [$id]);
                
                $this->recalcularTotal($os_id);
                flash('success', 'Peça removida');
            }
        }
        
        redirect('/public/dashboard.php?page=os&action=edit&id=' . $os_id);
    }
    
    private function recalcularTotal($os_id) {
        $db = db();
        
        // Somar serviços
        $stmt = $db->query("SELECT COALESCE(SUM(valor), 0) as total FROM servicos_os WHERE os_id = ?", [$os_id]);
        $totalServicos = $stmt->fetch()['total'];
        
        // Somar peças
        $stmt = $db->query("SELECT COALESCE(SUM(quantidade * valor_unitario), 0) as total FROM pecas_os WHERE os_id = ?", [$os_id]);
        $totalPecas = $stmt->fetch()['total'];
        
        // Atualizar OS
        $db->query(
            "UPDATE ordens_servico SET valor_mao_obra = ?, valor_pecas = ?, valor_total = ? WHERE id = ?",
            [$totalServicos, $totalPecas, $totalServicos + $totalPecas, $os_id]
        );
    }
    
    public function delete() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0) {
                // Devolver peças ao estoque antes de excluir
                $db = db();
                $stmt = $db->query("SELECT peca_id, quantidade FROM pecas_os WHERE os_id = ?", [$id]);
                while ($peca = $stmt->fetch()) {
                    $db->query(
                        "UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?",
                        [$peca['quantidade'], $peca['peca_id']]
                    );
                }
                
                $db->query("DELETE FROM ordens_servico WHERE id = ?", [$id]);
                flash('success', 'OS excluída');
            }
        }
        
        redirect('/public/dashboard.php?page=os');
    }
}