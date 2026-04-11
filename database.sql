-- Motor Prime - Banco de Dados
-- Criado em: 2026-04-11

-- Tabela de usuários
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    cargo ENUM('admin', 'mecanico', 'vendedor') DEFAULT 'vendedor',
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cpf VARCHAR(14) UNIQUE,
    cnpj VARCHAR(18) UNIQUE,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(150),
    endereco VARCHAR(255),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de veículos
CREATE TABLE IF NOT EXISTS veiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    placa VARCHAR(8) NOT NULL UNIQUE,
    marca VARCHAR(50) NOT NULL,
    modelo VARCHAR(50) NOT NULL,
    ano INT NOT NULL,
    cor VARCHAR(30),
    km INT DEFAULT 0,
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de fornecedores
CREATE TABLE IF NOT EXISTS fornecedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    cnpj VARCHAR(18) UNIQUE,
    telefone VARCHAR(20),
    email VARCHAR(150),
    endereco VARCHAR(255),
    observacoes TEXT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Tabela de peças/estoque
CREATE TABLE IF NOT EXISTS pecas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(30) NOT NULL UNIQUE,
    descricao VARCHAR(200) NOT NULL,
    marca VARCHAR(50),
    categoria VARCHAR(50),
    preco_custo DECIMAL(10,2) DEFAULT 0,
    preco_venda DECIMAL(10,2) DEFAULT 0,
    quantidade INT DEFAULT 0,
    estoque_minimo INT DEFAULT 5,
    fornecedor_id INT,
    ativo TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (fornecedor_id) REFERENCES fornecedores(id) ON DELETE SET NULL
) ENGINE=InnoDB;

-- Tabela de ordens de serviço
CREATE TABLE IF NOT EXISTS ordens_servico (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    veiculo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    status ENUM('orcamento', 'aprovado', 'em_execucao', 'finalizado', 'entregue') DEFAULT 'orcamento',
    descricao TEXT,
    valor_mao_obra DECIMAL(10,2) DEFAULT 0,
    valor_pecas DECIMAL(10,2) DEFAULT 0,
    valor_total DECIMAL(10,2) DEFAULT 0,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao') DEFAULT 'dinheiro',
    data_entrada DATE DEFAULT (CURRENT_DATE),
    data_fim DATE,
    data_entrega DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (veiculo_id) REFERENCES veiculos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de serviços realizados na OS
CREATE TABLE IF NOT EXISTS servicos_os (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    descricao VARCHAR(200) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de peças usadas na OS
CREATE TABLE IF NOT EXISTS pecas_os (
    id INT AUTO_INCREMENT PRIMARY KEY,
    os_id INT NOT NULL,
    peca_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (os_id) REFERENCES ordens_servico(id) ON DELETE CASCADE,
    FOREIGN KEY (peca_id) REFERENCES pecas(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de estoque (entradas de peças)
CREATE TABLE IF NOT EXISTS estoque_movimentacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peca_id INT NOT NULL,
    tipo ENUM('entrada', 'saida', 'venda') NOT NULL,
    quantidade INT NOT NULL,
    preco_unitario DECIMAL(10,2),
    observacoes VARCHAR(255),
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peca_id) REFERENCES pecas(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Tabela de vendas direto (balcão)
CREATE TABLE IF NOT EXISTS vendas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    peca_id INT NOT NULL,
    quantidade INT NOT NULL DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    forma_pagamento ENUM('dinheiro', 'pix', 'cartao') DEFAULT 'dinheiro',
    cliente_id INT,
    usuario_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (peca_id) REFERENCES pecas(id) ON DELETE CASCADE,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE SET NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Inserir usuárioAdmin padrão
INSERT INTO usuarios (nome, email, senha, cargo) VALUES 
('Administrador', 'admin@motorprime.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

-- Inserir fornecedores de exemplo
INSERT INTO fornecedores (nome, cnpj, telefone, email) VALUES 
('AutoParts Distribuidora', '12.345.678/0001-90', '(11) 99999-0001', 'contato@autoparts.com.br'),
('MegaPeças Brasil', '23.456.789/0001-01', '(11) 99999-0002', 'vendas@megapecas.com.br');

-- Inserir peças de exemplo
INSERT INTO pecas (codigo, descricao, marca, categoria, preco_custo, preco_venda, quantidade, estoque_minimo, fornecedor_id) VALUES 
('OLEO5W30', 'Óleo Lubrificante 5W-30 Synthetic', 'Mobil', 'Óleos', 45.00, 79.90, 50, 10, 1),
('FILTRO-001', 'Filtro de Óleo Gol/Golf', 'Bosch', 'Filtros', 25.00, 49.90, 30, 10, 1),
('FILTRO-002', 'Filtro de Ar Gol/Golf', 'Bosch', 'Filtros', 18.00, 39.90, 40, 15, 1),
('FILTRO-003', 'Filtro de Combustível Fiat', 'Bosch', 'Filtros', 32.00, 64.90, 20, 8, 2),
('PAST-001', 'Pastilha de Freio Dianteira', 'Cobreq', 'Freios', 55.00, 110.00, 25, 10, 2),
('VELA-001', 'Vela de Ignição', 'Bosch', 'Ignição', 15.00, 35.00, 60, 20, 1),
('BATERIA-45', 'Bateria 45Ah', ' Moura', 'Elétrica', 180.00, 350.00, 8, 3, 2),
('AMORT-001', 'Amortecedor Dianteiro', 'Cofap', 'Suspensão', 120.00, 240.00, 12, 5, 2);

-- Inserir clientes de exemplo
INSERT INTO clientes (nome, cpf, telefone, email, endereco) VALUES 
('José da Silva', '123.456.789-00', '(11) 99999-1111', 'jose@email.com', 'Rua das Flores, 123 - São Paulo/SP'),
('Maria dos Santos', '987.654.321-00', '(11) 99999-2222', 'maria@email.com', 'Av. Brasil, 456 - São Paulo/SP'),
('Pedro Almeida', '456.789.123-00', '(11) 99999-3333', 'pedro@email.com', 'Rua Nova, 789 - São Paulo/SP');

-- Inserir veículos de exemplo (vinculados aos clientes)
INSERT INTO veiculos (cliente_id, placa, marca, modelo, ano, cor, km) VALUES 
(1, 'ABC-1234', 'Volkswagen', 'Gol', 2020, 'Preto', 45000),
(1, 'DEF-5678', 'Chevrolet', 'Onix', 2022, 'Branco', 25000),
(2, 'GHI-9012', 'Fiat', 'Uno', 2018, 'Prata', 78000),
(3, 'JKL-3456', 'Ford', 'Ka', 2021, 'Vermelho', 32000);