<?php

namespace App\Controllers;

class ClienteController {
    public function index() {
        requireAuth();
        
        $db = db();
        $stmt = $db->query("SELECT * FROM clientes WHERE ativo = 1 ORDER BY nome");
        $clientes = $stmt->fetchAll();
        
        return ['clientes' => $clientes];
    }
    
    public function store() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = sanitize($_POST['nome'] ?? '');
            $cpf = sanitize($_POST['cpf'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');

            if (empty($nome) || empty($telefone)) {
                flash('error', 'Nome e telefone são obrigatórios');
                redirect('/public/dashboard.php?page=clientes');
            }

            $db = db();
            
            if (!empty($cpf)) {
                $stmt = $db->query("SELECT id FROM clientes WHERE cpf = ?", [$cpf]);
                if ($stmt->fetch()) {
                    flash('error', 'CPF já cadastrado');
                    redirect('/public/dashboard.php?page=clientes');
                }
            }

            $db->query(
                "INSERT INTO clientes (nome, cpf, cnpj, telefone, email, endereco, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)",
                [$nome, $cpf, $cnpj, $telefone, $email, $endereco, $observacoes]
            );

            flash('success', 'Cliente cadastrado com sucesso');
            redirect('/public/dashboard.php?page=clientes');
        }
    }
    
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $cpf = sanitize($_POST['cpf'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');

            if (empty($id) || empty($nome) || empty($telefone)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=clientes');
            }

            $db = db();
            $db->query(
                "UPDATE clientes SET nome = ?, cpf = ?, cnpj = ?, telefone = ?, email = ?, endereco = ?, observacoes = ? WHERE id = ?",
                [$nome, $cpf, $cnpj, $telefone, $email, $endereco, $observacoes, $id]
            );

            flash('success', 'Cliente atualizado com sucesso');
            redirect('/public/dashboard.php?page=clientes');
        }
    }
    
    public function delete() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0) {
                $db = db();
                $db->query("UPDATE clientes SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Cliente excluído com sucesso');
            }
        }
        
        redirect('/public/dashboard.php?page=clientes');
    }
    
    public function buscar() {
        requireAuth();
        
        $termo = sanitize($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            echo json_encode([]);
            return;
        }
        
        $db = db();
        $stmt = $db->query(
            "SELECT id, nome, cpf, telefone, email FROM clientes WHERE nome LIKE ? AND ativo = 1 LIMIT 10",
            ['%' . $termo . '%']
        );
        
        echo json_encode($stmt->fetchAll());
    }
}