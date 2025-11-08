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
        'academic_year',
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
     * Verificar se a matrícula está completamente finalizada
     */
    public function isFullyEnrolled()
    {
        return $this->process === 'completa' && $this->status === 'active';
    }

    /**
     * Verificar se a matrícula pode ser vinculada à turma
     */
    public function canBeLinkedToClassroom()
    {
        return $this->isFullyEnrolled();
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

    /**
     * Scope para filtrar por ano letivo
     */
    public function scopeByAcademicYear($query, $year)
    {
        return $query->where('academic_year', $year);
    }

    /**
     * Scope para matrícula ativa de um aluno em um ano específico
     */
    public function scopeActiveForStudentInYear($query, $studentId, $year)
    {
        return $query->where('student_id', $studentId)
                    ->where('academic_year', $year)
                    ->where('status', 'active');
    }

    /**
     * Scope para matrículas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para matrículas completas (finalizadas)
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Verificar se pode ser renovada para novo ano
     */
    public function canBeRenewed()
    {
        return in_array($this->status, ['active', 'completed']);
    }

    /**
     * Obter histórico de matrículas do aluno
     */
    public static function getStudentHistory($studentId)
    {
        return self::where('student_id', $studentId)
            ->with(['classroom', 'guardian'])
            ->orderBy('academic_year', 'desc')
            ->get();
    }

    /**
     * Verificar se aluno já tem matrícula ativa em determinado ano
     */
    public static function hasActiveEnrollmentInYear($studentId, $year)
    {
        return self::where('student_id', $studentId)
            ->where('academic_year', $year)
            ->where('status', 'active')
            ->exists();
    }

    /**
     * Relacionamento com histórico de turmas
     */
    public function classroomHistory()
    {
        return $this->hasMany(EnrollmentClassroomHistory::class)
            ->orderBy('start_date', 'desc');
    }

    /**
     * Obter turma atual através do histórico
     */
    public function currentClassroomFromHistory()
    {
        return $this->classroomHistory()
            ->whereNull('end_date')
            ->with('classroom')
            ->first();
    }

    /**
     * Obter todas as turmas que o aluno passou
     */
    public function getAllClassrooms()
    {
        return $this->classroomHistory()->with('classroom')->get();
    }
} 