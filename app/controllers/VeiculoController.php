<?php

namespace App\Controllers;

class VeiculoController {
    public function index() {
        requireAuth();
        
        $db = db();
        $stmt = $db->query("
            SELECT v.*, c.nome as cliente_nome 
            FROM veiculos v
            JOIN clientes c ON v.cliente_id = c.id
            WHERE v.ativo = 1
            ORDER BY v.placa
        ");
        $veiculos = $stmt->fetchAll();
        
        return ['veiculos' => $veiculos];
    }
    
    public function store() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            $placa = sanitize($_POST['placa'] ?? '');
            $marca = sanitize($_POST['marca'] ?? '');
            $modelo = sanitize($_POST['modelo'] ?? '');
            $ano = (int)($_POST['ano'] ?? 0);
            $cor = sanitize($_POST['cor'] ?? '');
            $km = (int)($_POST['km'] ?? 0);
            $observacoes = sanitize($_POST['observacoes'] ?? '');

            if (empty($cliente_id) || empty($placa) || empty($marca) || empty($modelo) || empty($ano)) {
                flash('error', 'Preencha todos os campos obrigatórios');
                redirect('/public/dashboard.php?page=veiculos');
            }

            $placaFormatada = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $placa));
            
            $db = db();
            $stmt = $db->query("SELECT id FROM veiculos WHERE placa = ? AND ativo = 1", [$placaFormatada]);
            if ($stmt->fetch()) {
                flash('error', 'Placa já cadastrada');
                redirect('/public/dashboard.php?page=veiculos');
            }

            $db->query(
                "INSERT INTO veiculos (cliente_id, placa, marca, modelo, ano, cor, km, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                [$cliente_id, $placaFormatada, $marca, $modelo, $ano, $cor, $km, $observacoes]
            );

            flash('success', 'Veículo cadastrado com sucesso');
            redirect('/public/dashboard.php?page=veiculos');
        }
    }
    
    public function update() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            $placa = sanitize($_POST['placa'] ?? '');
            $marca = sanitize($_POST['marca'] ?? '');
            $modelo = sanitize($_POST['modelo'] ?? '');
            $ano = (int)($_POST['ano'] ?? 0);
            $cor = sanitize($_POST['cor'] ?? '');
            $km = (int)($_POST['km'] ?? 0);
            $observacoes = sanitize($_POST['observacoes'] ?? '');

            if (empty($id) || empty($cliente_id) || empty($placa) || empty($marca) || empty($modelo)) {
                flash('error', 'Dados inválidos');
                redirect('/public/dashboard.php?page=veiculos');
            }

            $placaFormatada = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $placa));
            
            $db = db();
            $db->query(
                "UPDATE veiculos SET cliente_id = ?, placa = ?, marca = ?, modelo = ?, ano = ?, cor = ?, km = ?, observacoes = ? WHERE id = ?",
                [$cliente_id, $placaFormatada, $marca, $modelo, $ano, $cor, $km, $observacoes, $id]
            );

            flash('success', 'Veículo atualizado com sucesso');
            redirect('/public/dashboard.php?page=veiculos');
        }
    }
    
    public function delete() {
        requireAuth();
        
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int)($_POST['id'] ?? 0);
            
            if ($id > 0) {
                $db = db();
                $db->query("UPDATE veiculos SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Veículo excluído com sucesso');
            }
        }
        
        redirect('/public/dashboard.php?page=veiculos');
    }
    
    public function buscar() {
        requireAuth();
        
        $termo = sanitize($_GET['q'] ?? '');
        $cliente_id = (int)($_GET['cliente_id'] ?? 0);
        
        $db = db();
        
        if ($cliente_id > 0) {
            $stmt = $db->query(
                "SELECT id, placa, marca, modelo, ano FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY placa",
                [$cliente_id]
            );
        } elseif (strlen($termo) >= 2) {
            $stmt = $db->query(
                "SELECT v.id, v.placa, v.marca, v.modelo, v.ano, c.nome as cliente_nome 
                FROM veiculos v
                JOIN clientes c ON v.cliente_id = c.id
                WHERE (v.placa LIKE ? OR v.marca LIKE ? OR v.modelo LIKE ?) AND v.ativo = 1 
                LIMIT 10",
                ['%' . $termo . '%', '%' . $termo . '%', '%' . $termo . '%']
            );
        } else {
            $stmt = $db->query(
                "SELECT v.id, v.placa, v.marca, v.modelo, v.ano, c.nome as cliente_nome 
                FROM veiculos v
                JOIN clientes c ON v.cliente_id = c.id
                WHERE v.ativo = 1 
                ORDER BY v.placa
                LIMIT 20"
            );
        }
        
        echo json_encode($stmt->fetchAll());
    }
}