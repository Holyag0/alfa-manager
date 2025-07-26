# Módulo Comercial - Alfa Manager

## Visão Geral

O módulo comercial permite gerenciar serviços e pacotes oferecidos pela escola, facilitando a criação de combinações de serviços com preços especiais.

## Estrutura do Módulo

### Serviços (`services`)
- **Nome**: Nome do serviço
- **Preço**: Valor em decimal
- **Categoria**: Categorização do serviço
- **Status**: Ativo/Inativo
- **Descrição**: Descrição opcional

### Pacotes (`packages`)
- **Nome**: Nome do pacote
- **Preço**: Valor em decimal
- **Categoria**: Categorização do pacote
- **Status**: Ativo/Inativo
- **Descrição**: Descrição opcional
- **Serviços**: Relacionamento many-to-many com serviços

## Rotas Disponíveis

### Serviços
- `GET /services` - Listar serviços
- `GET /services/create` - Formulário de criação
- `POST /services` - Criar serviço
- `GET /services/{id}` - Visualizar serviço
- `GET /services/{id}/edit` - Formulário de edição
- `PUT /services/{id}` - Atualizar serviço
- `DELETE /services/{id}` - Excluir serviço
- `GET /comercial/services/{id}/toggle-status` - Alternar status
- `GET /comercial/services/categories/list` - Listar categorias

### Pacotes
- `GET /packages` - Listar pacotes
- `GET /packages/create` - Formulário de criação
- `POST /packages` - Criar pacote
- `GET /packages/{id}` - Visualizar pacote
- `GET /packages/{id}/edit` - Formulário de edição
- `PUT /packages/{id}` - Atualizar pacote
- `DELETE /packages/{id}` - Excluir pacote
- `GET /comercial/packages/{id}/toggle-status` - Alternar status
- `GET /comercial/packages/{id}/services` - Listar serviços do pacote
- `POST /comercial/packages/{id}/services/{service}` - Adicionar serviço ao pacote
- `DELETE /comercial/packages/{id}/services/{service}` - Remover serviço do pacote
- `GET /comercial/packages/categories/list` - Listar categorias

## Componentes Vue

### Serviços
- `Comercial/Services/Index.vue` - Listagem principal
- `Comercial/Services/Create.vue` - Criação
- `Comercial/Services/Edit.vue` - Edição
- `Comercial/Services/Show.vue` - Visualização
- `Comercial/Services/components/ServiceFilters.vue` - Filtros
- `Comercial/Services/components/ServiceTable.vue` - Tabela
- `Comercial/Services/components/ServiceForm.vue` - Formulário

### Pacotes
- `Comercial/Packages/Index.vue` - Listagem principal
- `Comercial/Packages/Create.vue` - Criação
- `Comercial/Packages/Edit.vue` - Edição
- `Comercial/Packages/Show.vue` - Visualização
- `Comercial/Packages/components/PackageFilters.vue` - Filtros
- `Comercial/Packages/components/PackageTable.vue` - Tabela
- `Comercial/Packages/components/PackageForm.vue` - Formulário
- `Comercial/Packages/components/ServiceSelector.vue` - Seletor de serviços

## Controllers

- `Comercial/ServiceController.php` - Gerenciamento de serviços
- `Comercial/PackageController.php` - Gerenciamento de pacotes

## Services

- `ServiceService.php` - Lógica de negócio para serviços
- `PackageService.php` - Lógica de negócio para pacotes

## Models

- `Service.php` - Modelo de serviço
- `Package.php` - Modelo de pacote

## Funcionalidades Especiais

### Cálculo de Desconto
Os pacotes calculam automaticamente:
- **Valor total dos serviços**: Soma dos preços dos serviços incluídos
- **Desconto**: Diferença entre valor total e preço do pacote
- **Percentual de desconto**: Calculado automaticamente

### Sugestão de Preço
Ao selecionar múltiplos serviços em um pacote, o sistema sugere um preço com 10% de desconto.

### Status Dinâmico
- **Ativo**: Disponível para uso
- **Inativo**: Temporariamente indisponível

## Dados de Teste

O seeder `ComercialSeeder` cria dados de exemplo:

### Serviços
- Berçário, Maternal, Jardim I, Jardim II
- Inglês, Música, Ballet, Judô
- Alimentação, Transporte, Período Integral, Material Didático

### Pacotes
- Pacote Berçário Completo
- Pacote Maternal Completo
- Pacote Jardim Completo
- Pacote Atividades
- Pacote Esportivo
- Pacote Premium

## Testes

Execute os testes do módulo comercial:
```bash
php artisan test --filter=ComercialTest
```

## Menu de Navegação

O módulo está integrado ao menu principal em `Comercial` com submenu para:
- **Serviços**: Gerenciamento de serviços
- **Pacotes**: Gerenciamento de pacotes

## Próximos Passos

1. **Integração com Matrículas**: Vincular serviços/pacotes às matrículas
2. **Relatórios**: Relatórios de vendas e faturamento
3. **Permissões**: Controle de acesso por roles
4. **Notificações**: Alertas de status e preços
5. **API**: Endpoints para integração externa 