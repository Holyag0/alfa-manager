# 🎯 ROADMAP: Sistema de Mensalidades e Turmas

## 📊 **ANÁLISE DO SISTEMA ATUAL**

### **✅ O que já existe:**
- **Sistema de Turmas** básico (Classroom model)
- **Sistema de Matrículas** com vinculação a turmas
- **Sistema Financeiro** com faturas e pagamentos
- **Serviços por Turma** (ClassroomService)
- **Pagamentos** com rastreamento de responsável

### **❌ O que falta:**
- **Gestão completa de mensalidades**
- **Sistema de turmas robusto**
- **Interface dedicada para mensalidades**
- **Relatórios financeiros**

---

## 🏗️ **1. SISTEMA DE MENSALIDADES**

### **1.1 Estrutura de Dados**
```sql
-- Tabela para controle de mensalidades
CREATE TABLE monthly_fees (
    id BIGINT PRIMARY KEY,
    enrollment_id BIGINT NOT NULL,
    year INT NOT NULL,
    month INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    due_date DATE NOT NULL,
    status ENUM('pending', 'paid', 'overdue', 'cancelled') DEFAULT 'pending',
    paid_date DATE NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_monthly_fee (enrollment_id, year, month),
    INDEX idx_status (status),
    INDEX idx_due_date (due_date)
);

-- Tabela para configuração de mensalidades por turma
CREATE TABLE classroom_monthly_configs (
    id BIGINT PRIMARY KEY,
    classroom_id BIGINT NOT NULL,
    year INT NOT NULL,
    monthly_amount DECIMAL(10,2) NOT NULL,
    due_day INT NOT NULL DEFAULT 10,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    
    UNIQUE KEY unique_classroom_year (classroom_id, year)
);
```

### **1.2 Funcionalidades do Sistema de Mensalidades**

#### **A. Geração Automática de Mensalidades**
- **Geração em lote** para todos os alunos ativos
- **Configuração por turma** (valor, dia de vencimento)
- **Geração mensal** automática via cron job
- **Notificações** de vencimento

#### **B. Gestão de Mensalidades**
- **Lista de mensalidades** com filtros (status, período, turma)
- **Pagamento individual** ou em lote
- **Estorno de mensalidades**
- **Relatórios** de inadimplência

#### **C. Interface de Usuário**
- **Página dedicada** `/mensalidades`
- **Filtros avançados** (turma, status, período)
- **Ações em lote** (marcar como pago, gerar cobrança)
- **Dashboard** com resumo financeiro

---

## 🏫 **2. SISTEMA DE TURMAS ROBUSTO**

