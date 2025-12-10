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

    protected $appends = ['month_name', 'employees_count', 'paid_count', 'total_gratification', 'total_bonus', 'total_abono', 'total_inss', 'total_advance', 'total_other_deductions'];

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
    public function scopeByYear($query, int|string $year)
    {
        return $query->where('year', (int) $year);
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
     * Conta todos os colaboradores ativos, não apenas os que têm entry
     */
    public function getEmployeesCountAttribute(): int
    {
        return \App\Models\Employee::active()->count();
    }

    /**
     * Accessor: Quantidade de pagos
     */
    public function getPaidCountAttribute(): int
    {
        return $this->entries()->whereNotNull('paid_at')->count();
    }

    /**
     * Accessor: Total de gratificações
     */
    public function getTotalGratificationAttribute(): float
    {
        return $this->entries()->sum('gratification');
    }

    /**
     * Accessor: Total de bônus
     */
    public function getTotalBonusAttribute(): float
    {
        return $this->entries()->sum('bonus');
    }

    /**
     * Accessor: Total de abono
     * Nota: Abono é o mesmo campo que bonus no banco de dados
     */
    public function getTotalAbonoAttribute(): float
    {
        return $this->entries()->sum('bonus');
    }

    /**
     * Accessor: Total de INSS
     */
    public function getTotalInssAttribute(): float
    {
        return $this->entries()->sum('inss_deduction');
    }

    /**
     * Accessor: Total de vale (advance_deduction)
     */
    public function getTotalAdvanceAttribute(): float
    {
        return $this->entries()->sum('advance_deduction');
    }

    /**
     * Accessor: Total de outros descontos
     */
    public function getTotalOtherDeductionsAttribute(): float
    {
        return $this->entries()->sum('other_deductions');
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

    /**
     * Verificar se a folha pode ser editada
     * Folhas com status 'completed' não podem ser editadas
     */
    public function canBeEdited(): bool
    {
        return $this->status !== 'completed';
    }

    /**
     * Verificar se a folha pode ser atualizada
     * Folhas com status 'completed' não podem ser atualizadas
     */
    public function canBeUpdated(): bool
    {
        return $this->status !== 'completed';
    }

    /**
     * Verificar se podem ser feitas alterações em entries
     * Folhas com status 'completed' não permitem alterações
     */
    public function canModifyEntries(): bool
    {
        return $this->status !== 'completed';
    }
}
