<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Package extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'category',
        'price',
        'status',
        'description',
    ];

    protected $casts = [
        'price' => 'float',
        'status' => 'string',
    ];

    protected $appends = [
        'formatted_price',
        'total_services_value',
        'has_discount',
        'discount_percentage'
    ];

    /**
     * Relacionamento com serviços (many-to-many)
     */
    public function services()
    {
        return $this->belongsToMany(Service::class);
    }

    /**
     * Scope para pacotes ativos
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
     * Calcular valor total dos serviços
     */
    public function getTotalServicesValueAttribute()
    {
        $total = $this->services->sum('price');
        return 'R$ ' . number_format($total, 2, ',', '.');
    }

    /**
     * Verificar se há desconto no pacote
     */
    public function getHasDiscountAttribute()
    {
        $totalServices = $this->services->sum('price');
        return $this->price < $totalServices;
    }

    /**
     * Calcular percentual de desconto
     */
    public function getDiscountPercentageAttribute()
    {
        $totalServices = $this->services->sum('price');
        if ($totalServices > 0) {
            $discount = (($totalServices - $this->price) / $totalServices) * 100;
            return round($discount, 2);
        }
        return 0;
    }
}
