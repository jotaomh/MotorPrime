<?php
session_start();

require_once __DIR__ . '/../app/config/database.php';
require_once __DIR__ . '/../app/middleware/auth.php';

requireAuth();

$page = $_GET['page'] ?? 'dashboard';
$action = $_GET['action'] ?? '';

// Carregar dados baseados na página
$dados = [];
switch ($page) {
    case 'dashboard':
        $db = db();
        
        $osCounts = ['orcamento' => 0, 'aprovado' => 0, 'em_execucao' => 0, 'finalizado' => 0, 'entregue' => 0];
        $stmt = $db->query("SELECT status, COUNT(*) as total FROM ordens_servico GROUP BY status");
        while ($row = $stmt->fetch()) {
            $osCounts[$row['status']] = (int)$row['total'];
        }
        
        $stmt = $db->query("
            SELECT COUNT(*) as total 
            FROM ordens_servico 
            WHERE status IN ('orcamento', 'aprovado', 'em_execucao', 'finalizado')
        ");
        $osEmAndamento = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COUNT(*) as total FROM pecas WHERE quantidade <= estoque_minimo AND ativo = 1");
        $pecasEstoqueBaixo = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico WHERE DATE(data_entrada) = CURDATE() AND status IN ('finalizado', 'entregue')");
        $faturamentoDia = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT COALESCE(SUM(valor_total), 0) as total FROM ordens_servico WHERE MONTH(data_entrada) = MONTH(CURDATE()) AND YEAR(data_entrada) = YEAR(CURDATE()) AND status IN ('finalizado', 'entregue')");
        $faturamentoMes = $stmt->fetch()['total'] ?? 0;
        
        $stmt = $db->query("SELECT os.*, c.nome as cliente_nome, v.placa, v.modelo FROM ordens_servico os JOIN clientes c ON os.cliente_id = c.id JOIN veiculos v ON os.veiculo_id = v.id ORDER BY os.created_at DESC LIMIT 5");
        $osRecentes = $stmt->fetchAll();
        
        $dados = compact('osCounts', 'osEmAndamento', 'pecasEstoqueBaixo', 'faturamentoDia', 'faturamentoMes', 'osRecentes');
        break;
        
    case 'clientes':
        $db = db();
        $stmt = $db->query("SELECT * FROM clientes WHERE ativo = 1 ORDER BY nome");
        $clientes = $stmt->fetchAll();
        $dados = compact('clientes');
        break;
        
    case 'veiculos':
        $db = db();
        $stmt = $db->query("SELECT v.*, c.nome as cliente_nome FROM veiculos v JOIN clientes c ON v.cliente_id = c.id WHERE v.ativo = 1 ORDER BY v.placa");
        $veiculos = $stmt->fetchAll();
        $stmt = $db->query("SELECT id, nome FROM clientes WHERE ativo = 1 ORDER BY nome");
        $clientes = $stmt->fetchAll();
        $dados = compact('veiculos', 'clientes');
        break;
        
    case 'funcionarios':
        if (!isAdmin()) {
            flash('error', 'Acesso restrito');
            header('Location: dashboard.php');
            exit;
        }
        $db = db();
        $stmt = $db->query("SELECT * FROM usuarios WHERE ativo = 1 ORDER BY nome");
        $funcionarios = $stmt->fetchAll();
        $dados = compact('funcionarios');
        break;
        
    case 'fornecedores':
        $db = db();
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        $dados = compact('fornecedores');
        break;
        
    case 'os':
        $db = db();
        $status = $_GET['status'] ?? '';
        $where = "WHERE 1=1";
        $params = [];
        if (!empty($status)) {
            $where .= " AND os.status = ?";
            $params[] = $status;
        }
        $stmt = $db->query("
            SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, 
                   v.placa, v.marca, v.modelo
            FROM ordens_servico os
            JOIN clientes c ON os.cliente_id = c.id
            JOIN veiculos v ON os.veiculo_id = v.id
            $where
            ORDER BY os.created_at DESC
        ", $params);
        $ordens = $stmt->fetchAll();
        $dados = compact('ordens');
        break;
        
    case 'os_edit':
    case 'os_view':
        $id = (int)($_GET['id'] ?? 0);
        if ($id > 0) {
            $db = db();
            $stmt = $db->query("
                SELECT os.*, c.nome as cliente_nome, c.telefone as cliente_telefone, c.cpf as cliente_cpf,
                       v.placa, v.marca, v.modelo, v.ano, v.cor
                FROM ordens_servico os
                JOIN clientes c ON os.cliente_id = c.id
                JOIN veiculos v ON os.veiculo_id = v.id
                WHERE os.id = ?
            ", [$id]);
            $os = $stmt->fetch();
            
            if ($os) {
                $stmt = $db->query("SELECT * FROM servicos_os WHERE os_id = ?", [$id]);
                $servicos = $stmt->fetchAll();
                $stmt = $db->query("
                    SELECT po.*, p.codigo, p.descricao as peca_descricao, p.marca
                    FROM pecas_os po
                    JOIN pecas p ON po.peca_id = p.id
                    WHERE po.os_id = ?
                ", [$id]);
                $pecas = $stmt->fetchAll();
                $dados = compact('os', 'servicos', 'pecas');
            }
        }
        break;
        
    case 'estoque':
        $db = db();
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.ativo = 1
            ORDER BY p.descricao
        ");
        $pecas = $stmt->fetchAll();
        
        $stmt = $db->query("
            SELECT p.*, f.nome as fornecedor_nome
            FROM pecas p
            LEFT JOIN fornecedores f ON p.fornecedor_id = f.id
            WHERE p.quantidade <= p.estoque_minimo AND p.ativo = 1
            ORDER BY p.quantidade ASC
        ");
        $estoqueBaixo = $stmt->fetchAll();
        
        $stmt = $db->query("SELECT * FROM fornecedores WHERE ativo = 1 ORDER BY nome");
        $fornecedores = $stmt->fetchAll();
        
        $dados = compact('pecas', 'estoqueBaixo', 'fornecedores');
        break;
        
    case 'financeiro':
        $db = db();
        $data_inicio = $_GET['data_inicio'] ?? date('Y-m-01');
        $data_fim = $_GET['data_fim'] ?? date('Y-m-d');
        
        $stmt = $db->query("
            SELECT DATE(data_entrada) as data, COUNT(*) as total_os, SUM(valor_total) as faturamento
            FROM ordens_servico
            WHERE DATE(data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue')
            GROUP BY DATE(data_entrada)
            ORDER BY data DESC
        ", [$data_inicio, $data_fim]);
        $faturamento_diario = $stmt->fetchAll();
        
        $stmt = $db->query("
            SELECT COUNT(*) as total_os, COALESCE(SUM(valor_total), 0) as faturamento
            FROM ordens_servico
            WHERE DATE(data_entrada) BETWEEN ? AND ?
            AND status IN ('finalizado', 'entregue')
        ", [$data_inicio, $data_fim]);
        $totais = $stmt->fetch();
        
        $dados = compact('faturamento_diario', 'totais', 'data_inicio', 'data_fim');
        break;
}

// Processar ações POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $db = db();
    
    switch ($page . '_' . ($_POST['action_form'] ?? '')) {
        case 'clientes_store':
        case 'clientes_update':
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $cpf = sanitize($_POST['cpf'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($nome && $telefone) {
                if ($id > 0) {
                    $db->query("UPDATE clientes SET nome=?, cpf=?, cnpj=?, telefone=?, email=?, endereco=?, observacoes=? WHERE id=?", 
                        [$nome, $cpf, $cnpj, $telefone, $email, $endereco, $observacoes, $id]);
                    flash('success', 'Cliente atualizado');
                } else {
                    $db->query("INSERT INTO clientes (nome, cpf, cnpj, telefone, email, endereco, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                        [$nome, $cpf, $cnpj, $telefone, $email, $endereco, $observacoes]);
                    flash('success', 'Cliente cadastrado');
                }
            }
            header('Location: dashboard.php?page=clientes');
            exit;
            
        case 'clientes_delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $db->query("UPDATE clientes SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Cliente excluído');
            }
            header('Location: dashboard.php?page=clientes');
            exit;
            
        case 'veiculos_store':
        case 'veiculos_update':
            $id = (int)($_POST['id'] ?? 0);
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            $placa = strtoupper(preg_replace('/[^A-Z0-9]/i', '', $_POST['placa'] ?? ''));
            $marca = sanitize($_POST['marca'] ?? '');
            $modelo = sanitize($_POST['modelo'] ?? '');
            $ano = (int)($_POST['ano'] ?? 0);
            $cor = sanitize($_POST['cor'] ?? '');
            $km = (int)($_POST['km'] ?? 0);
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($cliente_id && $placa && $marca && $modelo && $ano) {
                if ($id > 0) {
                    $db->query("UPDATE veiculos SET cliente_id=?, placa=?, marca=?, modelo=?, ano=?, cor=?, km=?, observacoes=? WHERE id=?", 
                        [$cliente_id, $placa, $marca, $modelo, $ano, $cor, $km, $observacoes, $id]);
                    flash('success', 'Veículo atualizado');
                } else {
                    $db->query("INSERT INTO veiculos (cliente_id, placa, marca, modelo, ano, cor, km, observacoes) VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$cliente_id, $placa, $marca, $modelo, $ano, $cor, $km, $observacoes]);
                    flash('success', 'Veículo cadastrado');
                }
            }
            header('Location: dashboard.php?page=veiculos');
            exit;
            
        case 'veiculos_delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $db->query("UPDATE veiculos SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Veículo excluído');
            }
            header('Location: dashboard.php?page=veiculos');
            exit;
            
        case 'funcionarios_store':
        case 'funcionarios_update':
            if (!isAdmin()) break;
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $cargo = sanitize($_POST['cargo'] ?? 'vendedor');
            $novaSenha = $_POST['nova_senha'] ?? '';
            
            if ($nome && $email) {
                if ($id > 0) {
                    if ($novaSenha) {
                        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
                        $db->query("UPDATE usuarios SET nome=?, email=?, cargo=?, senha=? WHERE id=?", 
                            [$nome, $email, $cargo, $senhaHash, $id]);
                    } else {
                        $db->query("UPDATE usuarios SET nome=?, email=?, cargo=? WHERE id=?", 
                            [$nome, $email, $cargo, $id]);
                    }
                    flash('success', 'Funcionário atualizado');
                } else {
                    $senhaHash = password_hash($_POST['senha'] ?? 'password', PASSWORD_DEFAULT);
                    $db->query("INSERT INTO usuarios (nome, email, senha, cargo) VALUES (?, ?, ?, ?)", 
                        [$nome, $email, $senhaHash, $cargo]);
                    flash('success', 'Funcionário cadastrado');
                }
            }
            header('Location: dashboard.php?page=funcionarios');
            exit;
            
        case 'funcionarios_delete':
            if (!isAdmin()) break;
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0 && $id !== $_SESSION['usuario']['id']) {
                $db->query("UPDATE usuarios SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Funcionário excluído');
            }
            header('Location: dashboard.php?page=funcionarios');
            exit;
            
        case 'fornecedores_store':
        case 'fornecedores_update':
            $id = (int)($_POST['id'] ?? 0);
            $nome = sanitize($_POST['nome'] ?? '');
            $cnpj = sanitize($_POST['cnpj'] ?? '');
            $telefone = sanitize($_POST['telefone'] ?? '');
            $email = sanitize($_POST['email'] ?? '');
            $endereco = sanitize($_POST['endereco'] ?? '');
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($nome) {
                if ($id > 0) {
                    $db->query("UPDATE fornecedores SET nome=?, cnpj=?, telefone=?, email=?, endereco=?, observacoes=? WHERE id=?", 
                        [$nome, $cnpj, $telefone, $email, $endereco, $observacoes, $id]);
                    flash('success', 'Fornecedor atualizado');
                } else {
                    $db->query("INSERT INTO fornecedores (nome, cnpj, telefone, email, endereco, observacoes) VALUES (?, ?, ?, ?, ?, ?)", 
                        [$nome, $cnpj, $telefone, $email, $endereco, $observacoes]);
                    flash('success', 'Fornecedor cadastrado');
                }
            }
            header('Location: dashboard.php?page=fornecedores');
            exit;
            
        case 'fornecedores_delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $db->query("UPDATE fornecedores SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Fornecedor excluído');
            }
            header('Location: dashboard.php?page=fornecedores');
            exit;
            
        case 'os_create':
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            $veiculo_id = (int)($_POST['veiculo_id'] ?? 0);
            $descricao = sanitize($_POST['descricao'] ?? '');
            
            if ($cliente_id && $veiculo_id) {
                $db->query("INSERT INTO ordens_servico (cliente_id, veiculo_id, usuario_id, descricao, data_entrada) VALUES (?, ?, ?, ?, CURDATE())", 
                    [$cliente_id, $veiculo_id, auth()['id'], $descricao]);
                $os_id = $db->lastInsertId();
                flash('success', 'OS criada');
                header('Location: dashboard.php?page=os_edit&id=' . $os_id);
                exit;
            }
            header('Location: dashboard.php?page=os');
            exit;
            
        case 'os_update':
            $id = (int)($_POST['id'] ?? 0);
            $status = sanitize($_POST['status'] ?? '');
            $descricao = sanitize($_POST['descricao'] ?? '');
            $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? 'dinheiro');
            
            $dataFim = '';
            $dataEntrega = '';
            if ($status === 'finalizado') $dataFim = ', data_fim = CURDATE()';
            if ($status === 'entregue') { $dataFim = ', data_fim = CURDATE()'; $dataEntrega = ', data_entrega = CURDATE()'; }
            
            if ($id > 0 && $status) {
                $db->query("UPDATE ordens_servico SET status=?, descricao=?, forma_pagamento=? $dataFim $dataEntrega WHERE id=?", 
                    [$status, $descricao, $forma_pagamento, $id]);
                flash('success', 'OS atualizada');
            }
            header('Location: dashboard.php?page=os_edit&id=' . $id);
            exit;
            
        case 'os_add_servico':
            $os_id = (int)($_POST['os_id'] ?? 0);
            $descricao = sanitize($_POST['descricao'] ?? '');
            $valor = (float)($_POST['valor'] ?? 0);
            
            if ($os_id && $descricao) {
                $db->query("INSERT INTO servicos_os (os_id, descricao, valor) VALUES (?, ?, ?)", [$os_id, $descricao, $valor]);
                
                $stmt = $db->query("SELECT COALESCE(SUM(valor), 0) + (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?) as total FROM servicos_os WHERE os_id = ?", [$os_id, $os_id]);
                $total = $stmt->fetch()['total'];
                $db->query("UPDATE ordens_servico SET valor_mao_obra = (SELECT COALESCE(SUM(valor), 0) FROM servicos_os WHERE os_id = ?), valor_pecas = (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?), valor_total = ? WHERE id = ?", [$os_id, $os_id, $total, $os_id]);
                
                flash('success', 'Serviço adicionado');
            }
            header('Location: dashboard.php?page=os_edit&id=' . $os_id);
            exit;
            
        case 'os_add_peca':
            $os_id = (int)($_POST['os_id'] ?? 0);
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 1);
            
            if ($os_id && $peca_id) {
                $stmt = $db->query("SELECT quantidade, preco_venda FROM pecas WHERE id = ?", [$peca_id]);
                $peca = $stmt->fetch();
                
                if ($peca && $peca['quantidade'] >= $quantidade) {
                    $db->query("INSERT INTO pecas_os (os_id, peca_id, quantidade, valor_unitario) VALUES (?, ?, ?, ?)", 
                        [$os_id, $peca_id, $quantidade, $peca['preco_venda']]);
                    $db->query("UPDATE pecas SET quantidade = quantidade - ? WHERE id = ?", [$quantidade, $peca_id]);
                    $db->query("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, usuario_id, observacoes) VALUES (?, 'saida', ?, ?, ?, ?)", 
                        [$peca_id, $quantidade, $peca['preco_venda'], auth()['id'], 'OS #' . $os_id]);
                    
                    $stmt = $db->query("SELECT COALESCE(SUM(valor), 0) + (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?) as total FROM servicos_os WHERE os_id = ?", [$os_id, $os_id]);
                    $total = $stmt->fetch()['total'];
                    $db->query("UPDATE ordens_servico SET valor_mao_obra = (SELECT COALESCE(SUM(valor), 0) FROM servicos_os WHERE os_id = ?), valor_pecas = (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?), valor_total = ? WHERE id = ?", [$os_id, $os_id, $total, $os_id]);
                    
                    flash('success', 'Peça adicionada');
                } else {
                    flash('error', 'Estoque insuficiente');
                }
            }
            header('Location: dashboard.php?page=os_edit&id=' . $os_id);
            exit;
            
        case 'estoque_store':
        case 'estoque_update':
            $id = (int)($_POST['id'] ?? 0);
            $codigo = sanitize($_POST['codigo'] ?? '');
            $descricao = sanitize($_POST['descricao'] ?? '');
            $marca = sanitize($_POST['marca'] ?? '');
            $categoria = sanitize($_POST['categoria'] ?? '');
            $preco_custo = (float)($_POST['preco_custo'] ?? 0);
            $preco_venda = (float)($_POST['preco_venda'] ?? 0);
            $estoque_minimo = (int)($_POST['estoque_minimo'] ?? 5);
            $fornecedor_id = (int)($_POST['fornecedor_id'] ?? 0);
            
            if ($codigo && $descricao) {
                if ($id > 0) {
                    $db->query("UPDATE pecas SET codigo=?, descricao=?, marca=?, categoria=?, preco_custo=?, preco_venda=?, estoque_minimo=?, fornecedor_id=? WHERE id=?", 
                        [$codigo, $descricao, $marca, $categoria, $preco_custo, $preco_venda, $estoque_minimo, $fornecedor_id ?: null, $id]);
                    flash('success', 'Peça atualizada');
                } else {
                    $quantidade = (int)($_POST['quantidade'] ?? 0);
                    $db->query("INSERT INTO pecas (codigo, descricao, marca, categoria, preco_custo, preco_venda, quantidade, estoque_minimo, fornecedor_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$codigo, $descricao, $marca, $categoria, $preco_custo, $preco_venda, $quantidade, $estoque_minimo, $fornecedor_id ?: null]);
                    flash('success', 'Peça cadastrada');
                }
            }
            header('Location: dashboard.php?page=estoque');
            exit;
            
        case 'estoque_entrada':
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 0);
            $preco_unitario = (float)($_POST['preco_unitario'] ?? 0);
            $observacoes = sanitize($_POST['observacoes'] ?? '');
            
            if ($peca_id && $quantidade > 0) {
                $db->query("UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?", [$quantidade, $peca_id]);
                if ($preco_unitario > 0) {
                    $db->query("UPDATE pecas SET preco_custo = ? WHERE id = ?", [$preco_unitario, $peca_id]);
                }
                $db->query("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, observacoes, usuario_id) VALUES (?, 'entrada', ?, ?, ?, ?)", 
                    [$peca_id, $quantidade, $preco_unitario, $observacoes, auth()['id']]);
                flash('success', 'Entrada registrada');
            }
            header('Location: dashboard.php?page=estoque');
            exit;
            
        case 'estoque_delete':
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                $db->query("UPDATE pecas SET ativo = 0 WHERE id = ?", [$id]);
                flash('success', 'Peça excluída');
            }
            header('Location: dashboard.php?page=estoque');
            exit;
            
        case 'financeiro_venda':
            $peca_id = (int)($_POST['peca_id'] ?? 0);
            $quantidade = (int)($_POST['quantidade'] ?? 1);
            $forma_pagamento = sanitize($_POST['forma_pagamento'] ?? 'dinheiro');
            $cliente_id = (int)($_POST['cliente_id'] ?? 0);
            
            if ($peca_id && $quantidade > 0) {
                $stmt = $db->query("SELECT quantidade, preco_venda FROM pecas WHERE id = ?", [$peca_id]);
                $peca = $stmt->fetch();
                
                if ($peca && $peca['quantidade'] >= $quantidade) {
                    $valor_total = $peca['preco_venda'] * $quantidade;
                    $db->query("INSERT INTO vendas (peca_id, quantidade, valor_unitario, valor_total, forma_pagamento, cliente_id, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?)", 
                        [$peca_id, $quantidade, $peca['preco_venda'], $valor_total, $forma_pagamento, $cliente_id ?: null, auth()['id']]);
                    $db->query("UPDATE pecas SET quantidade = quantidade - ? WHERE id = ?", [$quantidade, $peca_id]);
                    $db->query("INSERT INTO estoque_movimentacoes (peca_id, tipo, quantidade, preco_unitario, usuario_id, observacoes) VALUES (?, 'venda', ?, ?, ?, ?)", 
                        [$peca_id, $quantidade, $peca['preco_venda'], auth()['id'], 'Venda balcão']);
                    
                    flash('success', 'Venda realizada: ' . formatMoney($valor_total));
                } else {
                    flash('error', 'Estoque insuficiente');
                }
            }
            header('Location: dashboard.php?page=financeiro');
            exit;
    }
}

// Processar exclusões via GET
if ($action === 'delete_servico') {
    $id = (int)($_GET['sid'] ?? 0);
    $os_id = (int)($_GET['os_id'] ?? 0);
    if ($id > 0 && $os_id > 0) {
        $db->query("DELETE FROM servicos_os WHERE id = ?", [$id]);
        
        $stmt = $db->query("SELECT COALESCE(SUM(valor), 0) + (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?) as total FROM servicos_os WHERE os_id = ?", [$os_id, $os_id]);
        $total = $stmt->fetch()['total'];
        $db->query("UPDATE ordens_servico SET valor_mao_obra = (SELECT COALESCE(SUM(valor), 0) FROM servicos_os WHERE os_id = ?), valor_pecas = (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?), valor_total = ? WHERE id = ?", [$os_id, $os_id, $total, $os_id]);
        
        flash('success', 'Serviço removido');
    }
    header('Location: dashboard.php?page=os_edit&id=' . $os_id);
    exit;
}

if ($action === 'delete_peca') {
    $id = (int)($_GET['pid'] ?? 0);
    $os_id = (int)($_GET['os_id'] ?? 0);
    if ($id > 0 && $os_id > 0) {
        $stmt = $db->query("SELECT peca_id, quantidade FROM pecas_os WHERE id = ?", [$id]);
        $pecaOs = $stmt->fetch();
        
        if ($pecaOs) {
            $db->query("UPDATE pecas SET quantidade = quantidade + ? WHERE id = ?", [$pecaOs['quantidade'], $pecaOs['peca_id']]);
            $db->query("DELETE FROM pecas_os WHERE id = ?", [$id]);
            
            $stmt = $db->query("SELECT COALESCE(SUM(valor), 0) + (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?) as total FROM servicos_os WHERE os_id = ?", [$os_id, $os_id]);
            $total = $stmt->fetch()['total'];
            $db->query("UPDATE ordens_servico SET valor_mao_obra = (SELECT COALESCE(SUM(valor), 0) FROM servicos_os WHERE os_id = ?), valor_pecas = (SELECT COALESCE(SUM(quantidade * valor_unitario), 0) FROM pecas_os WHERE os_id = ?), valor_total = ? WHERE id = ?", [$os_id, $os_id, $total, $os_id]);
            
            flash('success', 'Peça removida');
        }
    }
    header('Location: dashboard.php?page=os_edit&id=' . $os_id);
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Motor Prime - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        dark: '#0a0a0a',
                        darker: '#121212',
                        darkGray: '#1a1a1a',
                        darkMid: '#2a2a2a',
                        primary: '#1e3a5f',
                        primaryLight: '#2d5a87',
                        accent: '#3b82f6',
                        accentLight: '#60a5fa'
                    }
                }
            }
        }
    </script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Segoe UI', system-ui, sans-serif; }
        .dark-bg {
            background: linear-gradient(135deg, #0a0a0a 0%, #1a1a1a 100%);
        }
    </style>
</head>
<body class="dark-bg text-gray-100 min-h-screen">
    <nav class="bg-darker border-b border-gray-800">
        <div class="container mx-auto px-4 py-3">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <span class="text-xl font-bold text-white">Motor Prime</span>
                </div>
                <div class="flex items-center space-x-4">
                    <span class="text-sm text-gray-400"><?= auth()['nome'] ?> (<?= auth()['cargo'] ?>)</span>
                    <a href="login.php?action=logout" class="bg-red-900 hover:bg-red-800 px-3 py-1 rounded text-sm text-red-400">Sair</a>
                </div>
            </div>
        </div>
    </nav>
    
    <div class="container mx-auto px-4 py-6">
        <div class="flex flex-wrap gap-2 mb-6">
            <a href="dashboard.php" class="px-4 py-2 rounded <?= $page === 'dashboard' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Dashboard</a>
            <a href="dashboard.php?page=clientes" class="px-4 py-2 rounded <?= $page === 'clientes' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Clientes</a>
            <a href="dashboard.php?page=veiculos" class="px-4 py-2 rounded <?= $page === 'veiculos' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Veículos</a>
            <?php if (isAdmin()): ?>
            <a href="dashboard.php?page=funcionarios" class="px-4 py-2 rounded <?= $page === 'funcionarios' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Funcionários</a>
            <?php endif; ?>
            <a href="dashboard.php?page=fornecedores" class="px-4 py-2 rounded <?= $page === 'fornecedores' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Fornecedores</a>
            <a href="dashboard.php?page=os" class="px-4 py-2 rounded <?= strpos($page, 'os') === 0 ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Ordens de Serviço</a>
            <a href="dashboard.php?page=estoque" class="px-4 py-2 rounded <?= $page === 'estoque' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Estoque</a>
            <a href="dashboard.php?page=financeiro" class="px-4 py-2 rounded <?= $page === 'financeiro' ? 'bg-accent text-white' : 'bg-darkGray text-gray-300 hover:bg-darkMid' ?>">Financeiro</a>
        </div>
        
        <?php if (flash('error')): ?>
            <div class="bg-red-900/30 border border-red-800 text-red-400 px-4 py-3 rounded mb-4"><?= flash('error') ?></div>
        <?php endif; ?>
        <?php if (flash('success')): ?>
            <div class="bg-green-900/30 border border-green-800 text-green-400 px-4 py-3 rounded mb-4"><?= flash('success') ?></div>
        <?php endif; ?>
        
        <?php include __DIR__ . '/views/page_' . $page . '.php'; ?>
    </div>
    
    <script src="assets/js/main.js"></script>
</body>
</html>