<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialCategory extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'type_label',
    ];

    /**
     * Relacionamento com transações
     */
    public function transactions()
    {
        return $this->hasMany(FinancialTransaction::class, 'category_id');
    }

    /**
     * Scope para categorias ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para receitas
     */
    public function scopeReceita($query)
    {
        return $query->where('type', 'receita');
    }

    /**
     * Scope para despesas
     */
    public function scopeDespesa($query)
    {
        return $query->where('type', 'despesa');
    }

    /**
     * Accessor para label do tipo
     */
    public function getTypeLabelAttribute(): string
    {
        return $this->type === 'receita' ? 'Receita' : 'Despesa';
    }
}

