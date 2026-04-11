# Motor Prime - Sistema de Gestão para Oficina Mecânica

Sistema web completo para gestão de oficina mecânica e loja de autopeças.

## Tecnologias

- **Backend**: PHP 8.2+ (POO)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Estilização**: Tailwind CSS (via CDN)
- **Banco de Dados**: MySQL / MariaDB

## Estrutura de Pastas

```
motor-prime/
├── public/
│   ├── index.php          # Página institucional
│   ├── login.php          # Login de funcionários
│   ├── cadastrar.php      # Cadastro de funcionários (via token)
│   ├── dashboard.php      # Painel administrativo
│   ├── cliente.php         # Login/Cadastro cliente
│   ├── cliente_dashboard.php  # Área do cliente
│   ├── admin_tokens.php   # Gerenciar tokens (Admin)
│   ├── api.php           # API REST
│   └── assets/js/main.js
├── app/
│   ├── config/database.php
│   ├── controllers/
│   ├── helpers/Permissao.php
│   ├── middleware/
│   └── views/page_*.php
├── database.sql
├── .env
└── README.md
```

## Instalação

### 1. Configurar Banco

```bash
mysql -u root -pSUA_SENHA < motor-prime/database.sql
```

### 2. Configurar .env

```env
DB_HOST=localhost
DB_NAME=motor_prime
DB_USER=root
DB_PASS=sua_senha
DB_CHARSET=utf8mb4
```

### 3. Acessar

- **Sistema**: `http://localhost/motor-prime/public/`
- **Login Admin**: `admin@motorprime.com` / `password`

## Fluxo de Usuário

### Área Pública
1. **Index** (`index.php`) - Apresentação da empresa
2. **Área do Cliente** (`cliente.php`) - Login/cadastro de clientes
3. **Login Funcionário** (`login.php`) - Login de funcionários

### Sistema Interno
1. **Admin** (Dashboard completo)
   - Dashboard, Clientes, Veículos, Funcionários, Fornecedores, OS, Estoque, Financeiro, Pedidos, Tokens

2. **Mecânico**
   - Dashboard, Clientes, Veículos, Ordens de Serviço

3. **Vendedor**
   - Dashboard, Clientes, Veículos, Estoque, Financeiro

## Sistema de Tokens

Para cadastrar novos funcionários:

1. Admin acessa **Dashboard** → **Tokens**
2. Gera token para cargo específico (Mecânico/Vendedor)
3. Compartilha link: `cadastrar.php?token=TOKEN`
4. Funcionário completa o cadastro

## Funcionalidades

| Módulo | Descrição |
|--------|------------|
| Dashboard | Visão geral com métricas |
| Clientes | CRUD completo |
| Veículos | Vinculados a clientes |
| Funcionários | Apenas Admin |
| Fornecedores | CRUD completo |
| Ordens de Serviço | OS completa com serviços e peças |
| Estoque | Controle de peças |
| Financeiro | Faturamento e vendas |

## Banco de Dados

Tabelas principais:
- `usuarios` - Funcionários do sistema
- `clientes` - Clientes
- `veiculos` - Veículos dos clientes
- `fornecedores` - Fornecedores
- `pecas` - Peças em estoque
- `ordens_servico` - Ordens de serviço
- `tokens_cadastro` - Tokens para cadastrar funcionários

## Credits

- Desenvolvido em PHP 8.2+
- Tailwind CSS via CDN
- PDO para banco de dados