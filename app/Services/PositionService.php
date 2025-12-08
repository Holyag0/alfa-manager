<?php

namespace App\Services;

use App\Models\Position;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PositionService
{
    protected Position $position;

    public function __construct(Position $position)
    {
        $this->position = $position;
    }

    /**
     * Listar todos os cargos com paginação
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->position
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Buscar cargos por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->position->newQuery();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active'] === 'true');
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Encontrar cargo por ID
     */
    public function find(int $id): ?Position
    {
        return $this->position->find($id);
    }

    /**
     * Criar um novo cargo
     */
    public function create(array $data): Position
    {
        // Normalizar campo booleano
        $data['is_active'] = isset($data['is_active']) 
            ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) 
            : true;

        return $this->position->create($data);
    }

    /**
     * Atualizar um cargo
     */
    public function update(int $id, array $data): Position
    {
        $position = $this->position->findOrFail($id);
        
        // Normalizar campo booleano
        if (isset($data['is_active'])) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $position->update($data);
        return $position->fresh();
    }

    /**
     * Excluir um cargo
     */
    public function delete(int $id): bool
    {
        $position = $this->position->findOrFail($id);

        // Verificar se há colaboradores vinculados (regra de negócio)
        if ($position->hasEmployees()) {
            throw new \Exception('Não é possível excluir cargo com colaboradores vinculados.');
        }

        return $position->delete();
    }

    /**
     * Alternar status do cargo
     */
    public function toggleStatus(int $id): Position
    {
        $position = $this->position->findOrFail($id);
        $position->is_active = !$position->is_active;
        $position->save();
        return $position->fresh();
    }

    /**
     * Listar cargos ativos para API (selects)
     */
    public function listActive(): Collection
    {
        return $this->position
            ->active()
            ->orderBy('name')
            ->get(['id', 'name']);
    }
}

