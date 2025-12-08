<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Services\PositionService;
use App\Http\Requests\PositionRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PositionController extends Controller
{
    private PositionService $positionService;

    public function __construct(PositionService $positionService)
    {
        $this->positionService = $positionService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $filters = request()->only(['name', 'is_active']);
            $positions = $this->positionService->search($filters);

            return Inertia::render('Cadastros/Positions/Index', [
                'positions' => $positions,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar cargos: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar lista de cargos.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            return Inertia::render('Cadastros/Positions/Create');
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PositionRequest $request)
    {
        try {
            $validated = $request->validated();
            $this->positionService->create($validated);

            return redirect()->route('cadastros.positions.index')
                ->with('success', 'Cargo criado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao criar cargo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar cargo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar cargo. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $position = $this->positionService->find($id);

            if (!$position) {
                return redirect()->route('cadastros.positions.index')
                    ->with('error', 'Cargo não encontrado.');
            }

            return Inertia::render('Cadastros/Positions/Show', [
                'position' => $position
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir cargo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar cargo.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $position = $this->positionService->find($id);

            if (!$position) {
                return redirect()->route('cadastros.positions.index')
                    ->with('error', 'Cargo não encontrado.');
            }

            return Inertia::render('Cadastros/Positions/Edit', [
                'position' => $position
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PositionRequest $request, $id)
    {
        try {
            $position = $this->positionService->find($id);

            if (!$position) {
                return redirect()->route('cadastros.positions.index')
                    ->with('error', 'Cargo não encontrado.');
            }

            $validated = $request->validated();
            $this->positionService->update($id, $validated);

            return redirect()->route('cadastros.positions.index')
                ->with('success', 'Cargo atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Erro de validação ao atualizar cargo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar cargo: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar cargo. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $position = $this->positionService->find($id);

            if (!$position) {
                return redirect()->route('cadastros.positions.index')
                    ->with('error', 'Cargo não encontrado.');
            }

            $this->positionService->delete($id);

            return redirect()->route('cadastros.positions.index')
                ->with('success', 'Cargo excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir cargo: ' . $e->getMessage());
            
            // Se for erro de regra de negócio, retornar mensagem específica
            if (str_contains($e->getMessage(), 'colaboradores vinculados')) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir cargo com colaboradores vinculados.');
            }
            
            return redirect()->back()->with('error', 'Erro ao excluir cargo.');
        }
    }

    /**
     * Alternar status do cargo
     */
    public function toggleStatus($id)
    {
        try {
            $position = $this->positionService->find($id);

            if (!$position) {
                return redirect()->route('cadastros.positions.index')
                    ->with('error', 'Cargo não encontrado.');
            }

            $this->positionService->toggleStatus($id);

            return redirect()->back()
                ->with('success', 'Status do cargo alterado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status do cargo: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao alterar status do cargo.');
        }
    }

    /**
     * API: Listar cargos ativos (para selects)
     */
    public function api()
    {
        try {
            $positions = $this->positionService->listActive();
            return response()->json($positions);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar cargos via API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar cargos'], 500);
        }
    }
}
