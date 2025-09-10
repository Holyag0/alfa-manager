<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\Package;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'price',
        'status',
        'category',
        'description',
    ];

    protected $casts = [
        'price' => 'float',
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_price',
        'category_color',
        'is_classroom_linked',
        'classroom_linking_label'
    ];

    /**
     * Relacionamento com pacotes (many-to-many)
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class);
    }

    /**
     * Relacionamento com categoria
     */
    public function category()
    {
        return $this->belongsTo(Category::class, 'category', 'name');
    }

    /**
     * Scope para serviços ativos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para buscar por categoria
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Accessor para formatar o preço
     */
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Relacionamento com serviços de turma
     */
    public function classroomServices()
    {
        return $this->hasMany(ClassroomService::class);
    }

    /**
     * Scope para serviços vinculados a turmas
     */
    public function scopeClassroomLinked($query)
    {
        return $query->whereHas('classroomServices');
    }

    /**
     * Scope para serviços globais
     */
    public function scopeGlobal($query)
    {
        return $query->whereDoesntHave('classroomServices');
    }

    /**
     * Scope para serviços de mensalidade
     */
    public function scopeMonthly($query)
    {
        return $query->where('category', 'Mensalidade');
    }

    /**
     * Scope para serviços de reserva
     */
    public function scopeReservation($query)
    {
        return $query->where('category', 'Reserva');
    }

    /**
     * Accessor para obter a cor da categoria
     */
    public function getCategoryColorAttribute()
    {
        $category = \App\Models\Category::where('name', $this->category)->first();
        return $category ? $category->color : '#3B82F6';
    }

    /**
     * Accessor para label de vinculação com turma
     */
    public function getClassroomLinkingLabelAttribute()
    {
        return $this->isClassroomLinked() ? 'Vinculado a Turmas' : 'Serviço Global';
    }

    /**
     * Accessor para is_classroom_linked
     */
    public function getIsClassroomLinkedAttribute()
    {
        return $this->isClassroomLinked();
    }

    /**
     * Verificar se é serviço de mensalidade
     */
    public function isMonthly()
    {
        return $this->category === 'Mensalidade';
    }

    /**
     * Verificar se é serviço de reserva
     */
    public function isReservation()
    {
        return $this->category === 'Reserva';
    }

    /**
     * Verificar se serviço é vinculado a turmas
     */
    public function isClassroomLinked()
    {
        return $this->classroomServices()->exists();
    }

    /**
     * Buscar preço para uma turma específica
     */
    public function getPriceForClassroom($classroomId)
    {
        $classroomService = $this->classroomServices()
            ->where('classroom_id', $classroomId)
            ->active()
            ->first();

        return $classroomService ? $classroomService->price : $this->price;
    }

    /**
     * Buscar descrição para uma turma específica
     */
    public function getDescriptionForClassroom($classroomId)
    {
        $classroomService = $this->classroomServices()
            ->where('classroom_id', $classroomId)
            ->active()
            ->first();

        return $classroomService ? $classroomService->full_description : $this->name;
    }
}
