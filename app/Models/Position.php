<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use App\Traits\LogsActivity;

class Position extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $fillable = [
        'name',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Relacionamento com colaboradores
     */
    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeInactive($query)
    {
        return $query->where('is_active', false);
    }

    /**
     * Accessors
     */
    public function getFormattedStatusAttribute(): string
    {
        return $this->is_active ? 'Ativo' : 'Inativo';
    }

    /**
     * Verifica se o cargo possui colaboradores vinculados
     */
    public function hasEmployees(): bool
    {
        return $this->employees()->exists();
    }

    /**
     * Customizar identificador para logs
     */
    protected function getIdentifier(): string
    {
        return $this->name;
    }
}
