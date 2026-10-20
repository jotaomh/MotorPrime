
## FASE 0
- Carregamento .env corrigido para usar Dotenv da raiz, removidos defaults inseguros, DEBUG booleano, bootstrap único criado.
- BASE_URL e função url() adicionados; redirects em middleware ajustados.
- .env removido do índice git e .env.example atualizado.

## FASE 1
- Criados database/schema.sql, migrations/, seed.sql (idempotente), scripts/migrate.php, scripts/create-admin.php.
- Tabelas tokens_cadastro, pedidos/pedido_itens, solicitacoes_servico adicionadas; clientes com senha NULL e email UNIQUE; CPF/CNPJ em dígitos previstos.
- ON DELETE CASCADE trocado por RESTRICT nas relações históricas; índices adicionados; CHECKs básicos.
- Documentação em docs/database.md com diagrama ER Mermaid.

## FASE 2
- dashboard.php: view path corrigido para app/views, whitelist de páginas, faturamento usa data de conclusão/entrega.
- cliente_mecanica.php: INSERT corrigido para solicitacoes_servico, valida veículo pertence ao cliente, GET checks.
- admin_pedidos.php: query única, valida status contra whitelist, CSRF.
- api.php: protegido por sessão/permissão.
- flash unificado (ler e limpar).

## Observações
- Alguns arquivos ainda usam session_start() direto e estruturas antigas; migração gradual conforme necessidade.
