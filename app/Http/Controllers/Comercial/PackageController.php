<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Services\PackageService;
use App\Services\ServiceService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PackageController extends Controller
{
    private PackageService $packageService;
    private ServiceService $serviceService;

    public function __construct(PackageService $packageService, ServiceService $serviceService)
    {
        $this->packageService = $packageService;
        $this->serviceService = $serviceService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['name', 'category', 'status']);
            $packages = $this->packageService->search($filters);
            $categories = $this->packageService->getCategories();

            return Inertia::render('Comercial/Packages/Index', [
                'packages' => $packages,
                'categories' => $categories,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar pacotes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar lista de pacotes.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $categories = $this->packageService->getCategories();
            $services = $this->serviceService->getActiveServices();

            return Inertia::render('Comercial/Packages/Create', [
                'categories' => $categories,
                'services' => $services
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
                'description' => 'nullable|string',
                'services' => 'nullable|array',
                'services.*' => 'exists:services,id'
            ]);

            $this->packageService->create($validated);

            return redirect()->route('packages.index')
                ->with('success', 'Pacote criado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar pacote: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar pacote. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $package = $this->packageService->find($id);

            if (!$package) {
                return redirect()->route('packages.index')
                    ->with('error', 'Pacote não encontrado.');
            }

            return Inertia::render('Comercial/Packages/Show', [
                'package' => $package
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir pacote: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar pacote.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $package = $this->packageService->find($id);
            $categories = $this->packageService->getCategories();
            $services = $this->serviceService->getActiveServices();

            if (!$package) {
                return redirect()->route('packages.index')
                    ->with('error', 'Pacote não encontrado.');
            }

            return Inertia::render('Comercial/Packages/Edit', [
                'package' => $package,
                'categories' => $categories,
                'services' => $services
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
            $package = $this->packageService->find($id);

            if (!$package) {
                return redirect()->route('packages.index')
                    ->with('error', 'Pacote não encontrado.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'price' => 'required|numeric|min:0',
                'category' => 'required|string|max:255',
                'status' => 'required|in:active,inactive',
                'description' => 'nullable|string',
                'services' => 'nullable|array',
                'services.*' => 'exists:services,id'
            ]);

            $this->packageService->update($package, $validated);

            return redirect()->route('packages.index')
                ->with('success', 'Pacote atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar pacote: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar pacote. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $package = $this->packageService->find($id);

            if (!$package) {
                return redirect()->route('packages.index')
                    ->with('error', 'Pacote não encontrado.');
            }

            $this->packageService->delete($package);

            return redirect()->route('packages.index')
                ->with('success', 'Pacote excluído com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir pacote: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir pacote.');
        }
    }

    /**
     * Toggle package status
     */
    public function toggleStatus($id)
    {
        try {
            $package = $this->packageService->find($id);

            if (!$package) {
                return redirect()->route('packages.index')
                    ->with('error', 'Pacote não encontrado.');
            }

            $newStatus = $package->status === 'active' ? 'inactive' : 'active';
            $this->packageService->update($package, ['status' => $newStatus]);

            return redirect()->back()
                ->with('success', 'Status do pacote alterado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao alterar status do pacote: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao alterar status do pacote.');
        }
    }

    /**
     * Get package services
     */
    public function getPackageServices($id)
    {
        try {
            $package = $this->packageService->find($id);

            if (!$package) {
                return response()->json(['error' => 'Pacote não encontrado'], 404);
            }

            return response()->json($package->services);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar serviços do pacote: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar serviços'], 500);
        }
    }

    /**
     * Add service to package
     */
    public function addServiceToPackage($packageId, $serviceId)
    {
        try {
            $package = $this->packageService->find($packageId);
            $service = $this->serviceService->find($serviceId);

            if (!$package || !$service) {
                return response()->json(['error' => 'Pacote ou serviço não encontrado'], 404);
            }

            $this->packageService->attachServices($package, [$serviceId]);

            return response()->json(['message' => 'Serviço adicionado ao pacote com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao adicionar serviço ao pacote: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao adicionar serviço'], 500);
        }
    }

    /**
     * Remove service from package
     */
    public function removeServiceFromPackage($packageId, $serviceId)
    {
        try {
            $package = $this->packageService->find($packageId);

            if (!$package) {
                return response()->json(['error' => 'Pacote não encontrado'], 404);
            }

            $this->packageService->detachServices($package, [$serviceId]);

            return response()->json(['message' => 'Serviço removido do pacote com sucesso']);
        } catch (\Exception $e) {
            Log::error('Erro ao remover serviço do pacote: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao remover serviço'], 500);
        }
    }

    /**
     * Get categories list for API
     */
    public function getCategories()
    {
        try {
            $categories = $this->packageService->getCategories();
            return response()->json($categories);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar categorias: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar categorias'], 500);
        }
    }
}
