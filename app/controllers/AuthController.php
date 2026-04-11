<?php

namespace App\Controllers;

class AuthController {
    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = sanitize($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';

            if (empty($email) || empty($senha)) {
                flash('error', 'Preencha todos os campos');
                redirect('/public/login.php');
            }

            $db = db();
            $stmt = $db->query("SELECT * FROM usuarios WHERE email = ? AND ativo = 1", [$email]);
            $usuario = $stmt->fetch();

            if ($usuario && password_verify($senha, $usuario['senha'])) {
                $_SESSION['usuario'] = [
                    'id' => $usuario['id'],
                    'nome' => $usuario['nome'],
                    'email' => $usuario['email'],
                    'cargo' => $usuario['cargo']
                ];
                flash('success', 'Bem-vindo, ' . $usuario['nome']);
                redirect('/public/dashboard.php');
            } else {
                flash('error', 'Email ou senha inválidos');
                redirect('/public/login.php');
            }
        }
    }

    public function logout() {
        session_destroy();
        redirect('/public/login.php');
    }

    public function register() {
        if (!isAdmin()) {
            flash('error', 'Acesso negado');
            redirect('/public/dashboard.php');
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = sanitize($_POST['nome'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $senha = $_POST['senha'] ?? '';
            $cargo = sanitize($_POST['cargo'] ?? 'vendedor');

            if (empty($nome) || empty($email) || empty($senha)) {
                flash('error', 'Preencha todos os campos');
                redirect('/public/login.php?action=register');
            }

            $db = db();
            $stmt = $db->query("SELECT id FROM usuarios WHERE email = ?", [$email]);
            if ($stmt->fetch()) {
                flash('error', 'Email já cadastrado');
                redirect('/public/login.php?action=register');
            }

            $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
            $db->query(
                "INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)",
                [$nome, $email, $senhaHash, $cargo]
            );

            flash('success', 'Usuário cadastrado com sucesso');
            redirect('/public/dashboard.php');
        }
    }
}