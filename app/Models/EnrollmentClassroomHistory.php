<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EnrollmentClassroomHistory extends Model
{
    use HasFactory;

    protected $table = 'enrollment_classroom_history';

    protected $fillable = [
        'enrollment_id',
        'classroom_id',
        'start_date',
        'end_date',
        'reason',
        'notes',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Relacionamento com matrícula
     */
    public function enrollment()
    {
        return $this->belongsTo(Enrollment::class);
    }

    /**
     * Relacionamento com turma
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Scope para histórico ativo (ainda está na turma)
     */
    public function scopeActive($query)
    {
        return $query->whereNull('end_date');
    }

    /**
     * Scope para histórico finalizado
     */
    public function scopeCompleted($query)
    {
        return $query->whereNotNull('end_date');
    }

    /**
     * Scope para histórico de uma matrícula específica
     */
    public function scopeForEnrollment($query, $enrollmentId)
    {
        return $query->where('enrollment_id', $enrollmentId)
            ->orderBy('start_date', 'desc');
    }

    /**
     * Scope para histórico de uma turma específica
     */
    public function scopeForClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId)
            ->orderBy('start_date', 'desc');
    }

    /**
     * Verificar se o registro está ativo
     */
    public function isActive()
    {
        return is_null($this->end_date);
    }

    /**
     * Finalizar este vínculo (aluno saiu da turma)
     */
    public function complete($endDate = null, $notes = null)
    {
        $this->end_date = $endDate ?? now();
        if ($notes) {
            $this->notes = ($this->notes ? $this->notes . "\n" : '') . $notes;
        }
        $this->save();
        return $this;
    }
}


