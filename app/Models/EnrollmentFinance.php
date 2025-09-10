<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentFinance extends Model
{
    use HasFactory;

    protected $fillable = [
        'enrollment_id',
        'monthly_fee',
        'reservation_fee',
        'total_paid',
        'total_due',
        'next_due_date',
        'payment_status',
        'notes'
    ];

    protected $casts = [
        'monthly_fee' => 'float',
        'reservation_fee' => 'float',
        'total_paid' => 'float',
        'total_due' => 'float',
        'next_due_date' => 'date',
        'payment_status' => 'string'
    ];

    protected $appends = [
        'formatted_monthly_fee',
        'formatted_reservation_fee',
        'formatted_total_paid',
        'formatted_total_due',
        'payment_status_label'
    ];

    /**
     * Relacionamento com matrícula
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relacionamento com faturas
     */
    public function invoices()
    {
        return $this->hasMany(EnrollmentInvoice::class, 'enrollment_id', 'enrollment_id');
    }

    /**
     * Relacionamento com pagamentos
     */
    public function payments()
    {
        return $this->hasMany(EnrollmentPayment::class, 'enrollment_id', 'enrollment_id');
    }

    /**
     * Scope para status de pagamento
     */
    public function scopeByPaymentStatus($query, $status)
    {
        return $query->where('payment_status', $status);
    }

    /**
     * Scope para vencimentos próximos
     */
    public function scopeDueSoon($query, $days = 7)
    {
        return $query->where('next_due_date', '<=', now()->addDays($days))
                    ->where('payment_status', '!=', 'paid');
    }

    /**
     * Scope para vencidos
     */
    public function scopeOverdue($query)
    {
        return $query->where('next_due_date', '<', now())
                    ->where('payment_status', '!=', 'paid');
    }

    /**
     * Accessor para mensalidade formatada
     */
    public function getFormattedMonthlyFeeAttribute()
    {
        return 'R$ ' . number_format($this->monthly_fee, 2, ',', '.');
    }

    /**
     * Accessor para taxa de reserva formatada
     */
    public function getFormattedReservationFeeAttribute()
    {
        return 'R$ ' . number_format($this->reservation_fee, 2, ',', '.');
    }

    /**
     * Accessor para total pago formatado
     */
    public function getFormattedTotalPaidAttribute()
    {
        return 'R$ ' . number_format($this->total_paid, 2, ',', '.');
    }

    /**
     * Accessor para total em aberto formatado
     */
    public function getFormattedTotalDueAttribute()
    {
        return 'R$ ' . number_format($this->total_due, 2, ',', '.');
    }

    /**
     * Accessor para label do status de pagamento
     */
    public function getPaymentStatusLabelAttribute()
    {
        $labels = [
            'paid' => 'Pago',
            'pending' => 'Pendente',
            'overdue' => 'Vencido',
            'partial' => 'Parcial'
        ];

        return $labels[$this->payment_status] ?? $this->payment_status;
    }

    /**
     * Calcular total em aberto
     */
    public function calculateTotalDue()
    {
        $totalInvoices = $this->invoices()
            ->where('status', '!=', 'cancelled')
            ->sum('amount');
        
        $totalPayments = $this->payments()
            ->where('status', 'confirmed')
            ->sum('amount');

        $this->total_due = $totalInvoices - $totalPayments;
        $this->total_paid = $totalPayments;
        
        // Atualizar status de pagamento
        if ($this->total_due <= 0) {
            $this->payment_status = 'paid';
        } elseif ($this->total_paid > 0) {
            $this->payment_status = 'partial';
        } else {
            $this->payment_status = 'pending';
        }

        $this->save();
        
        return $this->total_due;
    }
}
