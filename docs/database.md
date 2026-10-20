# Documentação do Banco de Dados - Motor Prime

## Schema

Consulte o arquivo `database/schema.sql` para a estrutura completa.

## ER em Mermaid

```mermaid
erDiagram
    USUARIOS {
        int id PK
        varchar nome
        varchar email UK
        varchar senha
        enum cargo
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    CLIENTES {
        int id PK
        varchar nome
        varchar cpf UK
        varchar cnpj UK
        varchar telefone
        varchar email UK
        varchar senha
        varchar endereco
        text observacoes
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    VEICULOS {
        int id PK
        int cliente_id FK
        varchar placa UK
        varchar marca
        varchar modelo
        int ano
        varchar cor
        int km
        text observacoes
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    FORNECEDORES {
        int id PK
        varchar nome
        varchar cnpj UK
        varchar telefone
        varchar email
        varchar endereco
        text observacoes
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    PECAS {
        int id PK
        varchar codigo UK
        varchar descricao
        varchar marca
        varchar categoria
        decimal preco_custo
        decimal preco_venda
        int quantidade
        int estoque_minimo
        int fornecedor_id FK
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    ORDENS_SERVICO {
        int id PK
        int cliente_id FK
        int veiculo_id FK
        int usuario_id FK
        enum status
        text descricao
        decimal valor_mao_obra
        decimal valor_pecas
        decimal valor_total
        enum forma_pagamento
        date data_entrada
        date data_fim
        date data_entrega
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    SERVICOS_OS {
        int id PK
        int os_id FK
        varchar descricao
        decimal valor
        timestamp created_at
    }
    PECAS_OS {
        int id PK
        int os_id FK
        int peca_id FK
        int quantidade
        decimal valor_unitario
        timestamp created_at
    }
    ESTOQUE_MOVIMENTACOES {
        int id PK
        int peca_id FK
        enum tipo
        int quantidade
        decimal preco_unitario
        varchar observacoes
        int usuario_id FK
        timestamp created_at
    }
    VENDAS {
        int id PK
        int peca_id FK
        int quantidade
        decimal valor_unitario
        decimal valor_total
        enum forma_pagamento
        int cliente_id FK
        int usuario_id FK
        tinyint ativo
        timestamp created_at
    }
    TOKENS_CADASTRO {
        int id PK
        varchar token UK
        enum cargo
        int created_by FK
        datetime expires_at
        datetime used_at
        timestamp created_at
    }
    PEDIDOS {
        int id PK
        int cliente_id FK
        enum status
        decimal valor_total
        enum forma_pagamento
        text observacoes
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }
    PEDIDO_ITENS {
        int id PK
        int pedido_id FK
        int peca_id FK
        int quantidade
        decimal valor_unitario
        timestamp created_at
    }
    SOLICITACOES_SERVICO {
        int id PK
        int cliente_id FK
        int veiculo_id FK
        varchar tipo_servico
        text descricao
        enum situacao
        text observacoes
        tinyint ativo
        timestamp created_at
        timestamp updated_at
    }

    CLIENTES ||--o{ VEICULOS : possui
    CLIENTES ||--o{ ORDENS_SERVICO : solicitou
    VEICULOS ||--o{ ORDENS_SERVICO : referente
    USUARIOS ||--o{ ORDENS_SERVICO : atende
    ORDENS_SERVICO ||--o{ SERVICOS_OS : tem
    ORDENS_SERVICO ||--o{ PECAS_OS : usa
    PECAS ||--o{ PECAS_OS : incluida
    PECAS ||--o{ ESTOQUE_MOVIMENTACOES : movimenta
    PECAS ||--o{ PEDIDO_ITENS : item
    PECAS ||--o{ VENDAS : vendida
    FORNECEDORES ||--o{ PECAS : fornece
    CLIENTES ||--o{ PEDIDOS : faz
    PEDIDOS ||--o{ PEDIDO_ITENS : contem
    CLIENTES ||--o{ SOLICITACOES_SERVICO : solicita
    VEICULOS ||--o{ SOLICITACOES_SERVICO : veiculo
    USUARIOS ||--o{ TOKENS_CADASTRO : gera
