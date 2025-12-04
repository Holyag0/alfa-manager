<?php

namespace App\Services;

use App\Models\Supplier;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class SupplierService
{
    protected Supplier $supplier;

    public function __construct(Supplier $supplier)
    {
        $this->supplier = $supplier;
    }

    /**
     * Buscar fornecedores/pagantes com filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->supplier->newQuery();

        // Aplicar filtros usando scopes do model
        if (isset($filters['is_pagante']) && $filters['is_pagante'] !== '') {
            $query->where('is_pagante', $filters['is_pagante'] === 'true');
        }

        if (isset($filters['is_fornecedor']) && $filters['is_fornecedor'] !== '') {
            $query->where('is_fornecedor', $filters['is_fornecedor'] === 'true');
        }

        if (isset($filters['active']) && $filters['active'] !== '') {
            $query->where('active', $filters['active'] === 'true');
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        // Ordenação usando método do model
        $query = $this->supplier->applySorting($query,  $sortBy = 'id', $sortOrder ='desc');

        return $query->paginate($perPage);
    }

    /**
     * Encontrar fornecedor/pagante por ID
     */
    public function find(int $id): ?Supplier
    {
        return $this->supplier->find($id);
    }
    /**
     * Encontrar fornecedor/pagante por ID com contagem de transações
     */
    public function findWithTransactionCounts(int $id): Supplier
    {
        return $this->supplier
            ->withCount([
                'transactionsAsPayer',
                'transactionsAsPayee'
            ])
            ->findOrFail($id);
    }
    /**
     * Criar um novo fornecedor/pagante
     */
    public function create(array $data): Supplier
    {
        // Converter valores booleanos
        $data = $this->normalizeBooleanFields($data);
        // Validar regras de negócio
        $this->validateBusinessRules($data);

        return $this->supplier->create($data);
    }

    /**
     * Atualizar fornecedor/pagante
     */
    public function update(int $id, array $data): Supplier
    {
        $supplier = $this->supplier->findOrFail($id);
        // Converter valores booleanos
        $data = $this->normalizeBooleanFields($data);

        // Validar regras de negócio
        $this->validateBusinessRules($data);

        $supplier->update($data);
        return $supplier->fresh();
    }
    /**
     * Excluir fornecedor/pagante
     */
    public function delete(int $id): bool
    {
        $supplier = $this->supplier->findOrFail($id);

        // Verificar se há transações vinculadas (regra de negócio)
        if ($supplier->hasTransactions()) {
            throw new \Exception('Não é possível excluir fornecedor/pagante com transações vinculadas.');
        }

        return $supplier->delete();
    }
    /**
     * Listar fornecedores/pagantes ativos para API (selects)
     */
    public function listActive(array $filters = []): Collection
    {
        $query = $this->supplier->active();

        if (isset($filters['is_pagante']) && $filters['is_pagante'] !== '') {
            $query->where('is_pagante', $filters['is_pagante'] === 'true');
        }

        if (isset($filters['is_fornecedor']) && $filters['is_fornecedor'] !== '') {
            $query->where('is_fornecedor', $filters['is_fornecedor'] === 'true');
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('name', 'like', '%' . $filters['search'] . '%')
                  ->orWhere('document', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);
    }

    /**
     * Normalizar campos booleanos
     */
    protected function normalizeBooleanFields(array $data): array
    {
        $data['is_pagante'] = filter_var($data['is_pagante'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['is_fornecedor'] = filter_var($data['is_fornecedor'] ?? false, FILTER_VALIDATE_BOOLEAN);
        $data['active'] = isset($data['active']) ? filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) : true;

        return $data;
    }

    /**
     * Validar regras de negócio
     */
    protected function validateBusinessRules(array $data): void
    {
        // Validar que pelo menos um tipo deve ser selecionado
        if (!($data['is_pagante'] ?? false) && !($data['is_fornecedor'] ?? false)) {
            throw ValidationException::withMessages([
                'is_pagante' => 'Selecione pelo menos um tipo (Pagante ou Fornecedor).'
            ]);
        }
    }
}

