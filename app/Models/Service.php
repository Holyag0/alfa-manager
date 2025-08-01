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
        'price' => 'decimal:2',
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_price'
    ];

    /**
     * Relacionamento com pacotes (many-to-many)
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class);
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
}
