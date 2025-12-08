<?php

namespace App\Services;

use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class EmployeeService
{
    protected Employee $employee;

    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    /**
     * Listar todos os colaboradores com paginação
     */
    public function all(int $perPage = 15): LengthAwarePaginator
    {
        return $this->employee
            ->with('position')
            ->orderBy('name')
            ->paginate($perPage);
    }

    /**
     * Buscar colaboradores por filtros
     */
    public function search(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->employee->newQuery()->with('position');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', '%' . $filters['name'] . '%');
        }

        if (isset($filters['position_id']) && $filters['position_id'] !== '') {
            $query->where('position_id', $filters['position_id']);
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $query->where('is_active', $filters['is_active'] === 'true');
        }

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->orderBy('name')->paginate($perPage);
    }

    /**
     * Encontrar colaborador por ID
     */
    public function find(int $id): ?Employee
    {
        return $this->employee->with('position')->find($id);
    }

    /**
     * Criar um novo colaborador
     */
    public function create(array $data): Employee
    {
        // Processar upload de foto
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            $photoPath = $data['photo']->store('employees', 'public');
            $data['photo'] = $photoPath;
        }

        // Normalizar campo booleano
        $data['is_active'] = isset($data['is_active']) 
            ? filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN) 
            : true;

        return $this->employee->create($data);
    }

    /**
     * Atualizar um colaborador
     */
    public function update(int $id, array $data): Employee
    {
        $employee = $this->employee->findOrFail($id);

        // Processar upload de foto
        if (isset($data['photo']) && $data['photo'] instanceof \Illuminate\Http\UploadedFile) {
            // Remover foto antiga se existir
            if ($employee->photo && Storage::disk('public')->exists($employee->photo)) {
                Storage::disk('public')->delete($employee->photo);
            }
            
            $photoPath = $data['photo']->store('employees', 'public');
            $data['photo'] = $photoPath;
        }

        // Normalizar campo booleano
        if (isset($data['is_active'])) {
            $data['is_active'] = filter_var($data['is_active'], FILTER_VALIDATE_BOOLEAN);
        }

        $employee->update($data);
        return $employee->fresh();
    }

    /**
     * Excluir um colaborador
     */
    public function delete(int $id): bool
    {
        $employee = $this->employee->findOrFail($id);

        // A foto será removida automaticamente pelo boot method do Model
        return $employee->delete();
    }

    /**
     * Alternar status do colaborador
     */
    public function toggleStatus(int $id): Employee
    {
        $employee = $this->employee->findOrFail($id);
        $employee->is_active = !$employee->is_active;
        $employee->save();
        return $employee->fresh();
    }

    /**
     * Listar colaboradores ativos para API (selects)
     */
    public function listActive(array $filters = []): Collection
    {
        $query = $this->employee->active();

        if (!empty($filters['search'])) {
            $query->search($filters['search']);
        }

        return $query->orderBy('name')->get(['id', 'name', 'position_id']);
    }
}

