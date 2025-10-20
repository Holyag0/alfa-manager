<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'invoice_number',
        'type',
        'description',
        'amount',
        'due_date',
        'status',
        'paid_date',
        'notes'
    ];

    protected $casts = [
        'amount' => 'float',
        'due_date' => 'date',
        'paid_date' => 'date',
        'type' => 'string',
        'status' => 'string'
    ];

    protected $appends = [
        'formatted_amount',
        'type_label',
        'status_label',
        'is_overdue'
    ];

    /**
     * Relacionamento com matrícula
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relacionamento com pagamentos
     */
    public function payments()
    {
        return $this->hasMany(EnrollmentPayment::class, 'invoice_id');
    }

    /**
     * Scope para faturas pendentes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para faturas pagas
     */
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Scope para faturas vencidas
     */
    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', 'pending');
    }

    /**
     * Scope para tipo de fatura
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Accessor para valor formatado
     */
    public function getFormattedAmountAttribute()
    {
        return 'R$ ' . number_format($this->amount, 2, ',', '.');
    }

    /**
     * Accessor para label do tipo
     */
    public function getTypeLabelAttribute()
    {
        $labels = [
            'monthly' => 'Mensalidade',
            'reservation' => 'Reserva',
            'service' => 'Serviço',
            'package' => 'Pacote',
            'other' => 'Outro'
        ];

        return $labels[$this->type] ?? $this->type;
    }

    /**
     * Accessor para label do status
     */
    public function getStatusLabelAttribute()
    {
        $labels = [
            'pending' => 'Pendente',
            'paid' => 'Pago',
            'overdue' => 'Vencido',
            'cancelled' => 'Cancelado',
            'refunded' => 'Estornado'
        ];

        return $labels[$this->status] ?? $this->status;
    }

    /**
     * Accessor para verificar se está vencida
     */
    public function getIsOverdueAttribute()
    {
        return $this->due_date < now() && $this->status === 'pending';
    }

    /**
     * Gerar número da fatura
     */
    public static function generateInvoiceNumber()
    {
        $lastInvoice = self::orderBy('id', 'desc')->first();
        $lastNumber = $lastInvoice ? (int) $lastInvoice->invoice_number : 0;
        return str_pad($lastNumber + 1, 8, '0', STR_PAD_LEFT);
    }

    /**
     * Marcar como paga
     */
    public function markAsPaid($paidDate = null)
    {
        $this->status = 'paid';
        $this->paid_date = $paidDate ?? now();
        $this->save();
    }

    /**
     * Marcar como vencida
     */
    public function markAsOverdue()
    {
        if ($this->status === 'pending' && $this->due_date < now()) {
            $this->status = 'overdue';
            $this->save();
        }
    }

    /**
     * Reativar serviço estornado (mudar para pending)
     */
    public function reactivate()
    {
        if ($this->status !== 'refunded') {
            throw new \Exception('Apenas serviços estornados podem ser reativados');
        }

        $this->status = 'pending';
        $this->paid_date = null;
        $this->save();

        // Recalcular totais da matrícula
        $this->enrollment->recalculateFinancials();

        return $this;
    }
}
