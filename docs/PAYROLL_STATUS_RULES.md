# 📋 Regras de Status da Folha de Pagamento

## Status Disponíveis

A folha de pagamento pode ter 3 status diferentes:

| Status | Descrição | Permite Alterações |
|--------|-----------|-------------------|
| **draft** | Rascunho | ✅ Sim |
| **processing** | Processando | ✅ Sim |
| **completed** | Fechada | ❌ Não |

## Regras de Negócio

### Status: `draft` (Rascunho)
- ✅ Permite todas as operações
- ✅ Permite registrar/editar pagamentos
- ✅ Permite atualizar folha (adicionar/remover colaboradores)
- ✅ Permite fechar a folha

### Status: `processing` (Processando)
- ✅ Permite todas as operações
- ✅ Permite registrar/editar pagamentos
- ✅ Permite atualizar folha (adicionar/remover colaboradores)
- ✅ Permite fechar a folha

### Status: `completed` (Fechada)
- ❌ **NÃO permite** registrar novos pagamentos
- ❌ **NÃO permite** editar pagamentos existentes
- ❌ **NÃO permite** atualizar folha (adicionar/remover colaboradores)
- ❌ **NÃO permite** marcar como pago/desmarcar
- ✅ Permite apenas **reabrir** a folha (volta para `draft`)

## Validações Implementadas

### Backend

1. **PayrollService**:
   - `registerPayment()`: Valida se folha pode ser editada
   - `updateEntry()`: Valida se folha pode ser editada
   - `markAsPaid()`: Valida se folha pode ser editada
   - `updatePayroll()`: Valida se folha pode ser atualizada

2. **PayrollController**:
   - `storeEntry()`: Retorna erro 403 se folha estiver fechada
   - `updateEntry()`: Retorna erro 403 se folha estiver fechada
   - `update()`: Retorna erro se folha estiver fechada

3. **Payroll Model**:
   - `canBeEdited()`: Retorna `false` se status for `completed`
   - `canBeUpdated()`: Retorna `false` se status for `completed`
   - `canModifyEntries()`: Retorna `false` se status for `completed`

### Frontend

1. **Show.vue**:
   - Botão "Atualizar Folha" oculto quando status é `completed`
   - Validação antes de abrir modal de pagamento

2. **EmployeePayrollTable.vue**:
   - Linhas da tabela desabilitadas quando status é `completed`
   - Cursor muda para `not-allowed` quando não pode editar

3. **PaymentEntryModal.vue**:
   - Aviso visual quando folha está fechada
   - Formulário desabilitado quando folha está fechada
   - Botão salvar desabilitado quando folha está fechada

## Fluxo de Status

```
[draft] ──(fechar)──> [completed] ──(reabrir)──> [draft]
   │
   └──(processar)──> [processing] ──(fechar)──> [completed]
```

## Mensagens de Erro

Quando uma operação é tentada em uma folha fechada:

- **Backend**: `"Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações."`
- **Frontend**: Alerta visual no modal e mensagem de erro

