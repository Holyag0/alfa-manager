<?php

namespace App\Http\Controllers\Financial;

use App\Http\Controllers\Controller;
use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use App\Models\Supplier;
use App\Services\FinancialService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class FinancialTransactionController extends Controller
{
    protected FinancialService $financialService;

    public function __construct(FinancialService $financialService)
    {
        $this->financialService = $financialService;
    }

    /**
     * Listar transações
     */
    public function index(Request $request)
    {
        try {
            // Eager loading seletivo - apenas campos essenciais para listagem
            $query = FinancialTransaction::select([
                'id',
                'type',
                'category_id',
                'amount',
                'transaction_date',
                'status',
                'source_type', // Para saber se é rastreada
                'description', // Para busca
                'transaction_number', // Para busca
                'reference', // Para busca
                'payer_name', // Para busca
                'payee_name', // Para busca
            ])->with([
                'category:id,name,color', // Apenas campos necessários
            ]);

            // Filtros
            if ($request->filled('type')) {
                $query->where('type', $request->type);
            }

            if ($request->filled('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            // Filtro de data - funciona com apenas uma data ou ambas
            if ($request->filled('start_date') && $request->filled('end_date')) {
                $query->byPeriod($request->start_date, $request->end_date);
            } elseif ($request->filled('start_date')) {
                $query->whereDate('transaction_date', '>=', $request->start_date);
            } elseif ($request->filled('end_date')) {
                $query->whereDate('transaction_date', '<=', $request->end_date);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('description', 'like', '%' . $search . '%')
                      ->orWhere('transaction_number', 'like', '%' . $search . '%')
                      ->orWhere('reference', 'like', '%' . $search . '%')
                      ->orWhere('payer_name', 'like', '%' . $search . '%')
                      ->orWhere('payee_name', 'like', '%' . $search . '%');
                });
            }

            // Ordenação - Whitelist de colunas permitidas para prevenir SQL injection
            $allowedSortColumns = [
                'id',
                'transaction_date',
                'amount',
                'type',
                'status',
                'description',
                'transaction_number',
                'created_at',
                'updated_at',
            ];
            $sortBy = $request->get('sort_by', 'transaction_date');
            $sortBy = in_array($sortBy, $allowedSortColumns) ? $sortBy : 'transaction_date';
            
            $allowedSortOrders = ['asc', 'desc'];
            $sortOrder = $request->get('sort_order', 'desc');
            $sortOrder = in_array(strtolower($sortOrder), $allowedSortOrders) ? strtolower($sortOrder) : 'desc';
            
            $query->orderBy($sortBy, $sortOrder);

            // Paginação - Validar e limitar per_page para prevenir DoS
            $perPage = $request->get('per_page', 15);
            $perPage = max(1, min(100, (int) $perPage)); // Limitar entre 1 e 100
            $transactions = $query->paginate($perPage);

            $categories = FinancialCategory::active()->orderBy('name')->get();

            return Inertia::render('Financial/Transactions/Index', [
                'transactions' => $transactions,
                'categories' => $categories,
                'filters' => $request->only(['type', 'category_id', 'status', 'start_date', 'end_date', 'search']),
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar transações financeiras: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar transações.');
        }
    }

    /**
     * Formulário de criação
     */
    public function create()
    {
        $categories = FinancialCategory::active()->orderBy('type')->orderBy('name')->get();
        $pagantes = Supplier::active()->pagante()->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);
        $fornecedores = Supplier::active()->fornecedor()->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);

        return Inertia::render('Financial/Transactions/Create', [
            'categories' => $categories,
            'pagantes' => $pagantes,
            'fornecedores' => $fornecedores,
        ]);
    }

    /**
     * Criar transação (despesa manual)
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'type' => 'required|in:receita,despesa',
                'category_id' => 'required|exists:financial_categories,id',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0.01',
                'transaction_date' => 'required|date',
                'payment_method' => 'nullable|in:pix,credit_card,debit_card,cash,bank_transfer,check,other',
                'reference' => 'nullable|string|max:255',
                'notes' => 'nullable|string|max:1000',
                'status' => 'nullable|in:pending,confirmed',
                'payer_name' => 'nullable|string|max:255',
                'payer_document' => 'nullable|string|max:20',
                'payee_name' => 'nullable|string|max:255',
                'payee_document' => 'nullable|string|max:20',
                'payer_supplier_id' => 'nullable|exists:suppliers,id',
                'payee_supplier_id' => 'nullable|exists:suppliers,id',
            ]);

            if ($validated['type'] === 'despesa') {
                $transaction = $this->financialService->registerExpense($validated);
            } else {
                // Receitas manuais também podem ser registradas
                $transaction = DB::transaction(function () use ($this, $validated) {
                    return FinancialTransaction::create([
                        'transaction_number' => $this->financialService->generateTransactionNumber('REC'),
                        'type' => 'receita',
                        'category_id' => $validated['category_id'],
                        'description' => $validated['description'],
                        'amount' => $validated['amount'],
                        'transaction_date' => $validated['transaction_date'],
                        'payment_method' => $validated['payment_method'] ?? null,
                        'reference' => $validated['reference'] ?? null,
                        'notes' => $validated['notes'] ?? null,
                        'status' => $validated['status'] ?? 'confirmed',
                        'confirmed_at' => ($validated['status'] ?? 'confirmed') === 'confirmed' ? now() : null,
                        'confirmed_by' => ($validated['status'] ?? 'confirmed') === 'confirmed' ? auth()->id() : null,
                        'payer_name' => $validated['payer_name'] ?? null,
                        'payer_document' => $validated['payer_document'] ?? null,
                        'payer_supplier_id' => $validated['payer_supplier_id'] ?? null,
                    ]);
                });
            }

            return redirect()->route('financial.transactions.index')
                ->with('success', 'Transação registrada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao criar transação financeira: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao registrar transação.'])
                ->withInput();
        }
    }

    /**
     * Exibir detalhes da transação
     */
    public function show($id)
    {
        try {
            $transaction = FinancialTransaction::with([
                'category',
                'confirmedBy',
                'source',
                'attachments'
            ])->findOrFail($id);

            return Inertia::render('Financial/Transactions/Show', [
                'transaction' => $transaction,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir transação financeira: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Transação não encontrada.');
        }
    }

    /**
     * Buscar detalhes completos para modal (API/JSON)
     */
    public function details($id)
    {
        try {
            $transaction = FinancialTransaction::with([
                'category:id,name,color,description',
                'confirmedBy:id,name',
                'attachments'
            ])->findOrFail($id);

            // Retornar apenas em formato JSON para a modal
            return response()->json($transaction);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar detalhes da transação: ' . $e->getMessage());
            return response()->json(['error' => 'Transação não encontrada'], 404);
        }
    }

    /**
     * Formulário de edição
     */
    public function edit($id)
    {
        try {
            $transaction = FinancialTransaction::with('category')->findOrFail($id);
            
            // Bloquear edição de transações rastreadas
            if ($transaction->source_type) {
                return redirect()
                    ->route('financial.transactions.show', $id)
                    ->with('error', 'Transações rastreadas automaticamente não podem ser editadas.');
            }
            
            $categories = FinancialCategory::active()
                ->orderBy('type')
                ->orderBy('name')
                ->get();
            
            $pagantes = Supplier::active()->pagante()->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);
            $fornecedores = Supplier::active()->fornecedor()->orderBy('name')->get(['id', 'is_pagante', 'is_fornecedor', 'name', 'document']);
            
            return Inertia::render('Financial/Transactions/Edit', [
                'transaction' => $transaction,
                'categories' => $categories,
                'pagantes' => $pagantes,
                'fornecedores' => $fornecedores,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao carregar formulário de edição: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Transação não encontrada.');
        }
    }

    /**
     * Atualizar transação
     */
    public function update(Request $request, $id)
    {
        try {
            $transaction = FinancialTransaction::findOrFail($id);
            
            // Bloquear edição de transações rastreadas
            if ($transaction->source_type) {
                return redirect()->back()->withErrors([
                    'error' => 'Transações rastreadas automaticamente não podem ser editadas.'
                ]);
            }
            
            $validated = $request->validate([
                'category_id' => 'required|exists:financial_categories,id',
                'description' => 'required|string|max:500',
                'amount' => 'required|numeric|min:0.01',
                'transaction_date' => 'required|date',
                'payment_method' => 'nullable|in:pix,credit_card,debit_card,cash,bank_transfer,check,other',
                'reference' => 'nullable|string|max:255',
                'status' => 'nullable|in:pending,confirmed',
                'notes' => 'nullable|string|max:1000',
                'payer_name' => 'nullable|string|max:255',
                'payer_document' => 'nullable|string|max:20',
                'payee_name' => 'nullable|string|max:255',
                'payee_document' => 'nullable|string|max:20',
                'payer_supplier_id' => 'nullable|exists:suppliers,id',
                'payee_supplier_id' => 'nullable|exists:suppliers,id',
            ]);
            
            // Não permitir voltar de confirmed para pending
            if ($transaction->status === 'confirmed' && ($validated['status'] ?? null) === 'pending') {
                return redirect()->back()->withErrors([
                    'status' => 'Não é possível alterar status de confirmado para pendente.'
                ]);
            }
            
            // Filtrar valores null para evitar atualizar campos para NULL quando não fornecidos
            // Isso preserva valores existentes quando campos não são enviados na requisição
            $updateData = array_filter($validated, function ($value) {
                return $value !== null;
            });
            
            $transaction->update($updateData);
            
            return redirect()
                ->route('financial.transactions.show', $id)
                ->with('success', 'Transação atualizada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar transação financeira: ' . $e->getMessage());
            return redirect()->back()
                ->withErrors(['error' => 'Erro ao atualizar transação.'])
                ->withInput();
        }
    }

    /**
     * Atualizar status da transação
     */
    public function updateStatus(Request $request, $id)
    {
        try {
            $transaction = FinancialTransaction::findOrFail($id);
            
            // Validar novo status
            $validated = $request->validate([
                'status' => 'required|in:pending,confirmed',
            ]);
            
            // Atualizar status
            $transaction->status = $validated['status'];
            
            // Se confirmando, registrar data e usuário
            if ($validated['status'] === 'confirmed') {
                $transaction->confirmed_at = now();
                $transaction->confirmed_by = auth()->id();
            } else {
                // Se mudando para pending, limpar dados de confirmação
                $transaction->confirmed_at = null;
                $transaction->confirmed_by = null;
            }
            
            $transaction->save();
            
            // Recarregar com relacionamentos para retornar dados completos
            $transaction->load(['category:id,name,color,description', 'confirmedBy:id,name', 'attachments']);
            
            return response()->json([
                'message' => 'Status atualizado com sucesso!',
                'transaction' => $transaction
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar status da transação: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao atualizar status'], 500);
        }
    }

    /**
     * Cancelar transação
     */
    public function cancel(Request $request, $id)
    {
        try {
            $transaction = FinancialTransaction::findOrFail($id);
            
            $reason = $request->input('reason');
            $this->financialService->cancelTransaction($transaction, $reason);

            return redirect()->back()
                ->with('success', 'Transação cancelada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao cancelar transação financeira: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Erro ao cancelar transação.');
        }
    }

    /**
     * Relatório financeiro
     */
    public function report(Request $request)
    {
        try {
            $startDate = $request->input('start_date', now()->startOfMonth()->format('Y-m-d'));
            $endDate = $request->input('end_date', now()->endOfMonth()->format('Y-m-d'));

            $report = $this->financialService->getFinancialReport($startDate, $endDate);

            return Inertia::render('Financial/Report', [
                'report' => $report,
                'filters' => [
                    'start_date' => $startDate,
                    'end_date' => $endDate,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao gerar relatório financeiro: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar relatório.');
        }
    }
}

