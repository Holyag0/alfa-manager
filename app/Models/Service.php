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
        'category_color'
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
     * Accessor para obter a cor da categoria
     */
    public function getCategoryColorAttribute()
    {
        $category = \App\Models\Category::where('name', $this->category)->first();
        return $category ? $category->color : '#3B82F6';
    }
}
