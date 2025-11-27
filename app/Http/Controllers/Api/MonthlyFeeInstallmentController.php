<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MonthlyFeeInstallment;
use App\Services\MonthlyFeePaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

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
            $request->validate([
                'due_date' => ['sometimes', 'date'],
                'other_discounts' => ['sometimes', 'numeric', 'min:0'],
            ]);

            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            // Verificar se há pagamento confirmado (apenas para aviso, não bloqueia)
            $hasConfirmedPayment = $installment->payments()
                ->where('status', 'confirmed')
                ->exists();
            
            // Aviso: Se houver pagamento confirmado, recomenda-se reverter o pagamento primeiro
            // Mas permitimos a edição para flexibilidade em casos de correção de erros
            if ($hasConfirmedPayment) {
                Log::warning('Editando mensalidade com pagamento confirmado', [
                    'installment_id' => $installment->id,
                    'note' => 'Recomenda-se reverter o pagamento antes de editar para manter integridade dos dados',
                ]);
            }

            $updateData = [];
            
            if ($request->has('due_date')) {
                // Garantir que a data seja parseada corretamente sem problemas de timezone
                // O Carbon vai interpretar a data como local (sem hora) se for YYYY-MM-DD
                $updateData['due_date'] = Carbon::parse($request->due_date)->format('Y-m-d');
            }
            
            if ($request->has('other_discounts')) {
                $updateData['other_discounts'] = $request->other_discounts;
            }

            $installment->update($updateData);

            // Recalcular status se a data de vencimento foi alterada
            if (isset($updateData['due_date'])) {
                $today = Carbon::today();
                // Recarregar o installment para pegar a data salva corretamente
                $installment->refresh();
                $dueDate = Carbon::parse($installment->due_date)->startOfDay();
                
                if ($installment->status === 'pending' && $today->gt($dueDate)) {
                    $installment->status = 'overdue';
                    $installment->save();
                } elseif ($installment->status === 'overdue' && $today->lte($dueDate)) {
                    $installment->status = 'pending';
                    $installment->save();
                }
            }

            return response()->json([
                'message' => 'Mensalidade atualizada com sucesso',
                'data' => $installment->fresh(['monthlyFee.enrollment.student', 'payments', 'classroomService.service']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deletar mensalidade individual
     */
    public function destroy($id): JsonResponse
    {
        try {
            $installment = MonthlyFeeInstallment::findOrFail($id);
            
            // Verificar se há pagamento confirmado (apenas para aviso, não bloqueia)
            $hasConfirmedPayment = $installment->payments()
                ->where('status', 'confirmed')
                ->exists();
            
            // Aviso: Se houver pagamento confirmado, recomenda-se reverter o pagamento primeiro
            // Mas permitimos a deleção para flexibilidade em casos de correção de erros
            if ($hasConfirmedPayment) {
                Log::warning('Deletando mensalidade com pagamento confirmado', [
                    'installment_id' => $installment->id,
                    'note' => 'Recomenda-se reverter o pagamento antes de deletar para manter integridade dos dados',
                ]);
            }

            // Soft delete
            $installment->delete();

            return response()->json([
                'message' => 'Mensalidade deletada com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Atualizar data de vencimento em lote
     */
    public function updateDueDatesBatch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'installment_ids' => ['required', 'array', 'min:1'],
                'installment_ids.*' => ['required', 'integer', 'exists:monthly_fee_installments,id'],
                'due_date' => ['required', 'date'],
            ]);

            $installmentIds = $request->installment_ids;
            $dueDate = $request->due_date;
            $today = \Carbon\Carbon::today();
            // Garantir que a data seja parseada corretamente sem problemas de timezone
            // O Carbon vai interpretar a data como local (sem hora) se for YYYY-MM-DD
            $newDueDate = Carbon::parse($dueDate)->format('Y-m-d');

            $updated = [];
            $warnings = [];

            foreach ($installmentIds as $installmentId) {
                $installment = MonthlyFeeInstallment::findOrFail($installmentId);
                
                // Verificar se há pagamento confirmado (apenas para aviso, não bloqueia)
                $hasConfirmedPayment = $installment->payments()
                    ->where('status', 'confirmed')
                    ->exists();
                
                // Aviso: Se houver pagamento confirmado, recomenda-se reverter o pagamento primeiro
                // Mas permitimos a edição para flexibilidade em casos de correção de erros
                if ($hasConfirmedPayment) {
                    Log::warning('Editando mensalidade em lote com pagamento confirmado', [
                        'installment_id' => $installment->id,
                        'note' => 'Recomenda-se reverter o pagamento antes de editar para manter integridade dos dados',
                    ]);
                    
                    $warnings[] = [
                        'id' => $installment->id,
                        'reference_month' => $installment->reference_month,
                        'installment_number' => $installment->installment_number,
                        'reason' => 'Possui pagamento confirmado (processada com aviso)',
                    ];
                }

                // Usar Carbon para garantir formatação correta
                // Parsear a data e garantir que seja apenas a data (sem hora)
                $parsedDate = Carbon::parse($newDueDate)->startOfDay();
                $installment->due_date = $parsedDate;
                
                // Atualizar status se necessário
                if ($installment->status === 'pending' && $today->gt($parsedDate)) {
                    $installment->status = 'overdue';
                } elseif ($installment->status === 'overdue' && $today->lte($parsedDate)) {
                    $installment->status = 'pending';
                }
                
                $installment->save();
                
                $updated[] = [
                    'id' => $installment->id,
                    'reference_month' => $installment->reference_month,
                    'installment_number' => $installment->installment_number,
                ];
            }

            $message = count($updated) . ' mensalidade(s) atualizada(s)';
            if (count($warnings) > 0) {
                $message .= '. ' . count($warnings) . ' mensalidade(s) processada(s) com aviso (possuem pagamentos confirmados)';
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'updated' => $updated,
                    'warnings' => $warnings ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar datas de vencimento',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Deletar mensalidades em lote
     */
    public function destroyBatch(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'installment_ids' => ['required', 'array', 'min:1'],
                'installment_ids.*' => ['required', 'integer', 'exists:monthly_fee_installments,id'],
            ]);

            $installmentIds = $request->installment_ids;
            $deleted = [];
            $warnings = [];

            foreach ($installmentIds as $installmentId) {
                $installment = MonthlyFeeInstallment::findOrFail($installmentId);
                
                // Verificar se há pagamento confirmado (apenas para aviso, não bloqueia)
                $hasConfirmedPayment = $installment->payments()
                    ->where('status', 'confirmed')
                    ->exists();
                
                // Aviso: Se houver pagamento confirmado, recomenda-se reverter o pagamento primeiro
                // Mas permitimos a deleção para flexibilidade em casos de correção de erros
                if ($hasConfirmedPayment) {
                    Log::warning('Deletando mensalidade em lote com pagamento confirmado', [
                        'installment_id' => $installment->id,
                        'note' => 'Recomenda-se reverter o pagamento antes de deletar para manter integridade dos dados',
                    ]);
                    
                    $warnings[] = [
                        'id' => $installment->id,
                        'reference_month' => $installment->reference_month,
                        'installment_number' => $installment->installment_number,
                        'reason' => 'Possui pagamento confirmado (processada com aviso)',
                    ];
                }

                // Soft delete
                $installment->delete();
                
                $deleted[] = [
                    'id' => $installment->id,
                    'reference_month' => $installment->reference_month,
                    'installment_number' => $installment->installment_number,
                ];
            }

            $message = count($deleted) . ' mensalidade(s) deletada(s)';
            if (count($warnings) > 0) {
                $message .= '. ' . count($warnings) . ' mensalidade(s) processada(s) com aviso (possuem pagamentos confirmados)';
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'deleted' => $deleted,
                    'warnings' => $warnings ?? [],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao deletar mensalidades',
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

    /**
     * Trocar serviço de uma mensalidade individual
     */
    public function changeService(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'classroom_service_id' => ['required', 'integer', 'exists:classroom_services,id'],
                'reason' => ['nullable', 'string', 'max:500'],
            ]);

            $installment = MonthlyFeeInstallment::findOrFail($id);
            $monthlyFeeService = app(\App\Services\MonthlyFeeService::class);
            
            $updatedInstallment = $monthlyFeeService->changeInstallmentService(
                $installment,
                $request->classroom_service_id,
                $request->reason
            );

            return response()->json([
                'message' => 'Serviço da mensalidade trocado com sucesso',
                'data' => $updatedInstallment->load(['classroomService.service', 'monthlyFee.enrollment.student']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao trocar serviço da mensalidade',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Pré-visualizar troca de serviço de todas as mensalidades de um contrato (ano letivo)
     */
    public function previewChangeAllServices(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'classroom_service_id' => ['required', 'integer', 'exists:classroom_services,id'],
            ]);

            $installment = MonthlyFeeInstallment::findOrFail($id);
            $monthlyFee = $installment->monthlyFee;
            
            $monthlyFeeService = app(\App\Services\MonthlyFeeService::class);
            
            $preview = $monthlyFeeService->previewChangeAllInstallmentsService(
                $monthlyFee,
                $request->classroom_service_id
            );

            return response()->json($preview);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao pré-visualizar troca de serviço',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Trocar serviço de todas as mensalidades de um contrato (ano letivo)
     */
    public function changeAllServices(Request $request, $id): JsonResponse
    {
        try {
            $request->validate([
                'classroom_service_id' => ['required', 'integer', 'exists:classroom_services,id'],
                'reason' => ['nullable', 'string', 'max:500'],
            ]);

            $installment = MonthlyFeeInstallment::findOrFail($id);
            $monthlyFee = $installment->monthlyFee;
            
            $monthlyFeeService = app(\App\Services\MonthlyFeeService::class);
            
            $result = $monthlyFeeService->changeAllInstallmentsService(
                $monthlyFee,
                $request->classroom_service_id,
                $request->reason
            );

            $message = 'Serviço de mensalidades trocado com sucesso';
            if ($result['summary']['skipped'] > 0) {
                $message .= ". {$result['summary']['updated']} parcela(s) atualizada(s), {$result['summary']['skipped']} parcela(s) ignorada(s) (possuem pagamentos confirmados)";
            }

            return response()->json([
                'message' => $message,
                'data' => [
                    'monthly_fee' => $monthlyFee->fresh(['enrollment.student']),
                    'installments' => $result['updated'],
                    'skipped' => $result['skipped'],
                    'summary' => $result['summary'],
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao trocar serviço das mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}

