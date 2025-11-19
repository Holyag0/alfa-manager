<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;

class MonthlyFeeInstallment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'monthly_fee_id',
        'classroom_service_id',
        'reference_month',
        'installment_number',
        'due_date',
        'status',
    ];

    protected $casts = [
        'due_date' => 'date',
        'installment_number' => 'integer',
    ];

    // Relationships
    public function monthlyFee(): BelongsTo
    {
        return $this->belongsTo(MonthlyFee::class);
    }

    public function classroomService(): BelongsTo
    {
        return $this->belongsTo(ClassroomService::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(MonthlyFeePayment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeWaived($query)
    {
        return $query->where('status', 'waived');
    }

    public function scopeByMonth($query, $month)
    {
        return $query->where('reference_month', $month);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('reference_month', 'like', $year . '%');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', Carbon::today());
    }

    public function scopeDueInDays($query, $days)
    {
        return $query->where('due_date', '<=', Carbon::today()->addDays($days))
            ->where('due_date', '>=', Carbon::today());
    }

    // Accessors - Valores calculados dinamicamente do serviço atual
    /**
     * Obter valor base do serviço atual
     */
    public function getBaseAmountAttribute(): float
    {
        // Buscar valor atual do serviço
        if ($this->classroomService && $this->classroomService->service) {
            return (float) $this->classroomService->service->price;
        }
        
        // Fallback: buscar do monthlyFee (valor histórico)
        if ($this->monthlyFee) {
            return (float) $this->monthlyFee->base_amount;
        }
        
        return 0;
    }

    /**
     * Obter desconto de irmão do contrato
     */
    public function getSiblingDiscountAttribute(): float
    {
        if ($this->monthlyFee && $this->monthlyFee->has_sibling_discount) {
            return (float) $this->monthlyFee->sibling_discount_amount;
        }
        return 0;
    }

    /**
     * Calcular valor final (base - descontos)
     * Para parcelas não pagas, sempre usa o valor atual do serviço
     */
    public function getFinalAmountAttribute(): float
    {
        // Se está paga, buscar do pagamento
        if ($this->status === 'paid' && $this->payments()->exists()) {
            $payment = $this->payments()->where('status', 'confirmed')->first();
            if ($payment) {
                return (float) $payment->amount;
            }
        }
        
        // Para parcelas não pagas, calcular do serviço atual
        $baseAmount = $this->base_amount;
        $siblingDiscount = $this->sibling_discount;
        
        return max(0, $baseAmount - $siblingDiscount);
    }

    public function getFormattedBaseAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->base_amount, 2, ',', '.');
    }

    public function getFormattedFinalAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->final_amount, 2, ',', '.');
    }

    public function getFormattedSiblingDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->sibling_discount, 2, ',', '.');
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado',
            'waived' => 'Dispensado',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'paid' => 'green',
            'overdue' => 'red',
            'cancelled' => 'gray',
            'waived' => 'blue',
            default => 'gray',
        };
    }

    public function getDaysLateAttribute(): int
    {
        if ($this->status !== 'overdue' || !$this->due_date) {
            return 0;
        }

        return Carbon::now()->diffInDays($this->due_date, false);
    }

    public function getReferenceMonthFormattedAttribute(): string
    {
        if (!$this->reference_month) {
            return '';
        }

        $date = Carbon::createFromFormat('Y-m', $this->reference_month);
        return $date->translatedFormat('F/Y');
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isOverdue(): bool
    {
        return $this->status === 'overdue';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isWaived(): bool
    {
        return $this->status === 'waived';
    }

    /**
     * Verifica se a parcela pode receber pagamento
     */
    public function canBePaid(): bool
    {
        return !in_array($this->status, ['paid', 'cancelled', 'waived']);
    }
}

