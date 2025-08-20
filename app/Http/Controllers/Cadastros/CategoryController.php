<?php

namespace App\Http\Controllers\Cadastros;

use App\Http\Controllers\Controller;
use App\Services\ServiceCategory;
use App\Http\Requests\CategoryRequest;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    private ServiceCategory $categoryService;

    public function __construct(ServiceCategory $categoryService)
    {
        $this->categoryService = $categoryService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $filters = request()->only(['name', 'type', 'is_active']);
            $categories = $this->categoryService->search($filters);
            $types = $this->categoryService->getTypes();

            return Inertia::render('Cadastros/Categories/Index', [
                'categories' => $categories,
                'types' => $types,
                'filters' => $filters
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar categorias: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar lista de categorias.');
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        try {
            $types = $this->categoryService->getTypes();

            return Inertia::render('Cadastros/Categories/Create', [
                'types' => $types
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de criação: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CategoryRequest $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name',
                'description' => 'nullable|string|max:500',
                'type' => 'required|in:service,package,both',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
                'is_active' => 'boolean'
            ]);

            $this->categoryService->create($validated);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar categoria: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar categoria. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $category = $this->categoryService->find($id);

            if (!$category) {
                return redirect()->route('categories.index')
                    ->with('error', 'Categoria não encontrada.');
            }

            return Inertia::render('Cadastros/Categories/Show', [
                'category' => $category
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir categoria: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar categoria.');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $category = $this->categoryService->find($id);
            $types = $this->categoryService->getTypes();

            if (!$category) {
                return redirect()->route('categories.index')
                    ->with('error', 'Categoria não encontrada.');
            }

            return Inertia::render('Cadastros/Categories/Edit', [
                'category' => $category,
                'types' => $types
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar formulário.');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CategoryRequest $request, $id)
    {
        try {
            $category = $this->categoryService->find($id);

            if (!$category) {
                return redirect()->route('categories.index')
                    ->with('error', 'Categoria não encontrada.');
            }

            $validated = $request->validate([
                'name' => 'required|string|max:255|unique:categories,name,' . $id,
                'description' => 'nullable|string|max:500',
                'type' => 'required|in:service,package,both',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
                'is_active' => 'boolean'
            ]);

            $this->categoryService->update($id, $validated);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar categoria: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar categoria. Tente novamente.'])
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $category = $this->categoryService->find($id);

            if (!$category) {
                return redirect()->route('categories.index')
                    ->with('error', 'Categoria não encontrada.');
            }

            $this->categoryService->delete($category);

            return redirect()->route('categories.index')
                ->with('success', 'Categoria excluída com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir categoria: ' . $e->getMessage());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Alternar status da categoria
     */
    public function toggleStatus($id)
    {
        try {
            $category = $this->categoryService->find($id);

            if (!$category) {
                return redirect()->route('categories.index')
                    ->with('error', 'Categoria não encontrada.');
            }

            $this->categoryService->toggleStatus($category);

            return redirect()->back()
                ->with('success', 'Status da categoria alterado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao alternar status da categoria: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao alterar status da categoria.');
        }
    }
}
