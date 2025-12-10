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
