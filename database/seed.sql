-- Seed idempotente
SET FOREIGN_KEY_CHECKS=0;

-- Não inserir admin com senha conhecida - usar scripts/create-admin.php
-- Limpar dados de exemplo com espaços
UPDATE pecas SET descricao = TRIM(descricao) WHERE descricao LIKE ' %' OR descricao LIKE '% ';

-- Dados mínimos idempotentes
INSERT IGNORE INTO fornecedores (nome, cnpj, telefone, email) VALUES 
('AutoParts Distribuidora', '12345678000190', '11999990001', 'contato@autoparts.com.br'),
('MegaPeças Brasil', '23456789000101', '11999990002', 'vendas@megapecas.com.br');

INSERT IGNORE INTO pecas (codigo, descricao, marca, categoria, preco_custo, preco_venda, quantidade, estoque_minimo, fornecedor_id) VALUES 
('OLEO5W30', 'Óleo Lubrificante 5W-30 Synthetic', 'Mobil', 'Óleos', 45.00, 79.90, 50, 10, 1),
('FILTRO-001', 'Filtro de Óleo Gol/Golf', 'Bosch', 'Filtros', 25.00, 49.90, 30, 10, 1),
('FILTRO-002', 'Filtro de Ar Gol/Golf', 'Bosch', 'Filtros', 18.00, 39.90, 40, 15, 1),
('FILTRO-003', 'Filtro de Combustível Fiat', 'Bosch', 'Filtros', 32.00, 64.90, 20, 8, 2),
('PAST-001', 'Pastilha de Freio Dianteira', 'Cobreq', 'Freios', 55.00, 110.00, 25, 10, 2),
('VELA-001', 'Vela de Ignição', 'Bosch', 'Ignição', 15.00, 35.00, 60, 20, 1),
('BATERIA-45', 'Bateria 45Ah', 'Moura', 'Elétrica', 180.00, 350.00, 8, 3, 2),
('AMORT-001', 'Amortecedor Dianteiro', 'Cofap', 'Suspensão', 120.00, 240.00, 12, 5, 2);

INSERT IGNORE INTO clientes (nome, cpf, telefone, email, endereco) VALUES 
('José da Silva', '12345678900', '11999991111', 'jose@email.com', 'Rua das Flores, 123 - São Paulo/SP'),
('Maria dos Santos', '98765432100', '11999992222', 'maria@email.com', 'Av. Brasil, 456 - São Paulo/SP'),
('Pedro Almeida', '45678912300', '11999993333', 'pedro@email.com', 'Rua Nova, 789 - São Paulo/SP');

INSERT IGNORE INTO veiculos (cliente_id, placa, marca, modelo, ano, cor, km) VALUES 
(1, 'ABC1234', 'Volkswagen', 'Gol', 2020, 'Preto', 45000),
(1, 'DEF5678', 'Chevrolet', 'Onix', 2022, 'Branco', 25000),
(2, 'GHI9012', 'Fiat', 'Uno', 2018, 'Prata', 78000),
(3, 'JKL3456', 'Ford', 'Ka', 2021, 'Vermelho', 32000);

SET FOREIGN_KEY_CHECKS=1;
