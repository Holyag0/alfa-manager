<?php

namespace App\Services;

use App\Models\Package;
use App\Models\Service;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class PackageService
{
    protected Package $package;

    public function __construct(Package $package)
    {
        $this->package = $package;
    }

    /**
     * Listar todos os pacotes com paginação
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->package
            ->with('services')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Buscar pacotes por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->package->with('services');

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
     * Encontrar pacote por ID
     */
    public function find(int $id): ?Package
    {
        return $this->package->with('services')->find($id);
    }

    /**
     * Criar um novo pacote
     */
    public function create(array $data): Package
    {
        $package = $this->package->create($data);
        
        // Vincular serviços se fornecidos
        if (!empty($data['services'])) {
            $package->services()->attach($data['services']);
        }

        return $package->load('services');
    }

    /**
     * Atualizar um pacote
     */
    public function update(Package $package, array $data): Package
    {
        $package->update($data);
        
        // Atualizar serviços vinculados se fornecidos
        if (array_key_exists('services', $data)) {
            $package->services()->sync($data['services'] ?? []);
        }

        return $package->fresh(['services']);
    }

    /**
     * Excluir um pacote
     */
    public function delete(Package $package): bool
    {
        return $package->delete();
    }

    /**
     * Listar categorias disponíveis
     */
    public function getCategories(): Collection
    {
        return $this->package->distinct()
            ->pluck('category')
            ->filter()
            ->sort()
            ->values();
    }

    /**
     * Listar pacotes ativos
     */
    public function getActivePackages(): Collection
    {
        return $this->package->active()
            ->with('services')
            ->orderBy('name')
            ->get();
    }

    /**
     * Vincular serviços a um pacote
     */
    public function attachServices(Package $package, array $serviceIds): Package
    {
        $package->services()->attach($serviceIds);
        return $package->load('services');
    }

    /**
     * Desvincular serviços de um pacote
     */
    public function detachServices(Package $package, array $serviceIds): Package
    {
        $package->services()->detach($serviceIds);
        return $package->load('services');
    }

    /**
     * Sincronizar serviços de um pacote
     */
    public function syncServices(Package $package, array $serviceIds): Package
    {
        $package->services()->sync($serviceIds);
        return $package->load('services');
    }
} 