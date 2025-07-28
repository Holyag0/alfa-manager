<?php

namespace App\Services;

use App\Models\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ServiceService
{
    protected Service $service;

    public function __construct(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Listar todos os serviços com paginação
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->service
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Buscar serviços por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->service->newQuery();

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (!empty($filters['category'])) {
            $query->where('category', $filters['category']);
        }

        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Encontrar serviço por ID
     */
    public function find(int $id): ?Service
    {
        return $this->service->find($id);
    }

    /**
     * Criar um novo serviço
     */
    public function create(array $data): Service
    {
        return $this->service->create($data);
    }

    /**
     * Atualizar um serviço
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);
        return $service->fresh();
    }

    /**
     * Excluir um serviço
     */
    public function delete(Service $service): bool
    {
        return $service->delete();
    }

    /**
     * Listar categorias disponíveis
     */
    public function getCategories(): Collection
    {
        return $this->service->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Listar serviços ativos para seleção
     */
    public function getActiveServices(): Collection
    {
        return $this->service->active()
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category']);
    }

    /**
     * Serviços não vinculados a um pacote específico
     */
    public function getNotLinkedToPackage(int $packageId): Collection
    {
        return $this->service->active()
            ->whereDoesntHave('packages', function ($query) use ($packageId) {
                $query->where('package_id', $packageId);
            })
            ->orderBy('name')
            ->get(['id', 'name', 'price', 'category']);
    }
} 