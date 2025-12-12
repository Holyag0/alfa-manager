<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'name',
        'birth_date',
        'email',
        'phone',
        'cpf',
        'photo',
        'rg',
        'birth_certificate_number',
        'notes',
    ];

    // Exemplo de método estático para buscar estudantes por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }

    public function guardians()
    {
        return $this->belongsToMany(Guardian::class, 'guardian_student')
            ->withTimestamps();
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Obter irmãos do aluno através dos grupos de irmãos dos responsáveis
     */
    public function getSiblings()
    {
        // Buscar todos os grupos de irmãos dos responsáveis deste aluno
        $siblingGroupIds = $this->guardians()
            ->whereNotNull('sibling_group_id')
            ->pluck('sibling_group_id')
            ->unique();

        if ($siblingGroupIds->isEmpty()) {
            return collect();
        }

        // Buscar todos os alunos que compartilham responsáveis no mesmo grupo
        return Student::whereHas('guardians', function ($query) use ($siblingGroupIds) {
            $query->whereIn('sibling_group_id', $siblingGroupIds);
        })
        ->where('id', '!=', $this->id)
        ->with(['guardians', 'enrollments.classroom'])
        ->get();
    }

    /**
     * Verificar se o aluno tem irmãos
     */
    public function hasSiblings(): bool
    {
        return $this->getSiblings()->count() > 0;
    }
} 