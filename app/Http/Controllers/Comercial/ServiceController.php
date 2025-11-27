<?php

namespace App\Http\Controllers\Comercial;

use App\Http\Controllers\Controller;
use App\Services\ServiceService;
use App\Http\Requests\ServiceRequest;
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
    public function index()
    {
        try {
            $filters = request()->only(['name', 'category', 'status']);
            $services = $this->serviceService->search($filters);
            $categories = $this->serviceService->getCategoryNames();

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
            $classrooms = \App\Models\Classroom::active()->orderBy('name')->get();

            return Inertia::render('Comercial/Services/Create', [
                'categories' => $categories,
                'classrooms' => $classrooms
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Mapear categoria para tipo de serviço automaticamente
     */
    private function mapCategoryToType(string $category): string
    {
        $mapping = [
            'Mensalidade' => 'monthly',
            'Reserva' => 'reservation',
            'Matrícula' => 'enrollment',
        ];
        
        return $mapping[$category] ?? 'service';
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ServiceRequest $request)
    {
        try {
            $validatedData = $request->validated();
            
            // Remover selected_classrooms dos dados do serviço
            $selectedClassrooms = $validatedData['selected_classrooms'] ?? [];
            unset($validatedData['selected_classrooms']);
            
            // Mapear categoria para tipo automaticamente se não fornecido
            if (!isset($validatedData['type']) && isset($validatedData['category'])) {
                $validatedData['type'] = $this->mapCategoryToType($validatedData['category']);
            }
            
            // Criar o serviço
            $service = $this->serviceService->create($validatedData);
            
            // Se vinculado a turmas, criar ClassroomServices
            if ($request->is_classroom_linked && !empty($selectedClassrooms)) {
                foreach ($selectedClassrooms as $classroomId) {
                    $classroom = \App\Models\Classroom::find($classroomId);
                    if ($classroom) {
                        \App\Models\ClassroomService::create([
                            'classroom_id' => $classroomId,
                            'service_id' => $service->id,
                            'price' => $service->price, // Preço base
                            'description' => $service->name . ' - ' . $classroom->name,
                            'is_active' => true
                        ]);
                    }
                }
            }

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

            // Carregar turmas vinculadas se o serviço for vinculado
            $serviceData = $service->toArray();
            
            if ($service->isClassroomLinked()) {
                $classroomServices = $service->classroomServices()
                    ->with('classroom')
                    ->get()
                    ->map(function ($cs) {
                        return [
                            'id' => $cs->id,
                            'classroom' => [
                                'id' => $cs->classroom->id,
                                'name' => $cs->classroom->name
                            ],
                            'price' => $cs->price,
                            'formatted_price' => $cs->formatted_price,
                            'description' => $cs->description,
                            'full_description' => $cs->full_description,
                            'is_active' => $cs->is_active
                        ];
                    });
                
                $serviceData['classroom_services'] = $classroomServices;
            }

            return Inertia::render('Comercial/Services/Show', [
                'service' => $serviceData
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
            $classrooms = \App\Models\Classroom::active()->orderBy('name')->get();

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            // Carregar turmas selecionadas para este serviço
            $selectedClassrooms = $service->classroomServices()->pluck('classroom_id')->toArray();

            return Inertia::render('Comercial/Services/Edit', [
                'service' => array_merge($service->toArray(), [
                    'selected_classrooms' => $selectedClassrooms
                ]),
                'categories' => $categories,
                'classrooms' => $classrooms
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ServiceRequest $request, $id)
    {
        try {
            $service = $this->serviceService->find($id);

            if (!$service) {
                return redirect()->route('services.index')
                    ->with('error', 'Serviço não encontrado.');
            }

            $validatedData = $request->validated();
            
            // Remover selected_classrooms dos dados do serviço
            $selectedClassrooms = $validatedData['selected_classrooms'] ?? [];
            unset($validatedData['selected_classrooms']);
            
            // Mapear categoria para tipo automaticamente se não fornecido
            if (!isset($validatedData['type']) && isset($validatedData['category'])) {
                $validatedData['type'] = $this->mapCategoryToType($validatedData['category']);
            }
            
            // Atualizar o serviço
            $this->serviceService->update($id, $validatedData);
            
            // Atualizar vinculações com turmas
            if ($request->is_classroom_linked) {
                // Remover vinculações existentes
                $service->classroomServices()->delete();
                
                // Criar novas vinculações
                if (!empty($selectedClassrooms)) {
                    foreach ($selectedClassrooms as $classroomId) {
                        $classroom = \App\Models\Classroom::find($classroomId);
                        if ($classroom) {
                            \App\Models\ClassroomService::create([
                                'classroom_id' => $classroomId,
                                'service_id' => $service->id,
                                'price' => $service->price, // Preço base
                                'description' => $service->name . ' - ' . $classroom->name,
                                'is_active' => true
                            ]);
                        }
                    }
                }
            } else {
                // Se não é vinculado, remover todas as vinculações
                $service->classroomServices()->delete();
            }

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
            $this->serviceService->update($service->id, ['status' => $newStatus]);

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

    /**
     * Get services list for API
     */
    public function getServicesApi()
    {
        try {
            $services = $this->serviceService->getActiveServices();
            return response()->json($services);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar serviços: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar serviços'], 500);
        }
    }
}
