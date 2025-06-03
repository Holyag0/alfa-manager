<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Student extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'registration_number',
        'name',
        'birth_date',
        'cpf',
        'rg',
        'gender',
        'phone',
        'email',
        'address',
        'photo_path',
        'status',
        'enrollment_date',
        'notes',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'enrollment_date' => 'date',
        'address' => 'array',
    ];

    protected $appends = [
        'age',
        'formatted_registration_number',
        'current_classroom',
        'photo_url',
    ];

    // Relacionamentos
    public function guardians(): BelongsToMany
{
    return $this->belongsToMany(Guardian::class, 'student_guardian')
        ->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup'])
        ->withTimestamps();
}

    public function enrollments(): HasMany
    {
        return $this->hasMany(Enrollment::class);
    }

    public function activeEnrollment(): HasOne
    {
        return $this->hasOne(Enrollment::class)->where('status', 'active');
    }

    public function currentClassroom()
    {
        return $this->activeEnrollment?->classroom;
    }

    // Accessors
    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    public function getFormattedRegistrationNumberAttribute(): string
    {
        return str_pad($this->registration_number, 7, '0', STR_PAD_LEFT);
    }

    public function getCurrentClassroomAttribute(): ?string
    {
        return $this->currentClassroom()?->name;
    }

    public function getPhotoUrlAttribute(): ?string
    {
        return $this->photo_path ? asset('storage/' . $this->photo_path) : null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByClassroom($query, $classroomId)
    {
        return $query->whereHas('enrollments', function ($q) use ($classroomId) {
            $q->where('classroom_id', $classroomId)->where('status', 'active');
        });
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('registration_number', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%");
        });
    }

    // Métodos auxiliares
    public function generateRegistrationNumber(): string
    {
        $year = now()->year;
        $lastStudent = static::whereYear('created_at', $year)
            ->orderBy('registration_number', 'desc')
            ->first();

        if ($lastStudent) {
            $lastNumber = (int) substr($lastStudent->registration_number, 4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $year . str_pad($newNumber, 3, '0', STR_PAD_LEFT);
    }

    public function transferToClassroom(Classroom $newClassroom, $date = null): bool
    {
        $currentEnrollment = $this->activeEnrollment;
        
        if (!$currentEnrollment) {
            return false;
        }

        // Finaliza matrícula atual
        $currentEnrollment->update([
            'status' => 'transferred',
            'end_date' => $date ?? now()->toDateString(),
        ]);

        // Cria nova matrícula
        $this->enrollments()->create([
            'classroom_id' => $newClassroom->id,
            'enrollment_date' => $date ?? now()->toDateString(),
            'status' => 'active',
        ]);

        // Atualiza contadores
        $currentEnrollment->classroom->decrement('current_students');
        $newClassroom->increment('current_students');

        return true;
    }

    public function getPrimaryGuardian(): ?Guardian
    {
        return $this->guardians()->wherePivot('is_primary', true)->first();
    }

    public function getFinancialGuardian(): ?Guardian
    {
        return $this->guardians()->wherePivot('is_financial', true)->first();
    }
}