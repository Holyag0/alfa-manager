<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Enrollment extends Model
{
    use HasFactory;

    protected $table = 'enrollments';

    protected $fillable = [
        'student_id',
        'guardian_id',
        'classroom_id',
        'status',
        'process',
        'enrollment_date',
        'notes',
    ];

    public function student()
    {
        return $this->belongsTo(Student::class);
    }

    public function guardian()
    {
        return $this->belongsTo(Guardian::class);
    }

    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    // Removido relacionamento com enrollment_finances (tabela removida)

    /**
     * Relacionamento com faturas da matrícula
     */
    public function invoices()
    {
        return $this->hasMany(EnrollmentInvoice::class);
    }

    /**
     * Relacionamento com pagamentos da matrícula
     */
    public function payments()
    {
        return $this->hasMany(EnrollmentPayment::class);
    }

    /**
     * Calcular total devido dinamicamente
     */
    public function getTotalDue()
    {
        return $this->invoices()->where('status', 'pending')->sum('amount');
    }

    /**
     * Calcular total pago dinamicamente
     */
    public function getTotalPaid()
    {
        return $this->payments()->where('status', 'confirmed')->sum('amount');
    }

    /**
     * Obter status de pagamento baseado nos totais
     */
    public function getPaymentStatus()
    {
        $totalDue = $this->getTotalDue();
        $totalPaid = $this->getTotalPaid();

        if ($totalDue == 0) {
            return 'paid';
        } elseif ($totalPaid >= $totalDue) {
            return 'paid';
        } elseif ($totalPaid > 0) {
            return 'partial';
        } else {
            return 'pending';
        }
    }

    /**
     * Obter resumo financeiro
     */
    public function getFinancialSummary()
    {
        return [
            'total_due' => $this->getTotalDue(),
            'total_paid' => $this->getTotalPaid(),
            'payment_status' => $this->getPaymentStatus(),
            'pending_invoices' => $this->invoices()->where('status', 'pending')->count(),
            'overdue_invoices' => $this->invoices()->where('status', 'overdue')->count(),
        ];
    }

    /**
     * Recalcular totais financeiros
     */
    public function recalculateFinancials()
    {
        // Força o recálculo dos totais
        $this->touch(); // Atualiza updated_at
        
        // Retorna o resumo atualizado
        return $this->getFinancialSummary();
    }
} 