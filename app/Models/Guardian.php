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
    public function scopeSearch($query, string $search)
    {
        if (empty(trim($search))) {
            return $query;
        }

        $search = trim($search);
        $cleanSearch = preg_replace('/\D/', '', $search); // Remove pontuação

        return $query->where(function ($q) use ($search, $cleanSearch) {
            // Busca por nome e email (texto)
            $q->where('name', 'like', "%{$search}%")
            ->orWhere('email', 'like', "%{$search}%");
            
            // Busca por telefone e CPF (apenas números)
            if (strlen($cleanSearch) >= 3) {
                $q->orWhereRaw("REPLACE(REPLACE(REPLACE(phone, '(', ''), ')', ''), '-', '') LIKE ?", ["%{$cleanSearch}%"])
                ->orWhereRaw("REPLACE(REPLACE(REPLACE(cpf, '.', ''), '-', ''), ' ', '') LIKE ?", ["%{$cleanSearch}%"]);
            }
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