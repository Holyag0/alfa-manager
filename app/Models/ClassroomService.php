<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClassroomService extends Model
{
    use HasFactory;

    protected $fillable = [
        'classroom_id',
        'service_id',
        'price',
        'description',
        'is_active'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean'
    ];

    protected $appends = [
        'formatted_price',
        'full_description'
    ];

    /**
     * Relacionamento com a turma
     */
    public function classroom()
    {
        return $this->belongsTo(Classroom::class);
    }

    /**
     * Relacionamento com o serviço
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Scope para serviços ativos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por tipo de serviço
     */
    public function scopeByServiceType($query, $type)
    {
        return $query->whereHas('service', function ($q) use ($type) {
            $q->where('type', $type);
        });
    }

    /**
     * Acessor para preço formatado
     */
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Acessor para descrição completa
     */
    public function getFullDescriptionAttribute()
    {
        if ($this->description) {
            return $this->description;
        }
        
        return $this->service->name . ' - ' . $this->classroom->name;
    }

    /**
     * Buscar serviço de reserva para uma turma
     */
    public static function findReservationService($classroomId)
    {
        return self::where('classroom_id', $classroomId)
            ->whereHas('service', function ($query) {
                $query->where('type', 'reservation');
            })
            ->active()
            ->first();
    }

    /**
     * Buscar serviço de mensalidade para uma turma
     */
    public static function findMonthlyService($classroomId)
    {
        return self::where('classroom_id', $classroomId)
            ->whereHas('service', function ($query) {
                $query->where('type', 'monthly');
            })
            ->active()
            ->first();
    }
}
