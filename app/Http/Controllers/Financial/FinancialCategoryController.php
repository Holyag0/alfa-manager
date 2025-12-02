<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\FinancialCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class FinancialCategoryController extends Controller
{
    /**
     * Listar categorias
     */
    public function index()
    {
        $categories = FinancialCategory::withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Financial/Categories/Index', [
            'categories' => $categories,
        ]);
    }

    /**
     * Criar categoria
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:receita,despesa',
                'description' => 'nullable|string|max:500',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
                'is_active' => 'boolean',
            ]);

            FinancialCategory::create($validated);

            return redirect()->route('financial.categories.index')
                ->with('success', 'Categoria criada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar categoria financeira: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao criar categoria.'])
                ->withInput();
        }
    }

    /**
     * Atualizar categoria
     */
    public function update(Request $request, $id)
    {
        try {
            $category = FinancialCategory::findOrFail($id);

            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'type' => 'required|in:receita,despesa',
                'description' => 'nullable|string|max:500',
                'color' => 'nullable|string|max:7|regex:/^#[0-9A-F]{6}$/i',
                'is_active' => 'boolean',
            ]);

            $category->update($validated);

            return redirect()->route('financial.categories.index')
                ->with('success', 'Categoria atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar categoria financeira: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar categoria.'])
                ->withInput();
        }
    }

    /**
     * Excluir categoria
     */
    public function destroy($id)
    {
        try {
            $category = FinancialCategory::findOrFail($id);

            if ($category->transactions()->exists()) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir uma categoria que possui transações.');
            }

            $category->delete();

            return redirect()->route('financial.categories.index')
                ->with('success', 'Categoria excluída com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir categoria financeira: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao excluir categoria.');
        }
    }
}

