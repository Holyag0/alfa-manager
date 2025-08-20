<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'type',
        'color',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $appends = [
        'formatted_color'
    ];

    /**
     * Relacionamento com serviços
     */
    public function services()
    {
        return $this->hasMany(Service::class, 'category', 'name');
    }

    /**
     * Relacionamento com pacotes
     */
    public function packages()
    {
        return $this->hasMany(Package::class, 'category', 'name');
    }

    /**
     * Scope para categorias ativas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Scope para categorias de serviços
     */
    public function scopeForServices($query)
    {
        return $query->whereIn('type', ['service', 'both']);
    }

    /**
     * Scope para categorias de pacotes
     */
    public function scopeForPackages($query)
    {
        return $query->whereIn('type', ['package', 'both']);
    }

    /**
     * Accessor para cor formatada
     */
    public function getFormattedColorAttribute()
    {
        return $this->color ?? '#3B82F6';
    }

    /**
     * Verificar se a categoria pode ser usada para serviços
     */
    public function canBeUsedForServices()
    {
        return in_array($this->type, ['service', 'both']);
    }

    /**
     * Verificar se a categoria pode ser usada para pacotes
     */
    public function canBeUsedForPackages()
    {
        return in_array($this->type, ['package', 'both']);
    }
}
