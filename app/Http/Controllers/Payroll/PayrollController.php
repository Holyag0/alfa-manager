<?php

namespace App\Http\Controllers\Payroll;

use App\Http\Controllers\Controller;
use App\Services\PayrollService;
use App\Http\Requests\PayrollEntryRequest;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class PayrollController extends Controller
{
    private PayrollService $payrollService;

    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * Lista de folhas de pagamento
     */
    public function index(Request $request)
    {
        try {
            $year = (int) $request->get('year', date('Y'));
            $payrolls = $this->payrollService->list(['year' => $year]);
            $availableYears = $this->payrollService->getAvailableYears();
            $hasPayrolls = $this->payrollService->hasPayrollsForYear($year);

            return Inertia::render('Payroll/Index', [
                'payrolls' => $payrolls,
                'selectedYear' => (int) $year,
                'availableYears' => $availableYears,
                'hasPayrolls' => $hasPayrolls,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao listar folhas de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar folhas de pagamento.');
        }
    }

    /**
     * Gerar 12 folhas do ano
     */
    public function generate(Request $request)
    {
        try {
            $year = $request->get('year', date('Y'));
            $this->payrollService->generateYearPayrolls($year);

            return redirect()->route('payroll.index', ['year' => $year])
                ->with('success', "Folhas de pagamento de {$year} geradas com sucesso!");
        } catch (\Exception $e) {
            Log::error('Erro ao gerar folhas de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao gerar folhas de pagamento.');
        }
    }

    /**
     * Criar folha adicional
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100',
                'month' => 'required|integer|min:1|max:12',
                'reference' => 'nullable|string|max:100',
            ]);

            $this->payrollService->create($validated);

            return redirect()->route('payroll.index', ['year' => $validated['year']])
                ->with('success', 'Folha de pagamento criada com sucesso!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao criar folha de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao criar folha de pagamento.');
        }
    }

    /**
     * Exibir folha com colaboradores
     */
    public function show($id)
    {
        try {
            $payroll = $this->payrollService->findWithEntries($id);

            if (!$payroll) {
                return redirect()->route('payroll.index')
                    ->with('error', 'Folha de pagamento não encontrada.');
            }

            $employeesData = $this->payrollService->getEmployeesForPayroll($id);

            return Inertia::render('Payroll/Show', [
                'payroll' => $payroll,
                'employeesData' => $employeesData,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao exibir folha de pagamento: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao carregar folha de pagamento.');
        }
    }

    /**
     * Buscar dados do lançamento de um colaborador (API)
     */
    public function getEntry($payrollId, $employeeId)
    {
        try {
            $entry = $this->payrollService->getOrCreateEntry($payrollId, $employeeId);

            return response()->json([
                'entry' => $entry,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao buscar lançamento: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao carregar lançamento.'], 500);
        }
    }

    /**
     * Registrar/Atualizar pagamento de colaborador
     */
    public function storeEntry($payrollId, $employeeId, PayrollEntryRequest $request)
    {
        try {
            $payroll = $this->payrollService->find($payrollId);
            if (!$payroll) {
                throw new \Exception('Folha de pagamento não encontrada.');
            }

            if (!$payroll->canModifyEntries()) {
                $message = 'Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.';
                if ($request->expectsJson()) {
                    return response()->json(['error' => $message], 403);
                }
                return redirect()->back()->with('error', $message);
            }

            $validated = $request->validated();
            $entry = $this->payrollService->registerPayment($payrollId, $employeeId, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Pagamento registrado com sucesso!',
                    'entry' => $entry,
                ]);
            }

            return redirect()->back()->with('success', 'Pagamento registrado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao registrar pagamento: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Erro ao registrar pagamento.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao registrar pagamento.');
        }
    }

    /**
     * Atualizar lançamento existente
     */
    public function updateEntry($payrollId, $entryId, PayrollEntryRequest $request)
    {
        try {
            $payroll = $this->payrollService->find($payrollId);
            if (!$payroll) {
                throw new \Exception('Folha de pagamento não encontrada.');
            }

            if (!$payroll->canModifyEntries()) {
                $message = 'Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.';
                if ($request->expectsJson()) {
                    return response()->json(['error' => $message], 403);
                }
                return redirect()->back()->with('error', $message);
            }

            $validated = $request->validated();
            $entry = $this->payrollService->updateEntry($entryId, $validated);

            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Lançamento atualizado com sucesso!',
                    'entry' => $entry,
                ]);
            }

            return redirect()->back()->with('success', 'Lançamento atualizado com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar lançamento: ' . $e->getMessage());
            
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Erro ao atualizar lançamento.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao atualizar lançamento.');
        }
    }

    /**
     * Marcar lançamento como pago
     */
    public function markPaid($payrollId, $entryId)
    {
        try {
            // Validar que o entry pertence ao payroll
            $payroll = $this->payrollService->find($payrollId);
            if (!$payroll) {
                return response()->json(['error' => 'Folha de pagamento não encontrada.'], 404);
            }

            $payrollEntry = \App\Models\PayrollEntry::where('id', $entryId)
                ->where('payroll_id', $payrollId)
                ->first();

            if (!$payrollEntry) {
                return response()->json(['error' => 'Lançamento não encontrado ou não pertence a esta folha.'], 404);
            }

            $entry = $this->payrollService->markAsPaid($entryId);

            return response()->json([
                'message' => 'Marcado como pago!',
                'entry' => $entry,
            ]);
        } catch (\Exception $e) {
            Log::error('Erro ao marcar como pago: ' . $e->getMessage());
            return response()->json(['error' => 'Erro ao marcar como pago.'], 500);
        }
    }

    /**
     * Fechar folha de pagamento
     */
    public function close($id)
    {
        try {
            $this->payrollService->close($id);

            return redirect()->back()->with('success', 'Folha de pagamento fechada com sucesso!');
        } catch (\Exception $e) {
            Log::error('Erro ao fechar folha: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao fechar folha de pagamento.');
        }
    }

    /**
     * Reabrir folha de pagamento
     */
    public function reopen($id)
    {
        try {
            $payroll = $this->payrollService->reopen($id);

            return redirect()->back()->with('success', 'Folha de pagamento reaberta com sucesso! Agora você pode fazer alterações.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Folha não encontrada ao reabrir: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Folha de pagamento não encontrada.');
        } catch (\Exception $e) {
            Log::error('Erro ao reabrir folha: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Deletar todas as folhas com status draft de um ano específico
     */
    public function deleteAllDraft(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100'
            ]);

            $result = $this->payrollService->deleteAllDraft($validated['year']);

            if ($result['success']) {
                return redirect()->back()->with('success', $result['message']);
            } else {
                return redirect()->back()->with('error', $result['message']);
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Erro ao deletar todas as folhas: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Erro ao deletar folhas de pagamento.');
        }
    }

    /**
     * Excluir folha
     */
    public function destroy($id)
    {
        try {
            $payroll = $this->payrollService->find($id);
            
            if (!$payroll) {
                return redirect()->back()->with('error', 'Folha de pagamento não encontrada.');
            }

            // Verificar se a folha pode ser deletada (apenas se estiver em draft)
            if ($payroll->status !== 'draft') {
                return redirect()->back()->with('error', 'Apenas folhas com status rascunho podem ser excluídas. Feche ou reabra a folha primeiro.');
            }

            $this->payrollService->delete($id);

            return redirect()->route('payroll.index', ['year' => $payroll->year])
                ->with('success', 'Folha de pagamento excluída com sucesso!');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Folha não encontrada ao excluir: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Folha de pagamento não encontrada.');
        } catch (\Exception $e) {
            Log::error('Erro ao excluir folha: ' . $e->getMessage());
            Log::error('Stack trace: ' . $e->getTraceAsString());
            return redirect()->back()->with('error', 'Erro ao excluir folha de pagamento: ' . $e->getMessage());
        }
    }

    /**
     * Atualizar uma folha específica
     */
    public function update($id)
    {
        try {
            $result = $this->payrollService->updatePayroll($id);

            if (request()->expectsJson()) {
                return response()->json([
                    'message' => 'Folha atualizada com sucesso!',
                    'removed' => $result['removed'],
                    'added' => $result['added'],
                    'payroll' => $result['payroll'],
                ]);
            }

            return redirect()->back()->with('success', 
                "Folha atualizada! {$result['added']} colaborador(es) adicionado(s) e {$result['removed']} removido(s)."
            );
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar folha: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Erro ao atualizar folha.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao atualizar folha de pagamento.');
        }
    }

    /**
     * Atualizar todas as folhas
     */
    public function updateAll(Request $request)
    {
        try {
            $validated = $request->validate([
                'year' => 'required|integer|min:2020|max:2100'
            ]);

            $result = $this->payrollService->updateAllPayrolls($validated['year']);

            if ($result['total'] === 0) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => $result['message'],
                        'total' => 0,
                        'success' => 0,
                        'failed' => 0,
                    ]);
                }
            }

            $message = $result['message'];
            
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $message,
                    'total' => $result['total'],
                    'success' => $result['success'],
                    'failed' => $result['failed'],
                    'results' => $result['results'],
                ]);
            }

            if ($result['total'] === 0) {
                return redirect()->back()->with('info', $result['message']);
            }

            $message = $result['message'];
            
            if ($result['failed'] > 0) {
                return redirect()->back()->with('warning', $message);
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            Log::error('Erro ao atualizar todas as folhas: ' . $e->getMessage());
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Erro ao atualizar folhas.'], 500);
            }
            
            return redirect()->back()->with('error', 'Erro ao atualizar folhas de pagamento.');
        }
    }
}
