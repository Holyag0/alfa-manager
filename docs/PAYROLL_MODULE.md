# 📋 Módulo de Folha de Pagamento

> Sistema de gestão de folha de pagamento para colaboradores

## 📑 Índice

1. [Visão Geral](#visão-geral)
2. [Estrutura de Dados](#estrutura-de-dados)
3. [Backend](#backend)
   - [Migrations](#migrations)
   - [Models](#models)
   - [Service](#service)
   - [Controller](#controller)
   - [Request Validation](#request-validation)
   - [Rotas](#rotas)
4. [Frontend](#frontend)
   - [Estrutura de Arquivos](#estrutura-de-arquivos)
   - [Componentes](#componentes)
5. [Fluxo de Uso](#fluxo-de-uso)
6. [Checklist de Implementação](#checklist-de-implementação)
7. [Estimativa de Tempo](#estimativa-de-tempo)

---

## Visão Geral

O módulo de Folha de Pagamento permite:

- **Gerar folhas automaticamente**: Ao acessar o índice, se não houver folhas, o usuário pode gerar 12 folhas correspondentes aos 12 meses do ano
- **Criar folhas adicionais**: O usuário pode criar folhas extras se necessário
- **Listar colaboradores por folha**: Ao clicar em uma folha, são exibidos todos os colaboradores
- **Registrar pagamentos**: Modal com informações do colaborador e formulário para registrar o pagamento

### Entidades Principais

| Entidade | Descrição |
|----------|-----------|
| **Payroll** | Representa uma folha de pagamento mensal |
| **PayrollEntry** | Representa o registro de pagamento de um colaborador em uma folha |

---

## Estrutura de Dados

### Mapeamento da Planilha Excel

| Campo Planilha | Campo Sistema | Tipo | Descrição |
|----------------|---------------|------|-----------|
| Qde | - | - | Número sequencial (automático) |
| NOMES DOS FUNCIONARIOS | `employee_id` | FK | Referência ao colaborador |
| SAL. CART | `base_salary` | decimal(10,2) | Salário carteira |
| Gratif | `gratification` | decimal(10,2) | Gratificação |
| Abonus | `bonus` | decimal(10,2) | Abonos |
| SAL 2025 | `gross_salary` | decimal(10,2) | Salário bruto (calculado) |
| INSS | `inss_deduction` | decimal(10,2) | Desconto INSS |
| VALE | `advance_deduction` | decimal(10,2) | Vale/Adiantamento |
| Sal. Líquido | `net_salary` | decimal(10,2) | Salário líquido (calculado) |
| Assinatura | `payment_method` + `payment_info` | string | Método e dados de pagamento |

---

## Backend

### Migrations

#### 1. Tabela `payrolls`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payrolls', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month');
            $table->string('reference')->nullable(); // Ex: "JUNHO - 2025"
            $table->enum('status', ['draft', 'processing', 'completed'])->default('draft');
            $table->decimal('total_gross', 12, 2)->default(0);
            $table->decimal('total_net', 12, 2)->default(0);
            $table->timestamp('closed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            $table->unique(['year', 'month']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payrolls');
    }
};
```

#### 2. Tabela `payroll_entries`

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Proventos
            $table->decimal('base_salary', 10, 2)->default(0);      // SAL. CART
            $table->decimal('gratification', 10, 2)->default(0);    // Gratif
            $table->decimal('bonus', 10, 2)->default(0);            // Abonus
            $table->decimal('gross_salary', 10, 2)->default(0);     // SAL 2025 (calculado)
            
            // Descontos
            $table->decimal('inss_deduction', 10, 2)->default(0);   // INSS
            $table->decimal('advance_deduction', 10, 2)->default(0); // VALE
            $table->decimal('other_deductions', 10, 2)->default(0); // Outros descontos
            
            // Líquido
            $table->decimal('net_salary', 10, 2)->default(0);       // Sal. Líquido (calculado)
            
            // Pagamento
            $table->enum('payment_method', ['pix', 'poupanca', 'conta_corrente', 'dinheiro'])->nullable();
            $table->string('payment_info')->nullable();              // CPF PIX, dados conta, etc.
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->unique(['payroll_id', 'employee_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
```

---

### Models

#### 1. Model `Payroll`

**Arquivo:** `app/Models/Payroll.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Payroll extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'year',
        'month',
        'reference',
        'status',
        'total_gross',
        'total_net',
        'closed_at',
    ];

    protected $casts = [
        'year' => 'integer',
        'month' => 'integer',
        'total_gross' => 'decimal:2',
        'total_net' => 'decimal:2',
        'closed_at' => 'datetime',
    ];

    protected $appends = ['month_name', 'employees_count', 'paid_count'];

    /**
     * Nomes dos meses em português
     */
    public const MONTH_NAMES = [
        1 => 'Janeiro',
        2 => 'Fevereiro',
        3 => 'Março',
        4 => 'Abril',
        5 => 'Maio',
        6 => 'Junho',
        7 => 'Julho',
        8 => 'Agosto',
        9 => 'Setembro',
        10 => 'Outubro',
        11 => 'Novembro',
        12 => 'Dezembro',
    ];

    /**
     * Relacionamento com lançamentos
     */
    public function entries(): HasMany
    {
        return $this->hasMany(PayrollEntry::class);
    }

    /**
     * Scope: Filtrar por ano
     */
    public function scopeByYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    /**
     * Scope: Filtrar por status
     */
    public function scopeByStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Accessor: Nome do mês
     */
    public function getMonthNameAttribute(): string
    {
        return self::MONTH_NAMES[$this->month] ?? '';
    }

    /**
     * Accessor: Quantidade de colaboradores
     */
    public function getEmployeesCountAttribute(): int
    {
        return $this->entries()->count();
    }

    /**
     * Accessor: Quantidade de pagos
     */
    public function getPaidCountAttribute(): int
    {
        return $this->entries()->whereNotNull('paid_at')->count();
    }

    /**
     * Recalcular totais da folha
     */
    public function recalculateTotals(): void
    {
        $this->total_gross = $this->entries()->sum('gross_salary');
        $this->total_net = $this->entries()->sum('net_salary');
        $this->save();
    }

    /**
     * Verificar se a folha está fechada
     */
    public function isClosed(): bool
    {
        return $this->status === 'completed' && $this->closed_at !== null;
    }
}
```

#### 2. Model `PayrollEntry`

**Arquivo:** `app/Models/PayrollEntry.php`

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayrollEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_id',
        'employee_id',
        'base_salary',
        'gratification',
        'bonus',
        'gross_salary',
        'inss_deduction',
        'advance_deduction',
        'other_deductions',
        'net_salary',
        'payment_method',
        'payment_info',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'base_salary' => 'decimal:2',
        'gratification' => 'decimal:2',
        'bonus' => 'decimal:2',
        'gross_salary' => 'decimal:2',
        'inss_deduction' => 'decimal:2',
        'advance_deduction' => 'decimal:2',
        'other_deductions' => 'decimal:2',
        'net_salary' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['is_paid', 'total_deductions', 'payment_method_label'];

    /**
     * Labels dos métodos de pagamento
     */
    public const PAYMENT_METHOD_LABELS = [
        'pix' => 'PIX',
        'poupanca' => 'Poupança',
        'conta_corrente' => 'Conta Corrente',
        'dinheiro' => 'Dinheiro',
    ];

    /**
     * Relacionamento com folha
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class);
    }

    /**
     * Relacionamento com colaborador
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * Accessor: Verificar se está pago
     */
    public function getIsPaidAttribute(): bool
    {
        return $this->paid_at !== null;
    }

    /**
     * Accessor: Total de descontos
     */
    public function getTotalDeductionsAttribute(): float
    {
        return $this->inss_deduction + $this->advance_deduction + $this->other_deductions;
    }

    /**
     * Accessor: Label do método de pagamento
     */
    public function getPaymentMethodLabelAttribute(): ?string
    {
        return $this->payment_method 
            ? self::PAYMENT_METHOD_LABELS[$this->payment_method] ?? $this->payment_method
            : null;
    }

    /**
     * Calcular totais automaticamente
     */
    public function calculateTotals(): void
    {
        // Calcular salário bruto
        $this->gross_salary = $this->base_salary + $this->gratification + $this->bonus;
        
        // Calcular salário líquido
        $totalDeductions = $this->inss_deduction + $this->advance_deduction + $this->other_deductions;
        $this->net_salary = $this->gross_salary - $totalDeductions;
    }

    /**
     * Boot method para calcular totais automaticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($entry) {
            $entry->calculateTotals();
        });

        static::saved(function ($entry) {
            // Recalcular totais da folha
            $entry->payroll->recalculateTotals();
        });

        static::deleted(function ($entry) {
            // Recalcular totais da folha
            $entry->payroll->recalculateTotals();
        });
    }
}
```

---

### Service

**Arquivo:** `app/Services/PayrollService.php`

```php
<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollEntry;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected Payroll $payroll;
    protected PayrollEntry $payrollEntry;

    public function __construct(Payroll $payroll, PayrollEntry $payrollEntry)
    {
        $this->payroll = $payroll;
        $this->payrollEntry = $payrollEntry;
    }

    /**
     * Gerar 12 folhas do ano
     */
    public function generateYearPayrolls(int $year): Collection
    {
        $payrolls = collect();

        DB::transaction(function () use ($year, &$payrolls) {
            for ($month = 1; $month <= 12; $month++) {
                // Verificar se já existe
                $existing = $this->payroll->where('year', $year)->where('month', $month)->first();
                
                if (!$existing) {
                    $monthName = Payroll::MONTH_NAMES[$month];
                    $payroll = $this->payroll->create([
                        'year' => $year,
                        'month' => $month,
                        'reference' => strtoupper($monthName) . ' - ' . $year,
                        'status' => 'draft',
                    ]);
                    $payrolls->push($payroll);
                } else {
                    $payrolls->push($existing);
                }
            }
        });

        return $payrolls;
    }

    /**
     * Criar uma folha adicional
     */
    public function create(array $data): Payroll
    {
        $monthName = Payroll::MONTH_NAMES[$data['month']];
        $data['reference'] = $data['reference'] ?? (strtoupper($monthName) . ' - ' . $data['year']);
        $data['status'] = $data['status'] ?? 'draft';

        return $this->payroll->create($data);
    }

    /**
     * Listar folhas com filtros
     */
    public function list(array $filters = []): Collection
    {
        $query = $this->payroll->newQuery();

        if (!empty($filters['year'])) {
            $query->byYear($filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        return $query->orderBy('year', 'desc')->orderBy('month', 'asc')->get();
    }

    /**
     * Listar anos disponíveis
     */
    public function getAvailableYears(): array
    {
        $years = $this->payroll->distinct()->pluck('year')->toArray();
        
        // Adicionar ano atual se não existir
        $currentYear = date('Y');
        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
        }
        
        rsort($years);
        return $years;
    }

    /**
     * Verificar se existem folhas para um ano
     */
    public function hasPayrollsForYear(int $year): bool
    {
        return $this->payroll->byYear($year)->exists();
    }

    /**
     * Buscar folha por ID
     */
    public function find(int $id): ?Payroll
    {
        return $this->payroll->find($id);
    }

    /**
     * Buscar folha com lançamentos e colaboradores
     */
    public function findWithEntries(int $id): ?Payroll
    {
        return $this->payroll
            ->with(['entries.employee.position'])
            ->find($id);
    }

    /**
     * Buscar todos os colaboradores ativos para uma folha
     * Retorna colaboradores com ou sem lançamento
     */
    public function getEmployeesForPayroll(int $payrollId): Collection
    {
        $payroll = $this->find($payrollId);
        if (!$payroll) {
            return collect();
        }

        // Buscar todos colaboradores ativos
        $employees = Employee::active()
            ->with('position')
            ->orderBy('name')
            ->get();

        // Buscar lançamentos existentes
        $entries = $this->payrollEntry
            ->where('payroll_id', $payrollId)
            ->get()
            ->keyBy('employee_id');

        // Mesclar dados
        return $employees->map(function ($employee) use ($entries) {
            $entry = $entries->get($employee->id);
            
            return [
                'employee' => $employee,
                'entry' => $entry,
                'has_entry' => $entry !== null,
                'is_paid' => $entry && $entry->paid_at !== null,
            ];
        });
    }

    /**
     * Buscar ou criar lançamento para um colaborador
     */
    public function getOrCreateEntry(int $payrollId, int $employeeId): PayrollEntry
    {
        $entry = $this->payrollEntry
            ->where('payroll_id', $payrollId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$entry) {
            $employee = Employee::find($employeeId);
            
            $entry = $this->payrollEntry->create([
                'payroll_id' => $payrollId,
                'employee_id' => $employeeId,
                'base_salary' => $employee->salary ?? 0,
            ]);
        }

        return $entry->load('employee.position');
    }

    /**
     * Registrar pagamento de colaborador
     */
    public function registerPayment(int $payrollId, int $employeeId, array $data): PayrollEntry
    {
        $entry = $this->getOrCreateEntry($payrollId, $employeeId);

        $entry->update([
            'base_salary' => $data['base_salary'] ?? $entry->base_salary,
            'gratification' => $data['gratification'] ?? 0,
            'bonus' => $data['bonus'] ?? 0,
            'inss_deduction' => $data['inss_deduction'] ?? 0,
            'advance_deduction' => $data['advance_deduction'] ?? 0,
            'other_deductions' => $data['other_deductions'] ?? 0,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_info' => $data['payment_info'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['mark_as_paid'] ?? false ? now() : $entry->paid_at,
        ]);

        return $entry->fresh(['employee.position']);
    }

    /**
     * Atualizar lançamento existente
     */
    public function updateEntry(int $entryId, array $data): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        
        $entry->update($data);

        return $entry->fresh(['employee.position']);
    }

    /**
     * Marcar como pago
     */
    public function markAsPaid(int $entryId): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        $entry->paid_at = now();
        $entry->save();

        return $entry->fresh(['employee.position']);
    }

    /**
     * Desmarcar como pago
     */
    public function markAsUnpaid(int $entryId): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        $entry->paid_at = null;
        $entry->save();

        return $entry->fresh(['employee.position']);
    }

    /**
     * Fechar folha de pagamento
     */
    public function close(int $id): Payroll
    {
        $payroll = $this->payroll->findOrFail($id);
        
        $payroll->update([
            'status' => 'completed',
            'closed_at' => now(),
        ]);

        return $payroll->fresh();
    }

    /**
     * Reabrir folha de pagamento
     */
    public function reopen(int $id): Payroll
    {
        $payroll = $this->payroll->findOrFail($id);
        
        $payroll->update([
            'status' => 'draft',
            'closed_at' => null,
        ]);

        return $payroll->fresh();
    }

    /**
     * Excluir folha
     */
    public function delete(int $id): bool
    {
        $payroll = $this->payroll->findOrFail($id);
        return $payroll->delete();
    }
}
```

---

### Controller

**Arquivo:** `app/Http/Controllers/Payroll/PayrollController.php`

```php
<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use App\Http\Requests\PayrollEntryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    private PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Lista de folhas de pagamento
     */
    public function index(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $payrolls = $this->payrollService->list(['year' => $year]);
            $availableYears = $this->payrollService->getAvailableYears();
            $hasPayrolls = $this->payrollService->hasPayrollsForYear($year);

            return Inertia::render('Payroll/Index', [
                'payrolls' => $payrolls,
                'selectedYear' => (int) $year,
                'availableYears' => $availableYears,
                'hasPayrolls' => $hasPayrolls,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar folhas de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar folhas de pagamento.');
        }
    }

    /**
     * Gerar 12 folhas do ano
     */
    public function generate(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $this->payrollService->generateYearPayrolls($year);

            return redirect()->route('payroll.index', ['year' => $year])
                ->with('success', "Folhas de pagamento de {$year} geradas com sucesso!");
        } catch (\Exception $e) {
            Log::error('Erro ao gerar folhas de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar folhas de pagamento.');
        }
    }

    /**
     * Criar folha adicional
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
                'reference' => 'nullable|string|max:100',
            ]);

            $this->payrollService->create($validated);

            return redirect()->route('payroll.index', ['year' => $validated['year']])
                ->with('success', 'Folha de pagamento criada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar folha de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar folha de pagamento.');
        }
    }

    /**
     * Exibir folha com colaboradores
     */
    public function show($id)
    {
        try {
            $payroll = $this->payrollService->findWithEntries($id);

            if (!$payroll) {
                return redirect()->route('payroll.index')
                    ->with('error', 'Folha de pagamento não encontrada.');
            }

            $employeesData = $this->payrollService->getEmployeesForPayroll($id);

            return Inertia::render('Payroll/Show', [
                'payroll' => $payroll,
                'employeesData' => $employeesData,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir folha de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar folha de pagamento.');
        }
    }

    /**
     * Buscar dados do lançamento de um colaborador (API)
     */
    public function getEntry($payrollId, $employeeId)
    {
        try {
            $entry = $this->payrollService->getOrCreateEntry($payrollId, $employeeId);

            return response()->json([
                'entry' => $entry,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar lançamento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar lançamento.'], 500);
        }
    }

    /**
     * Registrar/Atualizar pagamento de colaborador
     */
    public function storeEntry($payrollId, $employeeId, PayrollEntryRequest $request)
    {
        try {
            $validated = $request->validated();
            $entry = $this->payrollService->registerPayment($payrollId, $employeeId, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pagamento registrado com sucesso!',
                    'entry' => $entry,
                ]);
            }

            return redirect()->back()->with('success', 'Pagamento registrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao registrar pagamento: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Erro ao registrar pagamento.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao registrar pagamento.');
        }
    }

    /**
     * Atualizar lançamento existente
     */
    public function updateEntry($payrollId, $entryId, PayrollEntryRequest $request)
    {
        try {
            $validated = $request->validated();
            $entry = $this->payrollService->updateEntry($entryId, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Lançamento atualizado com sucesso!',
                    'entry' => $entry,
                ]);
            }

            return redirect()->back()->with('success', 'Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar lançamento: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Erro ao atualizar lançamento.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao atualizar lançamento.');
        }
    }

    /**
     * Marcar lançamento como pago
     */
    public function markPaid($entryId)
    {
        try {
            $entry = $this->payrollService->markAsPaid($entryId);

            return response()->json([
                'message' => 'Marcado como pago!',
                'entry' => $entry,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar como pago: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao marcar como pago.'], 500);
        }
    }

    /**
     * Fechar folha de pagamento
     */
    public function close($id)
    {
        try {
            $this->payrollService->close($id);

            return redirect()->back()->with('success', 'Folha de pagamento fechada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao fechar folha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao fechar folha de pagamento.');
        }
    }

    /**
     * Reabrir folha de pagamento
     */
    public function reopen($id)
    {
        try {
            $this->payrollService->reopen($id);

            return redirect()->back()->with('success', 'Folha de pagamento reaberta com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao reabrir folha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao reabrir folha de pagamento.');
        }
    }

    /**
     * Excluir folha
     */
    public function destroy($id)
    {
        try {
            $this->payrollService->delete($id);

            return redirect()->route('payroll.index')
                ->with('success', 'Folha de pagamento excluída com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir folha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir folha de pagamento.');
        }
    }
}
```

---

### Request Validation

**Arquivo:** `app/Http/Requests/PayrollEntryRequest.php`

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayrollEntryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'base_salary' => 'required|numeric|min:0',
            'gratification' => 'nullable|numeric|min:0',
            'bonus' => 'nullable|numeric|min:0',
            'inss_deduction' => 'nullable|numeric|min:0',
            'advance_deduction' => 'nullable|numeric|min:0',
            'other_deductions' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|in:pix,poupanca,conta_corrente,dinheiro',
            'payment_info' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
            'mark_as_paid' => 'nullable|boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'base_salary.required' => 'O salário base é obrigatório.',
            'base_salary.numeric' => 'O salário base deve ser um número.',
            'base_salary.min' => 'O salário base não pode ser negativo.',
            'payment_method.in' => 'Método de pagamento inválido.',
        ];
    }
}
```

---

### Rotas

**Adicionar em:** `routes/web.php`

```php
// Rotas de Folha de Pagamento
Route::prefix('payroll')->name('payroll.')->group(function () {
    Route::get('/', [App\Http\Controllers\Payroll\PayrollController::class, 'index'])->name('index');
    Route::post('/generate', [App\Http\Controllers\Payroll\PayrollController::class, 'generate'])->name('generate');
    Route::post('/create', [App\Http\Controllers\Payroll\PayrollController::class, 'store'])->name('store');
    Route::get('/{id}', [App\Http\Controllers\Payroll\PayrollController::class, 'show'])->name('show');
    Route::delete('/{id}', [App\Http\Controllers\Payroll\PayrollController::class, 'destroy'])->name('destroy');
    Route::post('/{id}/close', [App\Http\Controllers\Payroll\PayrollController::class, 'close'])->name('close');
    Route::post('/{id}/reopen', [App\Http\Controllers\Payroll\PayrollController::class, 'reopen'])->name('reopen');
    
    // Rotas de lançamentos
    Route::get('/{payrollId}/entries/{employeeId}', [App\Http\Controllers\Payroll\PayrollController::class, 'getEntry'])->name('entry.show');
    Route::post('/{payrollId}/entries/{employeeId}', [App\Http\Controllers\Payroll\PayrollController::class, 'storeEntry'])->name('entry.store');
    Route::put('/{payrollId}/entries/{entryId}', [App\Http\Controllers\Payroll\PayrollController::class, 'updateEntry'])->name('entry.update');
    Route::post('/entries/{entryId}/mark-paid', [App\Http\Controllers\Payroll\PayrollController::class, 'markPaid'])->name('entry.mark-paid');
});
```

---

## Frontend

### Estrutura de Arquivos

```
resources/js/Pages/Payroll/
├── Index.vue                    # Lista de folhas (com botão gerar)
├── Show.vue                     # Detalhes da folha com colaboradores
└── components/
    ├── PayrollCard.vue          # Card de cada mês
    ├── PayrollFilters.vue       # Filtros por ano
    ├── EmployeePayrollTable.vue # Tabela de colaboradores na folha
    ├── PaymentEntryModal.vue    # Modal de registro de pagamento
    └── PayrollSummary.vue       # Resumo totais da folha
```

### Componentes

#### Index.vue

**Funcionalidades:**
- Verificar se existem folhas para o ano selecionado
- Botão "Gerar Folhas do Ano" se não houver folhas
- Filtro por ano (select com anos disponíveis)
- Grid de cards mostrando os 12 meses
- Cada card mostra: mês, status, total bruto/líquido, qtd funcionários

#### Show.vue

**Funcionalidades:**
- Header com informações da folha (mês/ano, status, totais)
- Tabela com todos os colaboradores ativos
- Colunas: Nome, Cargo, SAL.CART, Gratif, Bônus, Bruto, INSS, Vale, Líquido, Status
- Linha clicável para abrir modal de pagamento
- Indicador visual de colaboradores já pagos vs pendentes

#### PaymentEntryModal.vue

**Layout do Modal:**

```
┌─────────────────────────────────────────────────┐
│ 📋 Registrar Pagamento                     [X]  │
├─────────────────────────────────────────────────┤
│ ┌─────────────────────────────────────────────┐ │
│ │  👤 DADOS DO COLABORADOR                    │ │
│ │  Nome: Maria da Silva                       │ │
│ │  Cargo: Professora                          │ │
│ │  CPF: 123.456.789-00                        │ │
│ │  Data Contratação: 15/03/2020               │ │
│ │  Salário Base (cadastro): R$ 1.518,00       │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │  💰 PROVENTOS                               │ │
│ │  SAL. CART:     [________] (auto-preenchido)│ │
│ │  Gratificação:  [________]                  │ │
│ │  Abonos:        [________]                  │ │
│ │  ─────────────────────────                  │ │
│ │  TOTAL BRUTO:   R$ 2.465,00                 │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │  📉 DESCONTOS                               │ │
│ │  INSS:          [________]                  │ │
│ │  Vale:          [________]                  │ │
│ │  Outros:        [________]                  │ │
│ │  ─────────────────────────                  │ │
│ │  TOTAL DESC:    R$ 113,85                   │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │  💵 SALÁRIO LÍQUIDO: R$ 2.351,15            │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │  🏦 FORMA DE PAGAMENTO                      │ │
│ │  Método: [PIX ▼]                            │ │
│ │  Dados:  [CPF 00140488332__________]        │ │
│ └─────────────────────────────────────────────┘ │
│                                                 │
│ ┌─────────────────────────────────────────────┐ │
│ │  📝 Observações                             │ │
│ │  [_____________________________________]    │ │
│ └─────────────────────────────────────────────┘ │
├─────────────────────────────────────────────────┤
│          [Cancelar]    [💾 Salvar Pagamento]    │
└─────────────────────────────────────────────────┘
```

---

## Fluxo de Uso

```
┌──────────────────────────────────────────────────────────────┐
│                    FLUXO DO USUÁRIO                          │
└──────────────────────────────────────────────────────────────┘

1. Acessa /payroll (Index)
   │
   ├─► SE não há folhas para o ano atual
   │   └─► Exibe botão "Gerar Folhas de [ANO]"
   │       └─► Cria 12 folhas (Jan-Dez) automaticamente
   │
   └─► SE há folhas
       └─► Exibe grid de cards dos 12 meses
           │
           └─► Clica em um mês (ex: "Junho")
               │
               └─► Abre Show.vue com lista de colaboradores
                   │
                   └─► Clica em um colaborador
                       │
                       └─► Abre PaymentEntryModal
                           │
                           ├─► Preenche dados do pagamento
                           │
                           └─► Salva
                               │
                               └─► Atualiza lista e totais
```

---

## Checklist de Implementação

### Backend

- [ ] Criar migration `create_payrolls_table`
- [ ] Criar migration `create_payroll_entries_table`
- [ ] Criar Model `Payroll`
- [ ] Criar Model `PayrollEntry`
- [ ] Criar `PayrollService`
- [ ] Criar `PayrollController`
- [ ] Criar `PayrollEntryRequest`
- [ ] Adicionar rotas no `web.php`
- [ ] Adicionar item no menu de navegação

### Frontend

- [ ] Criar pasta `resources/js/Pages/Payroll/`
- [ ] Criar `Index.vue` (lista de folhas)
- [ ] Criar `Show.vue` (detalhes da folha)
- [ ] Criar `components/PayrollCard.vue`
- [ ] Criar `components/PayrollFilters.vue`
- [ ] Criar `components/EmployeePayrollTable.vue`
- [ ] Criar `components/PaymentEntryModal.vue`
- [ ] Criar `components/PayrollSummary.vue`

### Extras

- [ ] Adicionar permissões (payroll.view, payroll.create, payroll.edit)
- [ ] Testes unitários para PayrollService
- [ ] Testes de feature para rotas

---

## Estimativa de Tempo

| Fase | Descrição | Tempo Estimado |
|------|-----------|----------------|
| 1 | Migrations e Models | 1-2 horas |
| 2 | Service e Controller | 2-3 horas |
| 3 | Rotas e Request | 30 min |
| 4 | Frontend Index.vue | 2-3 horas |
| 5 | Frontend Show.vue | 2-3 horas |
| 6 | Modal de Pagamento | 2-3 horas |
| 7 | Integração e Testes | 1-2 horas |
| **Total** | | **~12-16 horas** |

---

## Design Visual

O design seguirá o padrão existente no sistema:

- **Cards**: Gradientes azul/cyan (similar ao módulo de Colaboradores)
- **Tabelas**: Hover effects com transição suave
- **Modais**: HeadlessUI (Dialog/TransitionRoot)
- **Cores de Status**:
  - 🟢 Verde: Pago/Completo
  - 🟡 Amarelo: Pendente/Em processamento
  - 🔴 Vermelho: Atrasado
- **Status Badges**: Chips coloridos com ícones

---

## Referências

- `app/Models/Employee.php` - Model de colaborador existente
- `app/Services/EmployeeService.php` - Padrão de service existente
- `app/Http/Controllers/Cadastros/EmployeeController.php` - Padrão de controller
- `resources/js/Pages/Cadastros/Employees/` - Padrão de componentes Vue

