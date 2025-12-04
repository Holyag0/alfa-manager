<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Services\SupplierService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    protected SupplierService $supplierService;

    public function __construct(SupplierService $supplierService)
    {
        $this->supplierService = $supplierService;
    }

    /**
     * Listar fornecedores/pagantes
     */
    public function index(Request $request)
    {
        try {
            $filters = $request->only(['is_pagante', 'is_fornecedor', 'active', 'search', 'sort_by', 'sort_order']);
            $suppliers = $this->supplierService->search($filters, 15);

            return Inertia::render('Financial/Suppliers/Index', [
                'suppliers' => $suppliers,
                'filters' => $filters,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar fornecedores/pagantes: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar fornecedores/pagantes.');
        }
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        return Inertia::render('Financial/Suppliers/Create');
    }

    /**
     * Criar fornecedor/pagante
     */
    public function store(Request $request)
    {
        try {
            // Validação de dados
            $rules = [
                'is_pagante' => 'required|boolean',
                'is_fornecedor' => 'required|boolean',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'active' => 'nullable|boolean',
            ];

            // Validar documento apenas se não estiver vazio, considerando soft-deleted
            if (!empty($request->document)) {
                $rules['document'] = [
                    'string',
                    'max:20',
                    Rule::unique('suppliers', 'document')->whereNull('deleted_at')
                ];
            } else {
                $rules['document'] = 'nullable|string|max:20';
            }

            $validated = $request->validate($rules);

            // Criar usando o service (lógica de negócio está no service)
            $supplier = $this->supplierService->create($validated);

            return redirect()->route('financial.suppliers.index')
                ->with('success', 'Fornecedor/Pagante registrado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            
            Log::error('Erro de validação ao criar fornecedor/pagante: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {

            Log::error('Erro ao criar fornecedor/pagante: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao registrar fornecedor/pagante: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Exibir detalhes
     */
    public function show($id)
    {
        try {
            // Verificar se é requisição Inertia (redirecionamento após update/create)
            $isInertiaRequest = request()->header('X-Inertia') || request()->header('X-Inertia-Version');
            
            // Se for requisição AJAX explícita (fetch do JavaScript) e NÃO for Inertia, retornar JSON
            if ((request()->wantsJson() || request()->ajax()) && !$isInertiaRequest) {
                $supplier = $this->supplierService->findWithTransactionCounts($id);
                return response()->json([
                    'supplier' => $supplier
                ]);
            }

            // Sempre retornar resposta Inertia para requisições normais ou Inertia
            $supplier = $this->supplierService->findWithTransactionCounts($id);
            return Inertia::render('Financial/Suppliers/Show', [
                'supplier' => $supplier,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir fornecedor/pagante: ' . $e->getMessage());
            
            $isInertiaRequest = request()->header('X-Inertia') || request()->header('X-Inertia-Version');
            
            if ((request()->wantsJson() || request()->ajax()) && !$isInertiaRequest) {
                return response()->json(['error' => 'Fornecedor/Pagante não encontrado.'], 404);
            }
            
            return redirect()->back()->with('error', 'Fornecedor/Pagante não encontrado.');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        try {
            $supplier = $this->supplierService->find($id);
            
            if (!$supplier) {
                return redirect()->back()->with('error', 'Fornecedor/Pagante não encontrado.');
            }

            return Inertia::render('Financial/Suppliers/Edit', [
                'supplier' => $supplier,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fornecedor/Pagante não encontrado.');
        }
    }

    /**
     * Atualizar fornecedor/pagante
     */
    public function update(Request $request, $id)
    {
        try {
            // Validação de dados
            $rules = [
                'is_pagante' => 'required|boolean',
                'is_fornecedor' => 'required|boolean',
                'name' => 'required|string|max:255',
                'email' => 'nullable|email|max:255',
                'phone' => 'nullable|string|max:20',
                'address' => 'nullable|string|max:500',
                'notes' => 'nullable|string|max:1000',
                'active' => 'nullable|boolean',
            ];

            // Validar documento apenas se não estiver vazio
            if (!empty($request->document)) {
                $rules['document'] = [
                    'string',
                    'max:20',
                    Rule::unique('suppliers', 'document')->ignore($id)->whereNull('deleted_at')
                ];
            } else {
                $rules['document'] = 'nullable|string|max:20';
            }

            $validated = $request->validate($rules);

            // Atualizar usando o service (lógica de negócio está no service)
            $this->supplierService->update($id, $validated);

            return redirect()->route('financial.suppliers.show', $id)
                ->with('success', 'Fornecedor/Pagante atualizado com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {

            Log::error('Erro de validação ao atualizar fornecedor/pagante: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {

            Log::error('Erro ao atualizar fornecedor/pagante: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar fornecedor/pagante: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Remover fornecedor/pagante
     */
    public function destroy($id)
    {
        try {
            // Excluir usando o service (lógica de negócio está no service)
            $this->supplierService->delete($id);

            return redirect()->route('financial.suppliers.index')
                ->with('success', 'Fornecedor/Pagante removido com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao remover fornecedor/pagante: ' . $e->getMessage());
            
            // Se for erro de regra de negócio, retornar mensagem específica
            if (str_contains($e->getMessage(), 'transações vinculadas')) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir fornecedor/pagante com transações vinculadas.');
            }
            
            return redirect()->back()->with('error', 'Erro ao remover fornecedor/pagante.');
        }
    }

    /**
     * API: Listar fornecedores/pagantes (para selects)
     */
    public function api(Request $request)
    {
        try {
            $filters = $request->only(['is_pagante', 'is_fornecedor', 'search']);
            $suppliers = $this->supplierService->listActive($filters);

            return response()->json($suppliers);
        } catch (\Exception $e) {

            Log::error('Erro ao buscar fornecedores/pagantes via API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar fornecedores/pagantes'], 500);
        }
    }
}
