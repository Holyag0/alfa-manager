<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guardian extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'cpf',
        'rg',
        'birth_date',
        'gender',
        'phone',
        'email',
        'profession',
        'address',
    ];

    protected $casts = [
        'birth_date' => 'date',
        'address' => 'array',
    ];

    protected $appends = [
        'age',
        'formatted_phone',
    ];

    // Relacionamentos
    public function students(): BelongsToMany
    {
        return $this->belongsToMany(Student::class, 'student_guardian')
            ->withPivot(['relationship', 'is_primary', 'is_financial', 'can_pickup'])
            ->withTimestamps();
    }

    // Accessors
    public function getAgeAttribute(): int
    {
        return $this->birth_date?->age ?? 0;
    }

    public function getFormattedPhoneAttribute(): string
    {
        $phone = preg_replace('/\D/', '', $this->phone);
        
        if (strlen($phone) === 11) {
            return '(' . substr($phone, 0, 2) . ') ' . substr($phone, 2, 5) . '-' . substr($phone, 7);
        }
        
        return $this->phone;
    }

    // Scopes
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%")
              ->orWhere('cpf', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%");
        });
    }

    // Métodos auxiliares
    public function getPrimaryStudents()
    {
        return $this->students()->wherePivot('is_primary', true)->get();
    }

    public function getFinancialStudents()
    {
        return $this->students()->wherePivot('is_financial', true)->get();
    }
}