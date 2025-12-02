<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\Supplier;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class SupplierController extends Controller
{
    /**
     * Listar fornecedores/pagantes
     */
    public function index(Request $request)
    {
        try {
            $query = Supplier::query();

            // Filtros
            if ($request->filled('is_pagante')) {
                $query->where('is_pagante', $request->is_pagante === 'true');
            }
            if ($request->filled('is_fornecedor')) {
                $query->where('is_fornecedor', $request->is_fornecedor === 'true');
            }

            if ($request->filled('active')) {
                $query->where('active', $request->active === 'true');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('document', 'like', '%' . $search . '%')
                      ->orWhere('email', 'like', '%' . $search . '%');
                });
            }

            // Ordenação - Whitelist de colunas permitidas para prevenir SQL injection
            $allowedSortColumns = [
                'id',
                'name',
                'document',
                'email',
                'phone',
                'is_pagante',
                'is_fornecedor',
                'active',
                'created_at',
                'updated_at',
            ];
            $sortBy = $request->get('sort_by', 'name');
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'name';
            
            $allowedSortOrders = ['asc', 'desc'];
            $sortOrder = $request->get('sort_order', 'asc');
            $sortOrder = in_array(strtolower($sortOrder), $allowedSortOrders) ? strtolower($sortOrder) : 'asc';
            
            $query->orderBy($sortBy, $sortOrder);

            // Paginação - Validar e limitar per_page para prevenir DoS
            $perPage = $request->get('per_page', 15);
            $perPage = max(1, min(100, (int) $perPage)); // Limitar entre 1 e 100
            $suppliers = $query->paginate($perPage);

            return Inertia::render('Financial/Suppliers/Index', [
                'suppliers' => $suppliers,
                'filters' => $request->only(['is_pagante', 'is_fornecedor', 'active', 'search']),
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
            // Converter valores booleanos antes da validação
            $data = $request->all();
            $data['is_pagante'] = filter_var($data['is_pagante'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['is_fornecedor'] = filter_var($data['is_fornecedor'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['active'] = isset($data['active']) ? filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) : true;

            // Criar uma nova request com os valores convertidos
            $request->merge($data);

            // Preparar regras de validação
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

            // Validar que pelo menos um tipo deve ser selecionado
            if (!$validated['is_pagante'] && !$validated['is_fornecedor']) {
                return redirect()->back()
                    ->withErrors(['is_pagante' => 'Selecione pelo menos um tipo (Pagante ou Fornecedor).'])
                    ->withInput();
            }

            $supplier = Supplier::create($validated);

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
            $supplier = Supplier::withCount([
                'transactionsAsPayer',
                'transactionsAsPayee'
            ])->findOrFail($id);

            return Inertia::render('Financial/Suppliers/Show', [
                'supplier' => $supplier,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir fornecedor/pagante: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Fornecedor/Pagante não encontrado.');
        }
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        try {
            $supplier = Supplier::findOrFail($id);

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
            $supplier = Supplier::findOrFail($id);

            // Converter valores booleanos antes da validação
            $data = $request->all();
            $data['is_pagante'] = filter_var($data['is_pagante'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['is_fornecedor'] = filter_var($data['is_fornecedor'] ?? false, FILTER_VALIDATE_BOOLEAN);
            $data['active'] = isset($data['active']) ? filter_var($data['active'], FILTER_VALIDATE_BOOLEAN) : true;

            // Criar uma nova request com os valores convertidos
            $request->merge($data);

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

            // Validar que pelo menos um tipo deve ser selecionado
            if (!$validated['is_pagante'] && !$validated['is_fornecedor']) {
                return redirect()->back()
                    ->withErrors(['is_pagante' => 'Selecione pelo menos um tipo (Pagante ou Fornecedor).'])
                    ->withInput();
            }

            $supplier->update($validated);

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
            $supplier = Supplier::findOrFail($id);

            // Verificar se há transações vinculadas
            $hasTransactions = $supplier->transactionsAsPayer()->exists() 
                || $supplier->transactionsAsPayee()->exists();

            if ($hasTransactions) {
                return redirect()->back()
                    ->with('error', 'Não é possível excluir fornecedor/pagante com transações vinculadas.');
            }

            $supplier->delete();

            return redirect()->route('financial.suppliers.index')
                ->with('success', 'Fornecedor/Pagante removido com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao remover fornecedor/pagante: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao remover fornecedor/pagante.');
        }
    }

    /**
     * API: Listar fornecedores/pagantes (para selects)
     */
    public function api(Request $request)
    {
        try {
            $query = Supplier::active();

            if ($request->filled('is_pagante')) {
                $query->where('is_pagante', $request->is_pagante === 'true');
            }
            if ($request->filled('is_fornecedor')) {
                $query->where('is_fornecedor', $request->is_fornecedor === 'true');
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', '%' . $search . '%')
                      ->orWhere('document', 'like', '%' . $search . '%');
                });
            }

            $suppliers = $query->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);

            return response()->json($suppliers);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar fornecedores/pagantes via API: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao buscar fornecedores/pagantes'], 500);
        }
    }
}
