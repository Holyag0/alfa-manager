<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class FinancialTransaction extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'transaction_number',
        'type',
        'category_id',
        'source_type',
        'source_id',
        'description',
        'amount',
        'transaction_date',
        'payment_method',
        'reference',
        'notes',
        'status',
        'confirmed_at',
        'confirmed_by',
        'payer_name',
        'payer_document',
        'payee_name',
        'payee_document',
        'payer_supplier_id',
        'payee_supplier_id',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'transaction_date' => 'date',
        'confirmed_at' => 'datetime',
    ];

    protected $appends = [
        'formatted_amount',
        'type_label',
        'status_label',
        'payment_method_label',
    ];

    /**
     * Relacionamento com categoria
     */
    public function category()
    {
        return $this->belongsTo(FinancialCategory::class, 'category_id');
    }

    /**
     * Relacionamento polimórfico com origem
     */
    public function source()
    {
        return $this->morphTo();
    }

    /**
     * Relacionamento com usuário que confirmou
     */
    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    /**
     * Relacionamento com anexos
     */
    public function attachments()
    {
        return $this->hasMany(FinancialAttachment::class, 'transaction_id');
    }

    /**
     * Relacionamento com supplier (pagante)
     */
    public function payerSupplier()
    {
        return $this->belongsTo(Supplier::class, 'payer_supplier_id');
    }

    /**
     * Relacionamento com supplier (fornecedor)
     */
    public function payeeSupplier()
    {
        return $this->belongsTo(Supplier::class, 'payee_supplier_id');
    }

    /**
     * Scopes
     */
    public function scopeReceita($query)
    {
        return $query->where('type', 'receita');
    }

    public function scopeDespesa($query)
    {
        return $query->where('type', 'despesa');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeByPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('transaction_date', [$startDate, $endDate]);
    }

    /**
     * Accessors
     */
    public function getFormattedAmountAttribute(): string
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'receita' ? 'Receita' : 'Despesa';
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            default => 'Desconhecido',
        };
    }

    public function getPaymentMethodLabelAttribute(): ?string
    {
        if (!$this->payment_method) {
            return null;
        }

        return match ($this->payment_method) {
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'cash' => 'Dinheiro',
            'bank_transfer' => 'Transferência Bancária',
            'check' => 'Cheque',
            'other' => 'Outro',
            default => $this->payment_method,
        };
    }

    /**
     * Métodos auxiliares
     */
    public function isReceita(): bool
    {
        return $this->type === 'receita';
    }

    public function isDespesa(): bool
    {
        return $this->type === 'despesa';
    }

    public function isConfirmed(): bool
    {
        return $this->status === 'confirmed';
    }
}

