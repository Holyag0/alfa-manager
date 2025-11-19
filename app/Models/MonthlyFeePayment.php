<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class MonthlyFeePayment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'monthly_fee_installment_id',
        'paid_by_guardian_id',
        'payment_number',
        'amount',
        'original_installment_amount',
        'sibling_discount',
        'punctuality_discount',
        'other_discounts',
        'interest_applied',
        'fine_applied',
        'method',
        'payment_date',
        'confirmation_date',
        'status',
        'reference',
        'transaction_id',
        'enrollment_invoice_id',
        'enrollment_payment_id',
        'notes',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'original_installment_amount' => 'decimal:2',
        'sibling_discount' => 'decimal:2',
        'punctuality_discount' => 'decimal:2',
        'other_discounts' => 'decimal:2',
        'interest_applied' => 'decimal:2',
        'fine_applied' => 'decimal:2',
        'payment_date' => 'datetime',
        'confirmation_date' => 'datetime',
    ];

    // Relationships
    public function installment(): BelongsTo
    {
        return $this->belongsTo(MonthlyFeeInstallment::class, 'monthly_fee_installment_id');
    }

    public function guardian(): BelongsTo
    {
        return $this->belongsTo(Guardian::class, 'paid_by_guardian_id');
    }

    public function enrollmentInvoice(): BelongsTo
    {
        return $this->belongsTo(EnrollmentInvoice::class);
    }

    public function enrollmentPayment(): BelongsTo
    {
        return $this->belongsTo(EnrollmentPayment::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeRefunded($query)
    {
        return $query->where('status', 'refunded');
    }

    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    public function scopeByGuardian($query, $guardianId)
    {
        return $query->where('paid_by_guardian_id', $guardianId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('payment_date', [$startDate, $endDate]);
    }

    // Accessors
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getFormattedOriginalAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->original_installment_amount, 2, ',', '.');
    }

    public function getTotalDiscountAttribute(): float
    {
        return (float) ($this->sibling_discount + $this->punctuality_discount + $this->other_discounts);
    }

    public function getFormattedTotalDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->total_discount, 2, ',', '.');
    }

    public function getFormattedSiblingDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->sibling_discount, 2, ',', '.');
    }

    public function getFormattedPunctualityDiscountAttribute(): string
    {
        return 'R$ ' . number_format($this->punctuality_discount, 2, ',', '.');
    }

    public function getFormattedOtherDiscountsAttribute(): string
    {
        return 'R$ ' . number_format($this->other_discounts, 2, ',', '.');
    }

    public function getFormattedInterestAttribute(): string
    {
        return 'R$ ' . number_format($this->interest_applied, 2, ',', '.');
    }

    public function getFormattedFineAttribute(): string
    {
        return 'R$ ' . number_format($this->fine_applied, 2, ',', '.');
    }

    public function getMethodLabelAttribute(): string
    {
        return match ($this->method) {
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'cash' => 'Dinheiro',
            'bank_transfer' => 'Transferência Bancária',
            'check' => 'Cheque',
            default => 'Outro',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Estornado',
            default => 'Desconhecido',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'yellow',
            'confirmed' => 'green',
            'cancelled' => 'gray',
            'refunded' => 'red',
            default => 'gray',
        };
    }

    // Helper methods
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }

    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    public function isRefunded(): bool
    {
        return $this->status === 'refunded';
    }
}

