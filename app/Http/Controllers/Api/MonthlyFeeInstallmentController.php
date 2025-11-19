<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeeInstallment;
use App\Services\MonthlyFeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class MonthlyFeeInstallmentController extends Controller
{
    protected $paymentService;

    public function __construct(MonthlyFeePaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * Listar mensalidades
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFeeInstallment::with([
                'monthlyFee.enrollment.student',
                'monthlyFee.enrollment.classroom',
                'classroomService.service', // ← Carregar serviço para mostrar valor real
                'payments.guardian'
            ]);

            // Filtros
            if ($request->has('reference_month')) {
                $query->where('reference_month', $request->reference_month);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('student_id')) {
                $query->whereHas('monthlyFee.enrollment', function ($q) use ($request) {
                    $q->where('student_id', $request->student_id);
                });
            }

            if ($request->has('classroom_id')) {
                $query->whereHas('monthlyFee.enrollment', function ($q) use ($request) {
                    $q->where('classroom_id', $request->classroom_id);
                });
            }

            if ($request->has('due_date_from') && $request->has('due_date_to')) {
                $query->whereBetween('due_date', [
                    $request->due_date_from,
                    $request->due_date_to
                ]);
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'due_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $installments = $query->paginate($perPage);

            return response()->json($installments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar mensalidades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detalhar mensalidade
     */
    public function show($id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::with([
                'monthlyFee.enrollment.student.guardians',
                'monthlyFee.enrollment.classroom',
                'monthlyFee.classroomService',
                'classroomService.service', // ← Carregar serviço usado nesta parcela
                'payments.guardian'
            ])->findOrFail($id);

            return response()->json($installment);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Mensalidade não encontrada',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Atualizar mensalidade
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            $installment->update($request->only([
                'other_discounts',
                'notes',
            ]));

            // Recalcular valor final
            $monthlyFeeService = app(\App\Services\MonthlyFeeService::class);
            $finalAmount = $monthlyFeeService->calculateInstallmentAmount($installment);
            $installment->update(['final_amount' => $finalAmount]);

            return response()->json([
                'message' => 'Mensalidade atualizada com sucesso',
                'data' => $installment->fresh(['monthlyFee.enrollment.student', 'payments']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Mensalidades vencidas
     */
    public function overdue(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFeeInstallment::with([
                'monthlyFee.enrollment.student.guardians',
                'monthlyFee.enrollment.classroom',
            ])
            ->where('status', 'overdue')
            ->orderBy('due_date', 'asc');

            $perPage = $request->get('per_page', 15);
            $installments = $query->paginate($perPage);

            return response()->json($installments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar mensalidades vencidas',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mensalidades pendentes
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFeeInstallment::with([
                'monthlyFee.enrollment.student',
                'monthlyFee.enrollment.classroom',
            ])
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc');

            $perPage = $request->get('per_page', 15);
            $installments = $query->paginate($perPage);

            return response()->json($installments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar mensalidades pendentes',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Mensalidades por mês
     */
    public function byMonth($month): JsonResponse
    {
        try {
            $installments = MonthlyFeeInstallment::with([
                'monthlyFee.enrollment.student',
                'monthlyFee.enrollment.classroom',
                'payments.guardian'
            ])
            ->where('reference_month', $month)
            ->orderBy('due_date', 'asc')
            ->get();

            return response()->json($installments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar mensalidades do mês',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Registrar pagamento
     */
    public function pay(Request $request, $id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            $paymentData = [
                'paid_by_guardian_id' => $request->paid_by_guardian_id,
                'amount' => $request->amount ?? $installment->final_amount, // Accessor calcula do serviço atual
                'method' => $request->method,
                'payment_date' => $request->payment_date ?? now(),
                'reference' => $request->reference ?? null,
                'transaction_id' => $request->transaction_id ?? null,
                'notes' => $request->notes ?? null,
                'auto_confirm' => $request->auto_confirm ?? true, // Padrão true para confirmação automática
                'other_discounts' => $request->other_discounts ?? 0, // Descontos adicionais
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
     * Cancelar mensalidade
     */
    public function cancel(Request $request, $id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            $installment->update([
                'status' => 'cancelled',
            ]);

            return response()->json([
                'message' => 'Mensalidade cancelada com sucesso',
                'data' => $installment->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Dispensar mensalidade (waive)
     */
    public function waive(Request $request, $id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            $installment->update([
                'status' => 'waived',
            ]);

            return response()->json([
                'message' => 'Mensalidade dispensada com sucesso',
                'data' => $installment->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao dispensar mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}

