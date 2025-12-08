<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Services\EmployeeService;
use App\Services\PositionService;
use App\Http\Requests\EmployeeRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class EmployeeController extends Controller
{
    private EmployeeService $employeeService;
    private PositionService $positionService;

    public function __construct(EmployeeService $employeeService, PositionService $positionService)
    {
        $this->employeeService = $employeeService;
        $this->positionService = $positionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $filters = request()->only(['name', 'position_id', 'is_active', 'search']);
            $employees = $this->employeeService->search($filters);
            $positions = $this->positionService->listActive();

            return Inertia::render('Cadastros/Employees/Index', [
                'employees' => $employees,
                'positions' => $positions,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar colaboradores: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar lista de colaboradores.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $positions = $this->positionService->listActive();

            return Inertia::render('Cadastros/Employees/Create', [
                'positions' => $positions
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(EmployeeRequest $request)
    {
        try {
            $validated = $request->validated();
            $this->employeeService->create($validated);

            return redirect()->route('cadastros.employees.index')
                ->with('success', 'Colaborador criado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao criar colaborador: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar colaborador: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar colaborador. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $employee = $this->employeeService->find($id);

            if (!$employee) {
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'Colaborador não encontrado.'], 404);
                }
                return redirect()->route('cadastros.employees.index')
                    ->with('error', 'Colaborador não encontrado.');
            }

            // Se for requisição AJAX, retorna JSON
            if (request()->expectsJson()) {
                return response()->json([
                    'employee' => $employee->load('position')
                ]);
            }

            return Inertia::render('Cadastros/Employees/Show', [
                'employee' => $employee
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir colaborador: ' . $e->getMessage());
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Erro ao carregar colaborador.'], 500);
            }
            return redirect()->back()->with('error', 'Erro ao carregar colaborador.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $employee = $this->employeeService->find($id);
            $positions = $this->positionService->listActive();

            if (!$employee) {
                return redirect()->route('cadastros.employees.index')
                    ->with('error', 'Colaborador não encontrado.');
            }

            return Inertia::render('Cadastros/Employees/Edit', [
                'employee' => $employee,
                'positions' => $positions
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(EmployeeRequest $request, $id)
    {
        try {
            $employee = $this->employeeService->find($id);

            if (!$employee) {
                return redirect()->route('cadastros.employees.index')
                    ->with('error', 'Colaborador não encontrado.');
            }

            $validated = $request->validated();
            $this->employeeService->update($id, $validated);

            return redirect()->route('cadastros.employees.index')
                ->with('success', 'Colaborador atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao atualizar colaborador: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar colaborador: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar colaborador. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $employee = $this->employeeService->find($id);

            if (!$employee) {
                return redirect()->route('cadastros.employees.index')
                    ->with('error', 'Colaborador não encontrado.');
            }

            $this->employeeService->delete($id);

            return redirect()->route('cadastros.employees.index')
                ->with('success', 'Colaborador excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir colaborador: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir colaborador.');
        }
    }

    /**
     * Alternar status do colaborador
     */
    public function toggleStatus($id)
    {
        try {
            $employee = $this->employeeService->find($id);

            if (!$employee) {
                return redirect()->route('cadastros.employees.index')
                    ->with('error', 'Colaborador não encontrado.');
            }

            $this->employeeService->toggleStatus($id);

            return redirect()->back()
                ->with('success', 'Status do colaborador alterado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status do colaborador: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao alterar status do colaborador.');
        }
    }

    /**
     * API: Listar colaboradores ativos (para selects)
     */
    public function api()
    {
        try {
            $filters = request()->only(['search']);
            $employees = $this->employeeService->listActive($filters);
            return response()->json($employees);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar colaboradores via API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar colaboradores'], 500);
        }
    }
}
