<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ServiceCategory
{
    protected Category $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Listar todas as categorias com paginação
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->category
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Buscar categorias por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->category->newQuery();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }

        if (isset($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Encontrar categoria por ID
     */
    public function find(int $id): ?Category
    {
        return $this->category->find($id);
    }

    /**
     * Criar uma nova categoria
     */
    public function create(array $data): Category
    {
        return $this->category->create($data);
    }

    /**
     * Atualizar uma categoria
     */
    public function update(int $id, array $data): Category
    {
        $category = $this->category->findOrFail($id);
        $category->update($data);
        return $category->fresh();
    }

    /**
     * Excluir uma categoria
     */
    public function delete(Category $category): bool
    {
        // Verificar se há serviços ou pacotes usando esta categoria
        $hasServices = $category->services()->exists();
        $hasPackages = $category->packages()->exists();

        if ($hasServices || $hasPackages) {
            throw new \Exception('Não é possível excluir uma categoria que está sendo usada por serviços ou pacotes.');
        }

        return $category->delete();
    }

    /**
     * Listar tipos disponíveis
     */
    public function getTypes(): Collection
    {
        return collect([
            'service' => 'Apenas Serviços',
            'package' => 'Apenas Pacotes',
            'both' => 'Serviços e Pacotes'
        ]);
    }

    /**
     * Listar categorias ativas para serviços
     */
    public function getActiveForServices(): Collection
    {
        return $this->category->active()->forServices()->orderBy('name')->get();
    }

    /**
     * Listar categorias ativas para pacotes
     */
    public function getActiveForPackages(): Collection
    {
        return $this->category->active()->forPackages()->orderBy('name')->get();
    }

    /**
     * Listar todas as categorias ativas
     */
    public function getAllActive(): Collection
    {
        return $this->category->active()->orderBy('name')->get();
    }

    /**
     * Alternar status de uma categoria
     */
    public function toggleStatus(Category $category): Category
    {
        $category->update(['is_active' => !$category->is_active]);
        return $category->fresh();
    }

    /**
     * Verificar se o nome da categoria já existe
     */
    public function nameExists(string $name, ?int $excludeId = null): bool
    {
        $query = $this->category->where('name', $name);
        
        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->exists();
    }
}
