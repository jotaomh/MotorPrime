# Motor Prime - Sistema de Gestão para Oficina Mecânica

Sistema web completo para gestão de oficina mecânica e loja de autopeças desenvolvido em PHP 8.2+ com arquitetura MVC.

## Tecnologias

- **Backend**: PHP 8.2+ (POO)
- **Frontend**: HTML5, CSS3, JavaScript (Vanilla)
- **Estilização**: Tailwind CSS (via CDN)
- **Banco de Dados**: MySQL / MariaDB com PDO

## Estrutura de Pastas

```
motor-prime/
├── public/
│   ├── index.php          # Redirecionamento
│   ├── login.php         # Página de login
│   ├── dashboard.php    # Dashboard principal (single-page)
│   ├── api.php         # API REST para buscas AJAX
│   └── assets/
│       └── js/main.js   # JavaScript (máscaras, modais)
├── app/
│   ├── config/
│   │   └── database.php     # Conexão PDO + helpers
│   ├── controllers/         # Controladores (MVC)
│   │   ├── AuthController.php
│   │   ├── ClienteController.php
│   │   ├── DashboardController.php
│   │   ├── FinanceiroController.php
│   │   ├── FornecedorController.php
│   │   ├── FuncionarioController.php
│   │   ├── OSController.php
│   │   ├── PecaController.php
│   │   └── VeiculoController.php
│   ├── middleware/
│   │   └── auth.php        # Middleware de autenticação
│   └── views/
│       ├── page_dashboard.php
│       ├── page_clientes.php
│       ├── page_veiculos.php
│       ├── page_funcionarios.php
│       ├── page_fornecedores.php
│       ├── page_os.php
│       ├── page_os_edit.php
│       ├── page_estoque.php
│       └── page_financeiro.php
├── database.sql          # Script do banco + dados de exemplo
├── .env                # Variáveis de ambiente
├── .env.example        # Modelo de configuração
├── composer.json       # Dependências (opcional)
└── README.md          # Este arquivo
```

## Instalação

### 1. Copie o projeto

```bash
cd /var/www/html
# Clone ou copie a pasta motor-prime para cá
```

### 2. Configure o banco de dados

2.1. Crie o banco de dados:
```bash
mysql -u root -p < motor-prime/database.sql
```

2.2. Edite o arquivo `.env` com suas configurações MySQL:
```env
DB_HOST=localhost
DB_NAME=motor_prime
DB_USER=root
DB_PASS=sua_senha_aqui
DB_CHARSET=utf8mb4
```

### 3. Configure o servidor web

Configure o Apache ou Nginx para servir a pasta `public/`.

**Exemplo Apache (VirtualHost):**
```apache
<VirtualHost *:80>
    DocumentRoot /var/www/html/motor-prime/public
    ServerName motorprime.local
    <Directory /var/www/html/motor-prime/public>
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
```

**Ou use o .htaccess na pasta public/:**
```apache
RewriteEngine On
RewriteBase /motor-prime/public/
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^ index.php [L]
```

### 4. Acesse o sistema

- **URL**: http://localhost/motor-prime/public/login.php
- **Usuário Admin**: admin@motorprime.com
- **Senha**: password

## Funcionalidades

### 🔐 Autenticação
- Login com email e senha (password_hash/BCRYPT)
- Sessões PHP seguras
- Níveis de acesso: Admin, Mecânico, Vendedor
- Apenas Admin gerencia usuários

### 📊 Dashboard
- Cards informativos: OS em andamento, peças estoque baixo
- Faturamento do dia e do mês
- Status das OS (Orçamento, Aprovado, Em Execução, Finalizado, Entregue)
- Lista de OS recentes

### 👥 Clientes
- Cadastro: Nome, CPF/CNPJ, telefone, email, endereço, observações
- Máscaras automáticas para CPF, CNPJ, telefone
- Edição inline via modal

### 🚗 Veículos
- Cadastro vinculado ao cliente
- Placa formato brasileiro (ABC-1234)
- Marca, modelo, ano, cor, KM
- Busca por cliente

### 👷 Funcionários (Admin)
- Nome, email, cargo (Admin/Mecânico/Vendedor)
- Apenas Admin pode gerenciar
- Não pode excluir a si mesmo

### 📦 Fornecedores
- Nome, CNPJ, telefone, email, endereço
- Vinculados às peças

### 🔧 Ordens de Serviço
- Criar OS vinculada a Cliente + Veículo
- Adicionar serviços (mão de obra) com valores
- Adicionar peças (desconta automaticamente do estoque)
- Status流转: Orçamento → Aprovado → Em Execução → Finalizado → Entregue
- Cálculo automático: mão de obra + peças = total
- Forma de pagamento: Dinheiro, PIX, Cartão

### 📦 Estoque de Peças
- Código, descrição, marca, categoria
- Preço custo e preço venda
- Controle de quantidade
- Estoque mínimo (alerta automático)
- Entrada de peças (compra)
- Histórico de movimentações
- Venda de balcão

### 💰 Financeiro
- Relatório de faturamento por período
- Total de OS e valor total
- Média por OS
- Venda direta balcão
- Faturamento por forma de pagamento

## Estrutura do Banco

### Tabelas:
| Tabela | Descrição |
|--------|-----------|
| `usuarios` | Usuários do sistema (login) |
| `clientes` | Clientes |
| `veiculos` | Veículos dos clientes |
| `fornecedores` | Fornecedores de peças |
| `pecas` | Peças em estoque |
| `ordens_servico` | Ordens de serviço |
| `servicos_os` | Serviços realizados na OS |
| `pecas_os` | Peças usadas na OS |
| `estoque_movimentacoes` | Histórico de entrada/saída |
| `vendas` | Vendas diretas (balcão) |

### Dados de Exemplo Incluídos

O `database.sql`Popula:
- 1 usuário admin (admin@motorprime.com / password)
- 2 fornecedores
- 8 peças de exemplo
- 3 clientes
- 4 veículos

##API Endpoints

Buscas AJAX disponíveis:

| Endpoint | Parâmetros | Descrição |
|---------|------------|----------|
| `api.php?action=veiculos&cliente_id=X` | - | Lista veículos do cliente |
| `api.php?action=clientes&q=termo` | - | Busca clientes |
| `api.php?action=pecas&q=termo` | - | Busca peças |

## Observações

- PDO com prepared statements (segurança contra SQLi)
- Senhas hasheadas com BCRYPT
- Formatações brasileiras automáticas:
  - R$ (moeda): 1.234,56
  - CPF: 000.000.000-00
  - CNPJ: 00.000.000/0001-00
  - Telefone: (00) 00000-0000
  - Placa: ABC-1234
- Design responsivo (mobile-first)
- Código em português brasileiro
- Tailwind CSS via CDN (sem build necessário)

## Requisitos do Servidor

- PHP 8.2+
- MySQL 5.7+ ou MariaDB 10.3+
- Extensões: pdo_mysql, session
- Apache/Nginx com mod_rewrite (opcional)

## Troubleshooting

### Erro de conexão com banco
Verifique as credenciais no arquivo `.env` e se o MySQL está rodando.

### Erro de sessão
Certifique-se que o diretório tem permissão de escrita ou que o PHP pode criar arquivos de sessão.

### CSS não carrega
Verifique a conexão com a internet (Tailwind CDN) ou configure um CSSoffline.

## Licença

MIT License - Feel free to use!