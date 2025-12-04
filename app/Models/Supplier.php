<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'is_pagante',
        'is_fornecedor',
        'name',
        'document',
        'email',
        'phone',
        'address',
        'notes',
        'active',
    ];

    protected $casts = [
        'is_pagante' => 'boolean',
        'is_fornecedor' => 'boolean',
        'active' => 'boolean',
    ];

    protected $appends = [
        'type_labels',
        'type_label',
    ];

    /**
     * Relacionamento com transações financeiras (como pagante)
     */
    public function transactionsAsPayer()
    {
        return $this->hasMany(FinancialTransaction::class, 'payer_supplier_id');
    }

    /**
     * Relacionamento com transações financeiras (como fornecedor)
     */
    public function transactionsAsPayee()
    {
        return $this->hasMany(FinancialTransaction::class, 'payee_supplier_id');
    }

    /**
     * Scopes
     */
    public function scopePagante($query)
    {
        return $query->where('is_pagante', true);
    }

    public function scopeFornecedor($query)
    {
        return $query->where('is_fornecedor', true);
    }

    public function scopeActive($query)
    {
        return $query->where('active', true);
    }

    /**
     * Accessors
     */
    public function getTypeLabelsAttribute(): array
    {
        $labels = [];
        if ($this->is_pagante) {
            $labels[] = 'Pagante';
        }
        if ($this->is_fornecedor) {
            $labels[] = 'Fornecedor';
        }
        return $labels;
    }

    /**
     * Retorna label formatado como string (para compatibilidade)
     */
    public function getTypeLabelAttribute(): string
    {
        if ($this->is_pagante && $this->is_fornecedor) {
            return 'Pagante e Fornecedor';
        }
        if ($this->is_pagante) {
            return 'Pagante';
        }
        if ($this->is_fornecedor) {
            return 'Fornecedor';
        }
        return 'Nenhum';
    }

    /**
     * Verifica se é apenas pagante
     */
    public function isOnlyPagante(): bool
    {
        return $this->is_pagante && !$this->is_fornecedor;
    }

    /**
     * Verifica se é apenas fornecedor
     */
    public function isOnlyFornecedor(): bool
    {
        return $this->is_fornecedor && !$this->is_pagante;
    }

    /**
     * Verifica se é ambos
     */
    public function isBoth(): bool
    {
        return $this->is_pagante && $this->is_fornecedor;
    }

    /**
     * Aplicar ordenação com validação de colunas permitidas
     */
    public function applySorting($query, string $sortBy, string $sortOrder = 'asc')
    {
        $allowedSortColumns = [
            'id',
            'name',
            'document',
            'email',
            'phone',
            'is_pagante',
            'is_fornecedor',
            'active',
            'created_at',
            'updated_at',
        ];
        
        $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'name';
        
        $allowedSortOrders = ['asc', 'desc'];
        $sortOrder = in_array(strtolower($sortOrder), $allowedSortOrders) ? strtolower($sortOrder) : 'asc';
        
        return $query->orderBy($sortBy, $sortOrder);
    }

    /**
     * Verifica se o fornecedor/pagante possui transações vinculadas
     */
    public function hasTransactions(): bool
    {
        return $this->transactionsAsPayer()->exists() 
            || $this->transactionsAsPayee()->exists();
    }

    /**
     * Scope para buscar por texto (nome, documento, email)
     */
    public function scopeSearch($query, string $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('name', 'like', '%' . $search . '%')
              ->orWhere('document', 'like', '%' . $search . '%')
              ->orWhere('email', 'like', '%' . $search . '%');
        });
    }
}
