<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SiblingGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'siblings_count',
        'active_siblings_count',
    ];

    /**
     * Relacionamento com responsáveis (irmãos)
     */
    public function guardians()
    {
        return $this->hasMany(Guardian::class);
    }

    /**
     * Obter alunos do grupo através dos responsáveis
     * Como Guardian e Student têm relacionamento many-to-many,
     * não podemos usar hasManyThrough. Este método retorna
     * todos os estudantes únicos associados aos responsáveis do grupo.
     */
    public function students()
    {
        return Student::whereHas('guardians', function ($query) {
            $query->where('sibling_group_id', $this->id);
        });
    }

    /**
     * Scope para grupos ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Accessor para contar total de irmãos
     */
    public function getSiblingsCountAttribute()
    {
        return $this->guardians()->count();
    }

    /**
     * Accessor para contar irmãos ativos
     */
    public function getActiveSiblingsCountAttribute()
    {
        return $this->guardians()->whereHas('students', function ($query) {
            $query->whereHas('enrollments', function ($enrollmentQuery) {
                $enrollmentQuery->where('status', 'active');
            });
        })->count();
    }

    /**
     * Verificar se tem irmãos matriculados
     */
    public function hasActiveSiblings()
    {
        return $this->active_siblings_count > 0;
    }

    /**
     * Obter irmãos matriculados
     */
    public function getActiveSiblings()
    {
        return $this->guardians()->whereHas('students', function ($query) {
            $query->whereHas('enrollments', function ($enrollmentQuery) {
                $enrollmentQuery->where('status', 'active');
            });
        })->with('students.enrollments.classroom')->get();
    }
}
