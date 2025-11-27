<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeePayment;
use App\Models\MonthlyFeeInstallment;
use App\Services\MonthlyFeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class MonthlyFeePaymentController extends Controller
{
    protected $paymentService;

    public function __construct(MonthlyFeePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Listar pagamentos
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFeePayment::with([
                'installment.monthlyFee.enrollment.student',
                'guardian',
            ]);

            // Filtros
            if ($request->has('method')) {
                $query->where('method', $request->method);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('guardian_id')) {
                $query->where('paid_by_guardian_id', $request->guardian_id);
            }

            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('payment_date', [
                    $request->date_from,
                    $request->date_to
                ]);
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'payment_date');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $payments = $query->paginate($perPage);

            return response()->json($payments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar pagamentos',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detalhar pagamento
     */
    public function show($id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::with([
                'installment.monthlyFee.enrollment.student',
                'installment.monthlyFee.enrollment.classroom',
                'guardian',
            ])->findOrFail($id);

            return response()->json($payment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Pagamento não encontrado',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Registrar novo pagamento
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($request->installment_id);
            
            $paymentData = [
                'paid_by_guardian_id' => $request->paid_by_guardian_id,
                'amount' => $request->amount,
                'method' => $request->method,
                'payment_date' => $request->payment_date ?? now(),
                'reference' => $request->reference ?? null,
                'transaction_id' => $request->transaction_id ?? null,
                'notes' => $request->notes ?? null,
                'auto_confirm' => $request->auto_confirm ?? false,
            ];

            $payment = $this->paymentService->processPayment($installment, $paymentData);

            return response()->json([
                'message' => 'Pagamento registrado com sucesso',
                'data' => $payment->load(['installment', 'guardian']),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao registrar pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Confirmar pagamento
     */
    public function confirm($id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::findOrFail($id);
            
            $success = $this->paymentService->confirmPayment($payment);

            if ($success) {
                return response()->json([
                    'message' => 'Pagamento confirmado com sucesso',
                    'data' => $payment->fresh(['installment', 'guardian']),
                ]);
            }

            return response()->json([
                'message' => 'Não foi possível confirmar o pagamento',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao confirmar pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Estornar pagamento (apenas confirmados)
     */
    public function refund(Request $request, $id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::findOrFail($id);
            
            $reason = $request->input('reason', 'Estorno solicitado');
            $success = $this->paymentService->refundPayment($payment, $reason);

            if ($success) {
                return response()->json([
                    'message' => 'Pagamento estornado com sucesso',
                    'data' => $payment->fresh(['installment.monthlyFee.enrollment.student', 'guardian']),
                ]);
            }

            return response()->json([
                'message' => 'Não foi possível estornar o pagamento',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao estornar pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reverter pagamento (confirmado ou pendente)
     * Remove o pagamento e restaura a mensalidade ao estado anterior
     */
    public function revert(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'reason' => ['nullable', 'string', 'max:500'],
            ]);

            $payment = MonthlyFeePayment::with(['installment'])->findOrFail($id);
            
            $reason = $request->input('reason');
            $success = $this->paymentService->revertPayment($payment, $reason);

            if ($success) {
                return response()->json([
                    'message' => 'Pagamento revertido com sucesso. A mensalidade foi restaurada ao estado anterior.',
                    'data' => [
                        'payment' => $payment->fresh(['installment.monthlyFee.enrollment.student', 'guardian']),
                        'installment' => $payment->installment->fresh(),
                    ],
                ]);
            }

            return response()->json([
                'message' => 'Não foi possível reverter o pagamento',
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao reverter pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Gerar recibo de pagamento
     */
    public function receipt($id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::with([
                'installment.monthlyFee.enrollment.student',
                'installment.monthlyFee.enrollment.classroom',
                'guardian',
            ])->findOrFail($id);

            if ($payment->status !== 'confirmed') {
                return response()->json([
                    'message' => 'Apenas pagamentos confirmados podem gerar recibo',
                ], 400);
            }

            $receiptNumber = $this->paymentService->generateReceipt($payment);

            // Aqui você pode implementar a geração do PDF do recibo
            // Por enquanto, vamos retornar os dados do recibo em JSON
            
            return response()->json([
                'receipt_number' => $receiptNumber,
                'payment' => $payment,
                'student' => $payment->installment->monthlyFee->enrollment->student,
                'guardian' => $payment->guardian,
                'installment' => $payment->installment,
                'issued_at' => now()->format('d/m/Y H:i:s'),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao gerar recibo',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancelar pagamento
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::findOrFail($id);
            
            if ($payment->status === 'confirmed') {
                return response()->json([
                    'message' => 'Pagamentos confirmados devem ser estornados, não cancelados',
                ], 400);
            }

            $payment->update([
                'status' => 'cancelled',
                'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 
                           'Cancelado em ' . now()->format('d/m/Y H:i') . 
                           ($request->reason ? ": {$request->reason}" : ''),
            ]);

            return response()->json([
                'message' => 'Pagamento cancelado com sucesso',
                'data' => $payment->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Editar pagamento
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $payment = MonthlyFeePayment::findOrFail($id);
            
            // Validação condicional baseada no status do pagamento
            $rules = [];
            
            if ($payment->status === 'confirmed') {
                // Se confirmado, permitir editar apenas campos não financeiros
                $rules = [
                    'reference' => ['sometimes', 'string', 'max:255'],
                    'transaction_id' => ['sometimes', 'string', 'max:255'],
                    'notes' => ['sometimes', 'string', 'max:1000'],
                ];
            } else {
                // Se não confirmado, permitir editar todos os campos
                $rules = [
                    'paid_by_guardian_id' => ['sometimes', 'integer', 'exists:guardians,id'],
                    'amount' => ['sometimes', 'numeric', 'min:0'],
                    'original_installment_amount' => ['sometimes', 'numeric', 'min:0'],
                    'sibling_discount' => ['sometimes', 'numeric', 'min:0'],
                    'punctuality_discount' => ['sometimes', 'numeric', 'min:0'],
                    'other_discounts' => ['sometimes', 'numeric', 'min:0'],
                    'interest_applied' => ['sometimes', 'numeric', 'min:0'],
                    'fine_applied' => ['sometimes', 'numeric', 'min:0'],
                    'method' => ['sometimes', 'string', 'in:pix,credit_card,debit_card,cash,bank_transfer,check'],
                    'payment_date' => ['sometimes', 'date'],
                    'reference' => ['sometimes', 'string', 'max:255'],
                    'transaction_id' => ['sometimes', 'string', 'max:255'],
                    'notes' => ['sometimes', 'string', 'max:1000'],
                ];
            }

            $validated = $request->validate($rules);
            
            $updatedPayment = $this->paymentService->updatePayment($payment, $validated);

            return response()->json([
                'message' => 'Pagamento atualizado com sucesso',
                'data' => $updatedPayment->load(['installment.monthlyFee.enrollment.student', 'guardian']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar pagamento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Estatísticas de pagamentos
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFeePayment::query();

            // Filtrar por período se fornecido
            if ($request->has('date_from') && $request->has('date_to')) {
                $query->whereBetween('payment_date', [
                    $request->date_from,
                    $request->date_to
                ]);
            } else {
                // Padrão: mês atual
                $query->whereMonth('payment_date', now()->month)
                      ->whereYear('payment_date', now()->year);
            }

            $totalConfirmed = (clone $query)->where('status', 'confirmed')->sum('amount');
            $totalPending = (clone $query)->where('status', 'pending')->sum('amount');
            $totalRefunded = (clone $query)->where('status', 'refunded')->sum('amount');
            
            $countConfirmed = (clone $query)->where('status', 'confirmed')->count();
            $countPending = (clone $query)->where('status', 'pending')->count();
            $countRefunded = (clone $query)->where('status', 'refunded')->count();

            // Pagamentos por método
            $byMethod = (clone $query)
                ->where('status', 'confirmed')
                ->selectRaw('method, COUNT(*) as count, SUM(amount) as total')
                ->groupBy('method')
                ->get();

            return response()->json([
                'period' => [
                    'from' => $request->date_from ?? now()->startOfMonth()->format('Y-m-d'),
                    'to' => $request->date_to ?? now()->endOfMonth()->format('Y-m-d'),
                ],
                'totals' => [
                    'confirmed' => [
                        'amount' => $totalConfirmed,
                        'count' => $countConfirmed,
                    ],
                    'pending' => [
                        'amount' => $totalPending,
                        'count' => $countPending,
                    ],
                    'refunded' => [
                        'amount' => $totalRefunded,
                        'count' => $countRefunded,
                    ],
                ],
                'by_method' => $byMethod,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao gerar estatísticas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}

