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
        'has_discount',
        'discount_amount',
        'status',
        'category',
        'description',
    ];

    protected $casts = [
        'price' => 'float',
        'has_discount' => 'boolean',
        'discount_amount' => 'float',
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_price',
        'category_color',
        'is_classroom_linked',
        'classroom_linking_label',
        'final_price',
        'formatted_final_price',
        'formatted_discount_amount'
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
     * Accessor para formatar o preço original
     */
    public function getFormattedPriceAttribute()
    {
        return 'R$ ' . number_format($this->price, 2, ',', '.');
    }

    /**
     * Accessor para preço final (com desconto aplicado)
     */
    public function getFinalPriceAttribute()
    {
        if ($this->has_discount && $this->discount_amount > 0) {
            // Desconto por valor fixo (apenas para serviços)
            // Proteção contra valores negativos
            return max(0, $this->price - $this->discount_amount);
        }
        return $this->price;
    }

    /**
     * Accessor para preço final formatado
     */
    public function getFormattedFinalPriceAttribute()
    {
        return 'R$ ' . number_format($this->final_price, 2, ',', '.');
    }

    /**
     * Accessor para valor do desconto formatado
     */
    public function getFormattedDiscountAmountAttribute()
    {
        if ($this->has_discount && $this->discount_amount > 0) {
            return 'R$ ' . number_format($this->discount_amount, 2, ',', '.');
        }
        return 'R$ 0,00';
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
