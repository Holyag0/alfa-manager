<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateMonthlyFeeRequest;
use App\Models\MonthlyFee;
use App\Models\Enrollment;
use App\Services\MonthlyFeeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class MonthlyFeeController extends Controller
{
    protected $monthlyFeeService;

    public function __construct(MonthlyFeeService $monthlyFeeService)
    {
        $this->monthlyFeeService = $monthlyFeeService;
    }

    /**
     * Listar contratos de mensalidades
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = MonthlyFee::with(['enrollment.student', 'enrollment.classroom', 'classroomService']);

            // Filtros
            if ($request->has('academic_year')) {
                $query->where('academic_year', $request->academic_year);
            }

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('enrollment_id')) {
                $query->where('enrollment_id', $request->enrollment_id);
            }

            if ($request->has('student_id')) {
                $query->whereHas('enrollment', function ($q) use ($request) {
                    $q->where('student_id', $request->student_id);
                });
            }

            // Ordenação
            $sortBy = $request->get('sort_by', 'created_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginação
            $perPage = $request->get('per_page', 15);
            $monthlyFees = $query->paginate($perPage);

            return response()->json($monthlyFees);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar contratos de mensalidades',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Detalhar contrato de mensalidades
     */
    public function show($id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::with([
                'enrollment.student.guardians',
                'enrollment.classroom',
                'classroomService',
                'installments.payments'
            ])->findOrFail($id);

            return response()->json($monthlyFee);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Contrato de mensalidades não encontrado',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Criar contrato de mensalidades manualmente
     */
    public function store(CreateMonthlyFeeRequest $request)
    {
        try {
            $enrollment = Enrollment::findOrFail($request->enrollment_id);
            
            $monthlyFee = $this->monthlyFeeService->createMonthlyFee($enrollment, $request->validated());

            // Se for uma requisição Inertia (vem do router.post do Inertia), retornar redirect
            if ($request->header('X-Inertia')) {
                return back()->with('success', [
                    'message' => 'Contrato de mensalidades criado com sucesso',
                    'data' => $monthlyFee->load(['enrollment.student', 'installments']),
                ]);
            }

            // Caso contrário, retornar JSON (para requisições API diretas)
            return response()->json([
                'message' => 'Contrato de mensalidades criado com sucesso',
                'data' => $monthlyFee->load(['enrollment.student', 'installments']),
            ], 201);
        } catch (\Exception $e) {
            // Se for uma requisição Inertia, retornar redirect com erro
            if ($request->header('X-Inertia')) {
                return back()->withErrors([
                    'message' => 'Erro ao criar contrato de mensalidades',
                    'error' => $e->getMessage(),
                ]);
            }

            // Caso contrário, retornar JSON com erro
            return response()->json([
                'message' => 'Erro ao criar contrato de mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Atualizar contrato de mensalidades
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::findOrFail($id);
            
            $monthlyFee->update($request->only([
                'has_punctuality_discount',
                'punctuality_discount_amount',
                'due_day',
                'notes',
            ]));

            return response()->json([
                'message' => 'Contrato de mensalidades atualizado com sucesso',
                'data' => $monthlyFee->fresh(['enrollment.student', 'installments']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao atualizar contrato de mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancelar contrato de mensalidades
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::findOrFail($id);
            
            $reason = $request->input('reason', 'Cancelado manualmente');
            $this->monthlyFeeService->cancelMonthlyFee($monthlyFee, $reason);

            return response()->json([
                'message' => 'Contrato de mensalidades cancelado com sucesso',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao cancelar contrato de mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Listar parcelas do contrato
     */
    public function installments($id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::findOrFail($id);
            
            $installments = $monthlyFee->installments()
                ->with('payments')
                ->orderBy('installment_number')
                ->get();

            return response()->json($installments);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao listar parcelas',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Suspender contrato
     */
    public function suspend(Request $request, $id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::findOrFail($id);
            
            $reason = $request->input('reason', 'Suspenso manualmente');
            $this->monthlyFeeService->suspendMonthlyFee($monthlyFee, $reason);

            return response()->json([
                'message' => 'Contrato de mensalidades suspenso com sucesso',
                'data' => $monthlyFee->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao suspender contrato de mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Reativar contrato
     */
    public function reactivate($id): JsonResponse
    {
        try {
            $monthlyFee = MonthlyFee::findOrFail($id);
            
            $this->monthlyFeeService->reactivateMonthlyFee($monthlyFee);

            return response()->json([
                'message' => 'Contrato de mensalidades reativado com sucesso',
                'data' => $monthlyFee->fresh(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Erro ao reativar contrato de mensalidades',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}

