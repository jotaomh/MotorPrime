<?php
require_once __DIR__ . '/../app/bootstrap.php';

header('Content-Type: application/json');

require_once __DIR__ . '/../app/middleware/auth.php';

$action = $_GET['action'] ?? '';

if (!auth()) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autorizado']);
    exit;
}

$cargo = $_SESSION['usuario']['cargo'] ?? '';

switch ($action) {
    case 'veiculos':
        if (!\App\Helpers\Permissao::pode('veiculos')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sem permissão']);
            exit;
        }
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
        if (!\App\Helpers\Permissao::pode('clientes')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sem permissão']);
            exit;
        }
        $termo = cleanInput($_GET['q'] ?? '');
        
        if (strlen($termo) < 2) {
            echo json_encode([]);
            break;
        }
        
        $stmt = db()->query(
            "SELECT id, nome, LEFT(cpf, 3) as cpf_mask, telefone, email FROM clientes WHERE nome LIKE ? AND ativo = 1 LIMIT 10",
            ['%' . $termo . '%']
        );
        
        echo json_encode($stmt->fetchAll());
        break;
        
    case 'pecas':
        if (!\App\Helpers\Permissao::pode('estoque')) {
            http_response_code(403);
            echo json_encode(['error' => 'Sem permissão']);
            exit;
        }
        $termo = cleanInput($_GET['q'] ?? '');
        
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
        http_response_code(400);
        echo json_encode(['error' => 'Ação inválida']);
        break;
}
