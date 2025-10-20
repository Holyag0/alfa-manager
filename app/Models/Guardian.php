<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Guardian extends Model
{
    use HasFactory;

    protected $table = 'guardians';

    protected $fillable = [
        'name',
        'relationship', // Parentesco com o estudante
        'cpf',
        'rg',
        'marital_status',
        'status',
        'guardian_type',
        'occupation',
        'workplace',
        'birth_date',
        'gender',
        'notes',
        'sibling_group_id', // Grupo de irmãos
    ];

    // Exemplo de método estático para buscar responsáveis por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }

    public function students()
    {
        return $this->belongsToMany(Student::class, 'guardian_student')
            ->withTimestamps();
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function enrollments()
    {
        return $this->hasMany(Enrollment::class); // Matrículas que este responsável abriu
    }

    /**
     * Relacionamento com grupo de irmãos
     */
    public function siblingGroup()
    {
        return $this->belongsTo(SiblingGroup::class);
    }

    /**
     * Verificar se tem irmãos no mesmo grupo
     */
    public function hasSiblings()
    {
        return $this->sibling_group_id && $this->siblingGroup;
    }

    /**
     * Obter irmãos do mesmo grupo
     */
    public function getSiblings()
    {
        if (!$this->sibling_group_id) {
            return collect();
        }

        return Guardian::where('sibling_group_id', $this->sibling_group_id)
            ->where('id', '!=', $this->id)
            ->get();
    }

    /**
     * Obter irmãos matriculados
     */
    public function getActiveSiblings()
    {
        if (!$this->sibling_group_id) {
            return collect();
        }

        return Guardian::where('sibling_group_id', $this->sibling_group_id)
            ->where('id', '!=', $this->id)
            ->whereHas('students.enrollments', function ($query) {
                $query->where('status', 'active');
            })
            ->with('students.enrollments.classroom')
            ->get();
    }

    /**
     * Verificar se tem irmãos matriculados
     */
    public function hasActiveSiblings()
    {
        return $this->getActiveSiblings()->count() > 0;
    }
} 