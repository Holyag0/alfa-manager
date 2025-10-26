<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentPayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'invoice_id',
        'paid_by_guardian_id',
        'payment_number',
        'type',
        'description',
        'amount',
        'original_amount',
        'discount_amount',
        'interest_amount',
        'fine_amount',
        'method',
        'payment_date',
        'reference',
        'status',
        'notes'
    ];

    protected $casts = [
        'amount' => 'float',
        'original_amount' => 'float',
        'discount_amount' => 'float',
        'interest_amount' => 'float',
        'fine_amount' => 'float',
        'payment_date' => 'date',
        'type' => 'string',
        'method' => 'string',
        'status' => 'string'
    ];

    protected $appends = [
        'formatted_amount',
        'formatted_original_amount',
        'formatted_interest_amount',
        'formatted_discount_amount',
        'type_label',
        'method_label',
        'status_label'
    ];

    /**
     * Relacionamento com matrícula
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relacionamento com fatura
     */
    public function invoice()
    {
        return $this->belongsTo(EnrollmentInvoice::class, 'invoice_id');
    }

    /**
     * Relacionamento com responsável que efetuou o pagamento
     */
    public function paidByGuardian()
    {
        return $this->belongsTo(Guardian::class, 'paid_by_guardian_id');
    }

    /**
     * Scope para pagamentos confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    /**
     * Scope para pagamentos pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para tipo de pagamento
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para método de pagamento
     */
    public function scopeByMethod($query, $method)
    {
        return $query->where('method', $method);
    }

    /**
     * Accessor para valor formatado
     */
    public function getFormattedAmountAttribute()
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Accessor para valor original formatado
     */
    public function getFormattedOriginalAmountAttribute()
    {
        $amount = $this->original_amount ?? $this->amount;
        return 'R$ ' . number_format($amount, 2, ',', '.');
    }

    /**
     * Accessor para juros formatado
     */
    public function getFormattedInterestAmountAttribute()
    {
        return 'R$ ' . number_format($this->interest_amount ?? 0, 2, ',', '.');
    }

    /**
     * Accessor para desconto formatado
     */
    public function getFormattedDiscountAmountAttribute()
    {
        return 'R$ ' . number_format($this->discount_amount ?? 0, 2, ',', '.');
    }

    /**
     * Accessor para label do tipo
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'reservation' => 'Reserva',
            'monthly' => 'Mensalidade',
            'service' => 'Serviço',
            'package' => 'Pacote',
            'other' => 'Outro'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Accessor para label do método
     */
    public function getMethodLabelAttribute()
    {
        $labels = [
            'cash' => 'Dinheiro',
            'pix' => 'PIX',
            'credit_card' => 'Cartão de Crédito',
            'debit_card' => 'Cartão de Débito',
            'bank_transfer' => 'Transferência Bancária',
            'check' => 'Cheque',
            'other' => 'Outro'
        ];

        return $labels[$this->method] ?? $this->method;
    }

    /**
     * Accessor para label do status
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendente',
            'confirmed' => 'Confirmado',
            'cancelled' => 'Cancelado',
            'refunded' => 'Estornado'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Gerar número do pagamento
     */
    public static function generatePaymentNumber()
    {
        $lastPayment = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastPayment ? (int) $lastPayment->payment_number : 0;
        return str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Confirmar pagamento
     */
    public function confirm()
    {
        $this->status = 'confirmed';
        $this->save();

        // Se há uma fatura associada, marcar como paga
        if ($this->invoice) {
            $this->invoice->markAsPaid($this->payment_date);
        }

        // Recalcular totais da matrícula
        if ($this->enrollment->finance) {
            $this->enrollment->finance->calculateTotalDue();
        }
    }

    /**
     * Cancelar pagamento
     */
    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();

        // Recalcular totais da matrícula
        if ($this->enrollment->finance) {
            $this->enrollment->finance->calculateTotalDue();
        }
    }

    /**
     * Estornar pagamento
     */
    public function refund($reason = null)
    {
        $this->status = 'refunded';
        if ($reason) {
            $this->notes = ($this->notes ? $this->notes . ' | ' : '') . 'Estorno: ' . $reason;
        }
        $this->save();

        // Se há uma fatura associada, marcar como estornada
        if ($this->invoice) {
            $this->invoice->status = 'refunded';
            $this->invoice->paid_date = null;
            $this->invoice->save();
        }

        // Recalcular totais da matrícula
        $this->enrollment->recalculateFinancials();
    }

    /**
     * Verificar se o pagamento pode ser estornado
     */
    public function canBeRefunded()
    {
        return $this->status === 'confirmed';
    }

    /**
     * Verificar se o pagamento pode ser editado
     */
    public function canBeEdited()
    {
        return $this->status === 'confirmed';
    }
}
