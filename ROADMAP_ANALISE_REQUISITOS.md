# 📋 ROADMAP - ANÁLISE DE REQUISITOS DE NEGÓCIO
## Sistema de Gestão Escolar - Alfa Manager

---

## 🔍 **ANÁLISE DOS REQUISITOS DE NEGÓCIO**

### **Requisitos Identificados nas Imagens:**

#### **1. Estrutura de Preços por Ano Escolar:**
- **INFANTIL II**: R$ 460,00 (sem irmão) / R$ 430,00 (com irmão)
- **INFANTIL III, IV, V**: R$ 450,00 (sem irmão) / R$ 420,00 (com irmão)  
- **1º ANO a 5º ANO**: R$ 465,00 (sem irmão) / R$ 435,00 (com irmão)

#### **2. Estrutura de Matrícula:**
- **1ª Mensalidade/Matrícula**: Valores diferenciados por ano escolar
- **Reserva**: Valores específicos por turma
- **Restante Matrícula**: Valores fixos por categoria

#### **3. Sistema de Descontos:**
- **Desconto por irmão**: R$ 20,00 a R$ 30,00 nas mensalidades
- **Desconto por pontualidade**: R$ 10,00 no pagamento em dia
- **Promoção geral**: R$ 30,00 a R$ 55,00 de desconto

---

## ✅ **CAPACIDADES ATUAIS DO SISTEMA**

### **1. Sistema de Matrícula** ✅ **IMPLEMENTADO**
- ✅ Processo de matrícula com wizard
- ✅ Vinculação aluno-responsável-turma
- ✅ Controle de vagas por turma
- ✅ Status de matrícula (pending, active, cancelled)
- ✅ Troca de turma e cancelamento

### **2. Sistema de Serviços** ✅ **IMPLEMENTADO**
- ✅ Gerenciamento de serviços com preços diferenciados
- ✅ Vinculação de serviços a turmas específicas (`ClassroomService`)
- ✅ Categorização de serviços (Mensalidade, Reserva, Matrícula)
- ✅ Sistema de pacotes com descontos

### **3. Sistema Financeiro Básico** ⚠️ **PARCIALMENTE IMPLEMENTADO**
- ✅ Estrutura de faturas (`EnrollmentInvoice`)
- ✅ Sistema de pagamentos (`EnrollmentPayment`)
- ✅ Registro financeiro por matrícula (`EnrollmentFinance`)
- ✅ Geração automática de faturas de reserva e mensalidade
- ❌ **FALTA**: Sistema de receitas/despesas geral
- ❌ **FALTA**: Relatórios financeiros completos

### **4. Sistema de Turmas** ⚠️ **PARCIALMENTE IMPLEMENTADO**
- ✅ Modelo `Classroom` com vagas e turnos
- ✅ Relacionamento com serviços
- ❌ **FALTA**: Controller CRUD completo para turmas
- ❌ **FALTA**: Interface de gestão de turmas
- ❌ **FALTA**: Gestão de alunos por turma

---

## 🚨 **GAPS IDENTIFICADOS**

### **1. Sistema de Descontos por Irmãos** ❌ **NÃO IMPLEMENTADO**
- **Problema**: Não há lógica para detectar irmãos matriculados
- **Impacto**: Não consegue aplicar descontos automáticos
- **Solução**: Implementar sistema de detecção de irmãos

### **2. Sistema de Mensalidades Automáticas** ⚠️ **PARCIAL**
- **Problema**: Gera apenas primeira mensalidade
- **Impacto**: Não gera mensalidades recorrentes
- **Solução**: Implementar job para geração mensal

### **3. Sistema de Receitas e Despesas** ❌ **NÃO IMPLEMENTADO**
- **Problema**: Não há controle financeiro geral da escola
- **Impacto**: Não consegue rastrear receitas automáticas
- **Solução**: Implementar módulo financeiro completo

### **4. CRUD de Turmas** ❌ **NÃO IMPLEMENTADO**
- **Problema**: Não há interface para gerenciar turmas
- **Impacto**: Dificulta gestão de turmas e alunos
- **Solução**: Implementar controller e views para turmas

---

## 🎯 **ROADMAP DE IMPLEMENTAÇÃO**

### **FASE 1: CORREÇÕES CRÍTICAS (Prioridade ALTA) - 2-3 semanas**

#### **1.1 Sistema de Descontos por Irmãos**
```php
// Implementar em EnrollmentFinanceService
public function calculateSiblingDiscount(Enrollment $enrollment)
{
    $guardian = $enrollment->guardian;
    $siblingCount = $guardian->enrollments()
        ->where('status', 'active')
        ->where('id', '!=', $enrollment->id)
        ->count();
    
    return $siblingCount > 0 ? 30.00 : 0;
}
```

**Tarefas:**
- [ ] Adicionar campo `sibling_discount` em `EnrollmentFinance`
- [ ] Implementar lógica de detecção de irmãos
- [ ] Aplicar desconto automático nas faturas
- [ ] Atualizar interface para mostrar desconto

#### **1.2 Sistema de Mensalidades Recorrentes**
```php
// Implementar Job para geração mensal
class GenerateMonthlyInvoices implements ShouldQueue
{
    public function handle()
    {
        $activeEnrollments = Enrollment::where('status', 'active')->get();
        
        foreach ($activeEnrollments as $enrollment) {
            $this->createMonthlyInvoice($enrollment);
        }
    }
}
```

**Tarefas:**
- [ ] Criar Job `GenerateMonthlyInvoices`
- [ ] Configurar cron job para execução mensal
- [ ] Implementar lógica de vencimento (dia 10)
- [ ] Adicionar notificações de vencimento

