<?php

namespace App\Controllers;

class FuncionarioController {
    public function index() {
        requireAuth();
        
        $db = db();
        $stmt = $db->query("SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome");
        $funcionarios = $stmt->fetchAll();
        
        return ['funcionarios' => $funcionarios];
    }
    
    public function store() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = sanitize($_POST['nome'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $cargo = sanitize($_POST['cargo'] ?? 'vendedor');
            
            if (empty($nome) || empty($email) || empty($senha)) {
                flash('error', 'Preencha todos os campos');
                redirect('/public/dashboard.php?page=funcionarios');
            }
            
            $db = db();
            $stmt = $db->query("SELECT id FROM usuarios WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                flash('error', 'Email já cadastrado');
                redirect('/public/dashboard.php?page=funcionarios');
            }
            
            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $db->query(
                "INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)",
                [$nome, $email, $senhaHash, $cargo]
            );
            
            flash('success', 'Funcionário cadastrado');
            redirect('/public/dashboard.php?page=funcionarios');
        }
    }
    
    public function update() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $cargo = sanitize($_POST['cargo'] ?? 'vendedor');
            $novaSenha = $_POST['nova_senha'] ?? '';
            
            if ($id === 0 || empty($nome) || empty($email)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=funcionarios');
            }
            
            $db = db();
            
            // Verificar email duplicado
            $stmt = $db->query("SELECT id FROM usuarios WHERE email = ? AND id != ?", [$email, $id]);
            if ($stmt->fetch()) {
                flash('error', 'Email já está em uso');
                redirect('/public/dashboard.php?page=funcionarios');
            }
            
            if (!empty($novaSenha)) {
                $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                $db->query(
                    "UPDATE usuarios SET nome = ?, email = ?, cargo = ?, senha = ? WHERE id = ?",
                    [$nome, $email, $cargo, $senhaHash, $id]
                );
            } else {
                $db->query(
                    "UPDATE usuarios SET nome = ?, email = ?, cargo = ? WHERE id = ?",
                    [$nome, $email, $cargo, $id]
                );
            }
            
            flash('success', 'Funcionário atualizado');
            redirect('/public/dashboard.php?page=funcionarios');
        }
    }
    
    public function delete() {
        requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            // Não permitir excluir o próprio usuário
            if ($id === auth()['id']) {
                flash('error', 'Você não pode excluir seu próprio usuário');
                redirect('/public/dashboard.php?page=funcionarios');
            }
            
            if ($id > 0) {
                $db = db();
                $db->query("UPDATE usuarios SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Funcionário excluído');
            }
        }
        
        redirect('/public/dashboard.php?page=funcionarios');
    }
}