### **2.1 Estrutura de Dados Aprimorada**
```sql
-- Adicionar campos à tabela classrooms
ALTER TABLE classrooms ADD COLUMN (
    school_year VARCHAR(10) NOT NULL, -- Ex: "2024/2025"
    grade_level VARCHAR(20) NOT NULL, -- Ex: "1º Ano", "2º Ano"
    teacher_name VARCHAR(255) NULL,
    teacher_phone VARCHAR(20) NULL,
    teacher_email VARCHAR(255) NULL,
    max_students INT NOT NULL DEFAULT 30,
    current_students INT NOT NULL DEFAULT 0,
    is_active BOOLEAN DEFAULT TRUE,
    start_date DATE NULL,
    end_date DATE NULL,
    description TEXT NULL
);

-- Tabela para histórico de mudanças de turma
CREATE TABLE enrollment_classroom_changes (
    id BIGINT PRIMARY KEY,
    enrollment_id BIGINT NOT NULL,
    old_classroom_id BIGINT NULL,
    new_classroom_id BIGINT NOT NULL,
    change_date DATE NOT NULL,
    reason VARCHAR(255) NULL,
    changed_by BIGINT NULL, -- user_id
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### **2.2 Funcionalidades do Sistema de Turmas**

#### **A. Gestão de Turmas**
- **CRUD completo** de turmas
- **Vinculação de professores** às turmas
- **Controle de vagas** em tempo real
- **Histórico de mudanças** de turma

#### **B. Matrícula em Turmas**
- **Seleção de turma** durante matrícula
- **Validação de vagas** disponíveis
- **Transferência** entre turmas
- **Lista de espera** para turmas lotadas

#### **C. Relatórios de Turmas**
- **Lista de alunos** por turma
- **Ocupação** de vagas
- **Relatório de transferências**

---

## 🚀 **3. ORDEM DE IMPLEMENTAÇÃO**

### **FASE 1: Sistema de Turmas Robusto** ⭐ **PRIORIDADE ALTA**
**Tempo estimado: 2-3 dias**

#### **1.1 Backend (1 dia)**
- [ ] Criar migration para campos adicionais de turmas
- [ ] Atualizar modelo Classroom
- [ ] Criar migration para histórico de mudanças
- [ ] Implementar service para gestão de turmas
- [ ] Criar rotas API para turmas

#### **1.2 Frontend (1-2 dias)**
- [ ] Página de gestão de turmas (`/turmas`)
- [ ] Formulário de criação/edição de turmas
- [ ] Lista de turmas com filtros
- [ ] Modal de transferência de turma
- [ ] Integração com sistema de matrículas

#### **1.3 Testes e Validação (0.5 dia)**
- [ ] Testar CRUD de turmas
- [ ] Testar transferência de turmas
- [ ] Validar controle de vagas

---

### **FASE 2: Sistema de Mensalidades** ⭐ **PRIORIDADE ALTA**
**Tempo estimado: 3-4 dias**

#### **2.1 Backend (1.5 dias)**
- [ ] Criar migrations para mensalidades
- [ ] Implementar modelos MonthlyFee e ClassroomMonthlyConfig
- [ ] Criar service para gestão de mensalidades
- [ ] Implementar geração automática de mensalidades
- [ ] Criar rotas API para mensalidades

#### **2.2 Frontend (1.5 dias)**
- [ ] Página de mensalidades (`/mensalidades`)
- [ ] Lista de mensalidades com filtros avançados
- [ ] Modal de pagamento de mensalidades
- [ ] Dashboard financeiro
- [ ] Relatórios de inadimplência

#### **2.3 Automação (1 dia)**
- [ ] Implementar cron job para geração mensal
- [ ] Sistema de notificações de vencimento
- [ ] Relatórios automáticos

---

### **FASE 3: Integração e Relatórios** ⭐ **PRIORIDADE MÉDIA**
**Tempo estimado: 2 dias**

#### **3.1 Relatórios Financeiros**
- [ ] Relatório de mensalidades por período
- [ ] Relatório de inadimplência por turma
- [ ] Dashboard executivo
- [ ] Exportação para Excel/PDF

#### **3.2 Melhorias de UX**
- [ ] Notificações em tempo real
- [ ] Filtros avançados
- [ ] Ações em lote
- [ ] Mobile responsiveness

---

## 🎯 **RECOMENDAÇÃO: IMPLEMENTAR PRIMEIRO**

### **🏫 SISTEMA DE TURMAS ROBUSTO**

**Por que começar com turmas?**

1. **Base sólida**: Turmas são a base para mensalidades
2. **Impacto imediato**: Melhora a gestão atual de matrículas
3. **Menor complexidade**: Mais simples que mensalidades
4. **Dependência**: Mensalidades dependem de turmas bem estruturadas

### **📋 Plano de Implementação Imediata:**

#### **Semana 1: Sistema de Turmas**
- **Dia 1-2**: Backend (migrations, models, services)
- **Dia 3-4**: Frontend (páginas, formulários, listas)
- **Dia 5**: Testes e integração

#### **Semana 2: Sistema de Mensalidades**
- **Dia 1-2**: Backend (migrations, models, services)
- **Dia 3-4**: Frontend (páginas, relatórios, dashboard)
- **Dia 5**: Automação e testes

---

## 🔧 **TECNOLOGIAS E FERRAMENTAS**

### **Backend:**
- **Laravel** (já implementado)
- **Eloquent ORM** para relacionamentos
- **Laravel Scheduler** para cron jobs
- **Laravel Notifications** para alertas

### **Frontend:**
- **Vue.js 3** (já implementado)
- **Inertia.js** (já implementado)
- **Tailwind CSS** (já implementado)
- **Chart.js** para gráficos financeiros

### **Banco de Dados:**
- **MySQL** (já implementado)
- **Índices otimizados** para consultas
- **Transações** para consistência

---

## 📊 **MÉTRICAS DE SUCESSO**

### **Sistema de Turmas:**
- [ ] 100% das turmas com dados completos
- [ ] 0% de inconsistências de vagas
- [ ] Tempo de transferência < 30 segundos

### **Sistema de Mensalidades:**
- [ ] Geração automática 100% funcional
- [ ] Redução de 50% no tempo de gestão
- [ ] Relatórios em tempo real

---

## 🎉 **RESULTADO ESPERADO**

Ao final da implementação, teremos:

1. **Sistema de Turmas Robusto** com gestão completa
2. **Sistema de Mensalidades Automatizado** 
3. **Relatórios Financeiros** em tempo real
4. **Interface Intuitiva** para gestão
5. **Automação Completa** de processos

**Tempo total estimado: 2 semanas**
**Impacto: Alto - Melhora significativa na gestão escolar**
