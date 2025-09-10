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
    ];

    /**
     * Relacionamento com serviços da turma
     */
    public function services()
    {
        return $this->hasMany(ClassroomService::class);
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

    // Exemplo de método estático para buscar turmas por nome
    public static function searchByName($name)
    {
        return self::where('name', 'like', "%$name%") ->get();
    }
} 