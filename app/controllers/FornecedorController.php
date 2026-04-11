<?php

namespace App\Controllers;

class FornecedorController {
    public function index() {
        requireAuth();
        
        $db = db();
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        
        return ['fornecedores' => $fornecedores];
    }
    
    public function store() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = sanitize($_POST['nome'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if (empty($nome)) {
                flash('error', 'Nome é obrigatório');
                redirect('/public/dashboard.php?page=fornecedores');
            }
            
            $db = db();
            $db->query(
                "INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco, observacoes) VALUES (?, ?, ?, ?, ?, ?)",
                [$nome, $cnpj, $telefone, $email, $endereco, $observacoes]
            );
            
            flash('success', 'Fornecedor cadastrado');
            redirect('/public/dashboard.php?page=fornecedores');
        }
    }
    
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($id === 0 || empty($nome)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=fornecedores');
            }
            
            $db = db();
            $db->query(
                "UPDATE fornecedores SET nome = ?, cnpj = ?, telefone = ?, email = ?, endereco = ?, observacoes = ? WHERE id = ?",
                [$nome, $cnpj, $telefone, $email, $endereco, $observacoes, $id]
            );
            
            flash('success', 'Fornecedor atualizado');
            redirect('/public/dashboard.php?page=fornecedores');
        }
    }
    
    public function delete() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0) {
                $db = db();
                $db->query("UPDATE fornecedores SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Fornecedor excluído');
            }
        }
        
        redirect('/public/dashboard.php?page=fornecedores');
    }
}