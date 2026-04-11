<?php

namespace App\Controllers;

class PecaController {
    public function index() {
        requireAuth();
        
        $db = db();
        
        // Listar todas as peças
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.ativo = 1
            ORDER BY p.descricao
        ");
        $pecas = $stmt->fetchAll();
        
        // Peças com estoque baixo
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.quantidade <= p.estoque_minimo AND p.ativo = 1
            ORDER BY p.quantidade ASC
        ");
        $estoqueBaixo = $stmt->fetchAll();
        
        // Fornecedores para select
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        
        return [
            'pecas' => $pecas,
            'estoqueBaixo' => $estoqueBaixo,
            'fornecedores' => $fornecedores
        ];
    }
    
    public function store() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $codigo = sanitize($_POST['codigo'] ?? '');
            $descricao = sanitize($_POST['descricao'] ?? '');
            $marca = sanitize($_POST['marca'] ?? '');
            $categoria = sanitize($_POST['categoria'] ?? '');
            $preco_custo = (float)($_POST['preco_custo'] ?? 0);
            $preco_venda = (float)($_POST['preco_venda'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $estoque_minimo = (int)($_POST['estoque_minimo'] ?? 5);
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            
            if (empty($codigo) || empty($descricao)) {
                flash('error', 'Código e descrição são obrigatórios');
                redirect('/public/dashboard.php?page=estoque');
            }
            
            $db = db();
            $stmt = $db->query("SELECT id FROM pecas WHERE codigo = ?", [$codigo]);
            if ($stmt->fetch()) {
                flash('error', 'Código já cadastrado');
                redirect('/public/dashboard.php?page=estoque');
            }
            
            $db->query(
                "INSERT INTO pecas (codigo, descricao, marca, categoria, preco_custo, preco_venda, quantidade, estoque_minimo, fornecedor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)",
                [$codigo, $descricao, $marca, $categoria, $preco_custo, $preco_venda, $quantidade, $estoque_minimo, $fornecedor_id ?: null]
            );
            
            flash('success', 'Peça cadastrada');
            redirect('/public/dashboard.php?page=estoque');
        }
    }
    
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $codigo = sanitize($_POST['codigo'] ?? '');
            $descricao = sanitize($_POST['descricao'] ?? '');
            $marca = sanitize($_POST['marca'] ?? '');
            $categoria = sanitize($_POST['categoria'] ?? '');
            $preco_custo = (float)($_POST['preco_custo'] ?? 0);
            $preco_venda = (float)($_POST['preco_venda'] ?? 0);
            $estoque_minimo = (int)($_POST['estoque_minimo'] ?? 5);
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            
            if ($id === 0 || empty($codigo) || empty($descricao)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=estoque');
            }
            
            $db = db();
            
            // Verificar se código já existe em outra peça
            $stmt = $db->query("SELECT id FROM pecas WHERE codigo = ? AND id != ?", [$codigo, $id]);
            if ($stmt->fetch()) {
                flash('error', 'Código já cadastrado em outra peça');
                redirect('/public/dashboard.php?page=estoque');
            }
            
            $db->query(
                "UPDATE pecas SET codigo = ?, descricao = ?, marca = ?, categoria = ?, preco_custo = ?, preco_venda = ?, estoque_minimo = ?, fornecedor_id = ? WHERE id = ?",
                [$codigo, $descricao, $marca, $categoria, $preco_custo, $preco_venda, $estoque_minimo, $fornecedor_id ?: null, $id]
            );
            
            flash('success', 'Peça atualizada');
            redirect('/public/dashboard.php?page=estoque');
        }
    }
    
    public function delete() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0) {
                $db = db();
                $db->query("UPDATE pecas SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Peça excluída');
            }
        }
        
        redirect('/public/dashboard.php?page=estoque');
    }
    
    public function entrada() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $preco_unitario = (float)($_POST['preco_unitario'] ?? 0);
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($peca_id === 0 || $quantidade <= 0) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=estoque');
            }
            
            $db = db();
            
            // Atualizar quantidade
            $db->query(
                "UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?",
                [$quantidade, $peca_id]
            );
            
            // Atualizar preço custo se informado
            if ($preco_unitario > 0) {
                $db->query("UPDATE pecas SET preco_custo = ? WHERE id = ?", [$preco_unitario, $peca_id]);
            }
            
            // Registrar movimentação
            $db->query(
                "INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, observacoes, usuario_id) VALUES (?, 'entrada', ?, ?, ?, ?)",
                [$peca_id, $quantidade, $preco_unitario, $observacoes, auth()['id']]
            );
            
            flash('success', 'Entrada registrada');
            redirect('/public/dashboard.php?page=estoque');
        }
    }
    
    public function buscar() {
        requireAuth();
        
        $termo = sanitize($_GET['q'] ?? '');
        
        if (strlen($termo) < 1) {
            echo json_encode([]);
            return;
        }
        
        $db = db();
        $stmt = $db->query(
            "SELECT id, codigo, descricao, marca, preco_venda, quantidade FROM pecas WHERE (codigo LIKE ? OR descricao LIKE ?) AND ativo = 1 AND quantidade > 0 LIMIT 10",
            ['%' . $termo . '%', '%' . $termo . '%']
        );
        
        echo json_encode($stmt->fetchAll());
    }
}