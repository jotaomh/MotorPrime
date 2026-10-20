# Motor Prime - Sistema de Gestão para Oficina Mecânica

Sistema web completo para gestão de oficina mecânica e loja de autopeças.

## Tecnologias

- **Backend**: PHP 8.2+ (POO)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Estilização**: Tailwind CSS (via CDN - será migrado para build)
- **Banco de Dados**: MySQL / MariaDB

## Estrutura de Pastas

```
motor-prime/
├── app/
│   ├── bootstrap.php
│   ├── config/database.php
│   ├── helpers/
│   │   ├── helpers.php
│   │   └── Permissao.php
│   ├── middleware/auth.php
│   └── views/
├── database/
│   ├── schema.sql
│   ├── seed.sql
│   └── migrations/
├── docs/database.md
├── public/
├── scripts/
│   ├── create-admin.php
│   └── migrate.php
├── storage/logs/
└── vendor/
```

## Instalação

### 1. Clonar e instalar dependências
```bash
composer install
```

### 2. Configurar .env
```bash
cp .env.example .env
```
Edite o arquivo `.env` com as credenciais do banco de dados. DEBUG=false em produção.

### 3. Configurar Banco de Dados
```bash
# Instalação limpa
mysql -u root -p < database/schema.sql

# Ou via migrations (bancos existentes)
php scripts/migrate.php

# Semear dados
mysql -u root -p motor_prime < database/seed.sql

# Criar administrador
php scripts/create-admin.php
```

### 4. Executar
```bash
php -S localhost:8000 -t public
```

## Principais Funções

- Gestão de clientes, veículos, funcionários, fornecedores
- Ordens de Serviço com controle de peças e serviços
- Controle de estoque e movimentações
- Financeiro e vendas
- Área do cliente (login, loja, solicitações de serviço)
- Sistema de tokens para cadastro de funcionários

## Contas de teste (local)
Execute em ambiente local:
```bash
php scripts/seed-dev.php
```
