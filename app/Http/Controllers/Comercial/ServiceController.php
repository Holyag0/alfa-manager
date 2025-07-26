<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class ServiceController extends Controller
{
    private ServiceService $serviceService;

    public function __construct(ServiceService $serviceService)
    {
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['name', 'category', 'status']);
            $services = $this->serviceService->search($filters);
            $categories = $this->serviceService->getCategories();

            return Inertia::render('Comercial/Services/Index', [
                'services' => $services,
                'categories' => $categories,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar serviços: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar lista de serviços.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $categories = $this->serviceService->getCategories();

            return Inertia::render('Comercial/Services/Create', [
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'description' => 'nullable|string'
            ]);

            $this->serviceService->create($validated);

            return redirect()->route('services.index')
                ->with('success', 'Serviço criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar serviço: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar serviço. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $service = $this->serviceService->find($id);

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            return Inertia::render('Comercial/Services/Show', [
                'service' => $service
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir serviço: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar serviço.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $service = $this->serviceService->find($id);
            $categories = $this->serviceService->getCategories();

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            return Inertia::render('Comercial/Services/Edit', [
                'service' => $service,
                'categories' => $categories
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        try {
            $service = $this->serviceService->find($id);

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'description' => 'nullable|string'
            ]);

            $this->serviceService->update($service, $validated);

            return redirect()->route('services.index')
                ->with('success', 'Serviço atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar serviço: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar serviço. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $service = $this->serviceService->find($id);

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            $this->serviceService->delete($service);

            return redirect()->route('services.index')
                ->with('success', 'Serviço excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir serviço: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir serviço.');
        }
    }

    /**
     * Toggle service status
     */
    public function toggleStatus($id)
    {
        try {
            $service = $this->serviceService->find($id);

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            $newStatus = $service->status === 'active' ? 'inactive' : 'active';
            $this->serviceService->update($service, ['status' => $newStatus]);

            return redirect()->back()
                ->with('success', 'Status do serviço alterado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao alterar status do serviço: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao alterar status do serviço.');
        }
    }

    /**
     * Get categories list for API
     */
    public function getCategories()
    {
        try {
            $categories = $this->serviceService->getCategories();
            return response()->json($categories);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar categorias'], 500);
        }
    }
}