#### **1.3 CRUD Completo de Turmas**
```php
// Criar ClassroomController
class ClassroomController extends Controller
{
    public function index() { /* Listar turmas */ }
    public function create() { /* Formulário de criação */ }
    public function store() { /* Salvar turma */ }
    public function show($id) { /* Detalhes da turma */ }
    public function edit($id) { /* Formulário de edição */ }
    public function update($id) { /* Atualizar turma */ }
    public function destroy($id) { /* Excluir turma */ }
}
```

**Tarefas:**
- [ ] Criar `ClassroomController` completo
- [ ] Implementar views Vue.js para turmas
- [ ] Adicionar rotas no `web.php`
- [ ] Implementar validações e permissões

### **FASE 2: SISTEMA FINANCEIRO COMPLETO (Prioridade ALTA) - 3-4 semanas**

#### **2.1 Sistema de Receitas e Despesas**
```php
// Criar modelos para controle financeiro
class Revenue extends Model
{
    protected $fillable = [
        'type', 'description', 'amount', 'date', 
        'enrollment_id', 'category', 'status'
    ];
}

class Expense extends Model
{
    protected $fillable = [
        'type', 'description', 'amount', 'date',
        'category', 'status', 'supplier'
    ];
}
```

**Tarefas:**
- [ ] Criar modelos `Revenue` e `Expense`
- [ ] Implementar migrations
- [ ] Criar controllers e services
- [ ] Implementar views para gestão financeira
- [ ] Integrar com sistema de matrículas

#### **2.2 Relatórios Financeiros**
```php
// Implementar ReportService
class ReportService
{
    public function getMonthlyRevenue($month, $year) { }
    public function getEnrollmentRevenue($period) { }
    public function getOverduePayments() { }
    public function getSiblingDiscounts() { }
}
```

**Tarefas:**
- [ ] Criar `ReportService` para relatórios
- [ ] Implementar dashboard financeiro
- [ ] Criar relatórios de inadimplência
- [ ] Implementar exportação PDF/Excel

### **FASE 3: MELHORIAS E OTIMIZAÇÕES (Prioridade MÉDIA) - 2-3 semanas**

#### **3.1 Sistema de Notificações**
- [ ] Notificações de vencimento
- [ ] Alertas de inadimplência
- [ ] Comunicação com responsáveis

#### **3.2 Integração com Pagamentos**
- [ ] Integração com gateways de pagamento
- [ ] Geração de boletos
- [ ] Controle de parcelas

#### **3.3 Dashboard Executivo**
- [ ] Métricas de matrículas
- [ ] Indicadores financeiros
- [ ] Gráficos de receita/despesa

---

## 📊 **ESTRUTURA DE DADOS NECESSÁRIA**

### **Novas Tabelas:**
```sql
-- Sistema de receitas
CREATE TABLE revenues (
    id BIGINT PRIMARY KEY,
    type ENUM('enrollment', 'monthly', 'reservation', 'other'),
    description VARCHAR(255),
    amount DECIMAL(10,2),
    date DATE,
    enrollment_id BIGINT NULL,
    category VARCHAR(100),
    status ENUM('pending', 'confirmed', 'cancelled'),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Sistema de despesas
CREATE TABLE expenses (
    id BIGINT PRIMARY KEY,
    type ENUM('operational', 'staff', 'maintenance', 'other'),
    description VARCHAR(255),
    amount DECIMAL(10,2),
    date DATE,
    category VARCHAR(100),
    status ENUM('pending', 'paid', 'cancelled'),
    supplier VARCHAR(255),
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Descontos por irmãos
ALTER TABLE enrollment_finances 
ADD COLUMN sibling_discount DECIMAL(10,2) DEFAULT 0,
ADD COLUMN sibling_count INT DEFAULT 0;
```

---

## 🎯 **PRIORIDADES DE IMPLEMENTAÇÃO**

### **CRÍTICO (Implementar Imediatamente):**
1. ✅ Sistema de descontos por irmãos
2. ✅ Geração automática de mensalidades
3. ✅ CRUD completo de turmas

### **ALTO (Próximas 4 semanas):**
1. ✅ Sistema de receitas e despesas
2. ✅ Relatórios financeiros básicos
3. ✅ Dashboard financeiro

### **MÉDIO (Próximos 2 meses):**
1. ✅ Sistema de notificações
2. ✅ Integração com pagamentos
3. ✅ Relatórios avançados

### **BAIXO (Futuro):**
1. ✅ Integração com contabilidade
2. ✅ Sistema de auditoria
3. ✅ API para integrações externas

---

## 📈 **MÉTRICAS DE SUCESSO**

### **Funcionalidades:**
- [ ] 100% dos preços das imagens implementados
- [ ] Descontos automáticos funcionando
- [ ] Mensalidades geradas automaticamente
- [ ] Relatórios financeiros completos

### **Performance:**
- [ ] Geração de mensalidades em < 30 segundos
- [ ] Relatórios carregando em < 5 segundos
- [ ] Interface responsiva e intuitiva

### **Qualidade:**
- [ ] Testes unitários para lógica financeira
- [ ] Validações robustas
- [ ] Logs de auditoria completos

---

## 🚀 **PRÓXIMOS PASSOS IMEDIATOS**

1. **Implementar sistema de descontos por irmãos** (1 semana)
2. **Criar CRUD de turmas** (1 semana)  
3. **Implementar geração automática de mensalidades** (1 semana)
4. **Criar sistema básico de receitas/despesas** (2 semanas)

**Total estimado: 5 semanas para funcionalidades críticas**

---

*Este roadmap foi criado baseado na análise completa do sistema atual e nos requisitos de negócio apresentados nas imagens promocionais da Escola Alfa Baby.*
