<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Classroom extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'grade_level',
        'section',
        'school_year',
        'max_students',
        'current_students',
        'teacher_id',
        'schedule',
        'room_number',
        'status',
    ];

    protected $casts = [
        'schedule' => 'array',
        'school_year' => 'integer',
        'max_students' => 'integer',
        'current_students' => 'integer',
    ];

    protected $appends = [
        'available_spots',
        'occupancy_percentage',
        'is_full',
        'formatted_name',
    ];

    // Relacionamentos
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class)->where('status', 'active');
    }

    public function students(): HasManyThrough
    {
        return $this->hasManyThrough(
            Student::class,
            Enrollment::class,
            'classroom_id',
            'id',
            'id',
            'student_id'
        )->where('enrollments.status', 'active');
    }

    // Accessors
    public function getAvailableSpotsAttribute(): int
    {
        return max(0, $this->max_students - $this->current_students);
    }

    public function getOccupancyPercentageAttribute(): float
    {
        if ($this->max_students === 0) {
            return 0;
        }
        
        return round(($this->current_students / $this->max_students) * 100, 1);
    }

    public function getIsFullAttribute(): bool
    {
        return $this->current_students >= $this->max_students;
    }

    public function getFormattedNameAttribute(): string
    {
        return "{$this->grade_level}{$this->section}";
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeCurrentYear($query)
    {
        return $query->where('school_year', now()->year);
    }

    public function scopeByGrade($query, $grade)
    {
        return $query->where('grade_level', $grade);
    }

    public function scopeWithAvailableSpots($query)
    {
        return $query->whereRaw('current_students < max_students');
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('grade_level', 'like', "%{$search}%")
              ->orWhere('section', 'like', "%{$search}%")
              ->orWhere('room_number', 'like', "%{$search}%");
        });
    }

    // Métodos auxiliares
    public function canEnrollStudent(): bool
    {
        return $this->status === 'active' && !$this->is_full;
    }

    public function enrollStudent(Student $student, $enrollmentDate = null): Enrollment
    {
        if (!$this->canEnrollStudent()) {
            throw new \Exception('Turma não pode receber novos alunos');
        }

        // Finaliza matrícula ativa anterior do aluno
        $student->activeEnrollment?->update([
            'status' => 'transferred',
            'end_date' => now()->toDateString(),
        ]);

        // Cria nova matrícula
        $enrollment = $this->enrollments()->create([
            'student_id' => $student->id,
            'enrollment_date' => $enrollmentDate ?? now()->toDateString(),
            'status' => 'active',
        ]);

        // Atualiza contador
        $this->increment('current_students');

        return $enrollment;
    }

    public function removeStudent(Student $student, $reason = 'transferred'): bool
    {
        $enrollment = $this->activeEnrollments()
            ->where('student_id', $student->id)
            ->first();

        if (!$enrollment) {
            return false;
        }

        $enrollment->update([
            'status' => $reason,
            'end_date' => now()->toDateString(),
        ]);

        $this->decrement('current_students');

        return true;
    }

    public function getStudentsList()
    {
        return $this->students()
            ->select(['id', 'name', 'registration_number', 'photo_path'])
            ->orderBy('name')
            ->get();
    }

    public function updateStudentCount(): void
    {
        $count = $this->activeEnrollments()->count();
        $this->update(['current_students' => $count]);
    }

    // Método estático para criar nome da turma
    public static function generateName($gradeLevel, $section): string
    {
        return $gradeLevel . $section;
    }

    // Validação de capacidade
    public function validateCapacity(): bool
    {
        $actualCount = $this->activeEnrollments()->count();
        
        if ($actualCount !== $this->current_students) {
            $this->update(['current_students' => $actualCount]);
        }

        return $actualCount <= $this->max_students;
    }
}