<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Enrollment extends Model
{
    use HasFactory;

    protected $fillable = [
        'student_id',
        'classroom_id',
        'enrollment_date',
        'end_date',
        'status',
        'notes',
    ];

    protected $casts = [
        'enrollment_date' => 'date',
        'end_date' => 'date',
    ];

    protected $appends = [
        'duration_in_days',
        'is_active',
    ];

    // Relacionamentos
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function classroom(): BelongsTo
    {
        return $this->belongsTo(Classroom::class);
    }

    // Accessors
    public function getDurationInDaysAttribute(): ?int
    {
        if (!$this->enrollment_date) {
            return null;
        }

        $endDate = $this->end_date ?? now();
        return $this->enrollment_date->diffInDays($endDate);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === 'active';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByStudent($query, $studentId)
    {
        return $query->where('student_id', $studentId);
    }

    public function scopeByClassroom($query, $classroomId)
    {
        return $query->where('classroom_id', $classroomId);
    }

    public function scopeCurrentYear($query)
    {
        return $query->whereYear('enrollment_date', now()->year);
    }

    // Métodos auxiliares
    public function finish($reason = 'completed', $endDate = null): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        $this->update([
            'status' => $reason,
            'end_date' => $endDate ?? now()->toDateString(),
        ]);

        // Atualiza contador da turma
        $this->classroom->decrement('current_students');

        return true;
    }

    public function reactivate(): bool
    {
        if ($this->status === 'active') {
            return false;
        }

        // Verifica se a turma pode receber o aluno
        if ($this->classroom->is_full) {
            return false;
        }

        $this->update([
            'status' => 'active',
            'end_date' => null,
        ]);

        // Atualiza contador da turma
        $this->classroom->increment('current_students');

        return true;
    }
}