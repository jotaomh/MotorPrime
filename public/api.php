<?php
session_start();

header('Content-Type: application/json');

require_once __DIR__ . '/../app/config/database.php';

$action = $_GET['action'] ?? '';

switch ($action) {
    case 'veiculos':
        $cliente_id = (int)($_GET['cliente_id'] ?? 0);
        
        if ($cliente_id > 0) {
            $stmt = db()->query(
                "SELECT id, placa, marca, modelo, ano FROM veiculos WHERE cliente_id = ? AND ativo = 1 ORDER BY placa",
                [$cliente_id]
            );
        } else {
            $stmt = db()->query(
                "SELECT v.id, v.placa, v.marca, v.modelo, v.ano, c.nome as cliente_nome 
                FROM veiculos v
                JOIN clientes c ON v.cliente_id = c.id
                WHERE v.ativo = 1 
                ORDER BY v.placa
                LIMIT 20"
            );
        }
        
        echo json_encode($stmt->fetchAll());
        break;
        
    case 'clientes':
        $termo = sanitize($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            echo json_encode([]);
            break;
        }
        
        $stmt = db()->query(
            "SELECT id, nome, cpf, telefone, email FROM clientes WHERE nome LIKE ? AND ativo = 1 LIMIT 10",
            ['%' . $termo . '%']
        );
        
        echo json_encode($stmt->fetchAll());
        break;
        
    case 'pecas':
        $termo = sanitize($_GET['q'] ?? '');
        
        if (strlen($termo) < 1) {
            echo json_encode([]);
            break;
        }
        
        $stmt = db()->query(
            "SELECT id, codigo, descricao, marca, preco_venda, quantidade FROM pecas WHERE (codigo LIKE ? OR descricao LIKE ?) AND ativo = 1 AND quantidade > 0 LIMIT 10",
            ['%' . $termo . '%', '%' . $termo . '%']
        );
        
        echo json_encode($stmt->fetchAll());
        break;
        
    default:
        echo json_encode(['error' => 'Ação inválida']);
}