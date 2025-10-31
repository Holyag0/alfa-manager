<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Classroom extends Model
{
    use HasFactory;

    protected $table = 'classrooms';

    protected $fillable = [
        'name',
        'year',
        'shift',
        'vacancies',
        'max_students',
        'current_students',
        'is_active',
    ];

    /**
     * Relacionamento com serviços da turma
     */
    public function services()
    {
        return $this->hasMany(ClassroomService::class);
    }

    /**
     * Relacionamento com matrículas da turma
     */
    public function enrollments()
    {
        return $this->hasMany(Enrollment::class);
    }

    /**
     * Scope para turmas ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Buscar serviço de reserva da turma
     */
    public function getReservationService()
    {
        return $this->services()
            ->whereHas('service', function ($query) {
                $query->where('type', 'reservation');
            })
            ->active()
            ->first();
    }

    /**
     * Buscar serviço de mensalidade da turma
     */
    public function getMonthlyService()
    {
        return $this->services()
            ->whereHas('service', function ($query) {
                $query->where('type', 'monthly');
            })
            ->active()
            ->first();
    }

    /**
     * Contar alunos realmente matriculados (processo completo)
     */
    public function getEnrolledStudentsCount()
    {
        return $this->enrollments()
            ->where('process', 'completa')
            ->where('status', 'active')
            ->count();
    }

    /**
     * Verificar se há vagas disponíveis para matrícula completa
     */
    public function hasAvailableSlots()
    {
        $enrolledCount = $this->getEnrolledStudentsCount();
        return $enrolledCount < $this->max_students;
    }

    /**
     * Obter vagas disponíveis
     */
    public function getAvailableSlots()
    {
        $enrolledCount = $this->getEnrolledStudentsCount();
        return max(0, $this->max_students - $enrolledCount);
    }

    /**
     * Atualizar contador de alunos matriculados
     */
    public function updateEnrolledCount()
    {
        $this->current_students = $this->getEnrolledStudentsCount();
        $this->save();
        return $this;
    }

    /**
     * Verificar se aluno pode ser matriculado nesta turma
     */
    public function canEnrollStudent($studentId = null)
    {
        // Verificar se há vagas
        if (!$this->hasAvailableSlots()) {
            return false;
        }

        // Verificar se aluno já está matriculado (se fornecido)
        if ($studentId) {
            $alreadyEnrolled = $this->enrollments()
                ->where('student_id', $studentId)
                ->where('process', 'completa')
                ->where('status', 'active')
                ->exists();
            
            if ($alreadyEnrolled) {
                return false;
            }
        }

        return true;
    }

    // Exemplo de método estático para buscar turmas por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }
} 