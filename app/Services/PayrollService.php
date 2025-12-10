<?php

namespace App\Services;

use App\Models\Payroll;
use App\Models\PayrollEntry;
use App\Models\Employee;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PayrollService
{
    protected Payroll $payroll;
    protected PayrollEntry $payrollEntry;

    public function __construct(Payroll $payroll, PayrollEntry $payrollEntry)
    {
        $this->payroll = $payroll;
        $this->payrollEntry = $payrollEntry;
    }

    /**
     * Gerar 12 folhas do ano
     */
    public function generateYearPayrolls(int $year): Collection
    {
        $payrolls = collect();

        DB::transaction(function () use ($year, &$payrolls) {
            for ($month = 1; $month <= 12; $month++) {
                // Verificar se já existe
                $existing = $this->payroll->where('year', $year)->where('month', $month)->first();
                
                if (!$existing) {
                    $monthName = Payroll::MONTH_NAMES[$month];
                    $payroll = $this->payroll->create([
                        'year' => $year,
                        'month' => $month,
                        'reference' => strtoupper($monthName) . ' - ' . $year,
                        'status' => 'draft',
                    ]);
                    $payrolls->push($payroll);
                } else {
                    $payrolls->push($existing);
                }
            }
        });

        return $payrolls;
    }

    /**
     * Criar uma folha adicional
     */
    public function create(array $data): Payroll
    {
        $monthName = Payroll::MONTH_NAMES[$data['month']];
        $data['reference'] = $data['reference'] ?? (strtoupper($monthName) . ' - ' . $data['year']);
        $data['status'] = $data['status'] ?? 'draft';

        return $this->payroll->create($data);
    }

    /**
     * Listar folhas com filtros
     */
    public function list(array $filters = []): Collection
    {
        $query = $this->payroll->newQuery();

        if (!empty($filters['year'])) {
            $query->byYear((int) $filters['year']);
        }

        if (!empty($filters['status'])) {
            $query->byStatus($filters['status']);
        }

        return $query->orderBy('year', 'desc')->orderBy('month', 'asc')->get();
    }

    /**
     * Listar anos disponíveis
     */
    public function getAvailableYears(): array
    {
        $years = $this->payroll->distinct()->pluck('year')->toArray();
        
        // Adicionar ano atual se não existir
        $currentYear = date('Y');
        if (!in_array($currentYear, $years)) {
            $years[] = $currentYear;
        }
        
        rsort($years);
        return $years;
    }

    /**
     * Verificar se existem folhas para um ano
     */
    public function hasPayrollsForYear(int|string $year): bool
    {
        return $this->payroll->byYear((int) $year)->exists();
    }

    /**
     * Buscar folha por ID
     */
    public function find(int|string $id): ?Payroll
    {
        return $this->payroll->find((int) $id);
    }

    /**
     * Buscar folha com lançamentos e colaboradores
     */
    public function findWithEntries(int $id): ?Payroll
    {
        return $this->payroll
            ->with(['entries.employee.position'])
            ->find($id);
    }

    /**
     * Buscar todos os colaboradores ativos para uma folha
     * Retorna colaboradores com ou sem lançamento
     */
    public function getEmployeesForPayroll(int $payrollId): Collection
    {
        $payroll = $this->find($payrollId);
        if (!$payroll) {
            return collect();
        }

        // Buscar todos colaboradores ativos
        $employees = Employee::active()
            ->with('position')
            ->orderBy('name')
            ->get();

        // Buscar lançamentos existentes
        $entries = $this->payrollEntry
            ->where('payroll_id', $payrollId)
            ->get()
            ->keyBy('employee_id');

        // Mesclar dados
        return $employees->map(function ($employee) use ($entries) {
            $entry = $entries->get($employee->id);
            
            return [
                'employee' => $employee,
                'entry' => $entry,
                'has_entry' => $entry !== null,
                'is_paid' => $entry && $entry->paid_at !== null,
            ];
        });
    }

    /**
     * Buscar ou criar lançamento para um colaborador
     */
    public function getOrCreateEntry(int $payrollId, int $employeeId): PayrollEntry
    {
        $entry = $this->payrollEntry
            ->where('payroll_id', $payrollId)
            ->where('employee_id', $employeeId)
            ->first();

        if (!$entry) {
            $employee = Employee::find($employeeId);
            
            $entry = $this->payrollEntry->create([
                'payroll_id' => $payrollId,
                'employee_id' => $employeeId,
                'base_salary' => $employee->salary ?? 0,
            ]);
        }

        return $entry->load('employee.position');
    }

    /**
     * Registrar pagamento de colaborador
     */
    public function registerPayment(int $payrollId, int $employeeId, array $data): PayrollEntry
    {
        $payroll = $this->find($payrollId);
        if (!$payroll) {
            throw new \Exception('Folha de pagamento não encontrada.');
        }

        if (!$payroll->canModifyEntries()) {
            throw new \Exception('Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.');
        }

        $entry = $this->getOrCreateEntry($payrollId, $employeeId);
        
        // Se base_salary não foi informado, usar o salário de carteira do colaborador
        if (!isset($data['base_salary']) || $data['base_salary'] == 0) {
            $employee = Employee::find($employeeId);
            $data['base_salary'] = $employee->salary ?? $entry->base_salary;
        }

        $entry->update([
            'base_salary' => $data['base_salary'] ?? $entry->base_salary,
            'gratification' => $data['gratification'] ?? 0,
            'bonus' => $data['bonus'] ?? 0,
            'inss_deduction' => $data['inss_deduction'] ?? 0,
            'advance_deduction' => $data['advance_deduction'] ?? 0,
            'other_deductions' => $data['other_deductions'] ?? 0,
            'payment_method' => $data['payment_method'] ?? null,
            'payment_info' => $data['payment_info'] ?? null,
            'notes' => $data['notes'] ?? null,
            'paid_at' => $data['mark_as_paid'] ?? false ? now() : $entry->paid_at,
        ]);

        return $entry->fresh(['employee.position']);
    }

    /**
     * Atualizar lançamento existente
     */
    public function updateEntry(int $entryId, array $data): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        
        if (!$entry->payroll->canModifyEntries()) {
            throw new \Exception('Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.');
        }
        
        $entry->update($data);

        return $entry->fresh(['employee.position']);
    }

    /**
     * Marcar como pago
     */
    public function markAsPaid(int $entryId): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        
        if (!$entry->payroll->canModifyEntries()) {
            throw new \Exception('Folha de pagamento fechada não permite alterações. Reabra a folha para fazer alterações.');
        }
        
        $entry->paid_at = now();
        $entry->save();

        return $entry->fresh(['employee.position']);
    }

    /**
     * Desmarcar como pago
     */
    public function markAsUnpaid(int $entryId): PayrollEntry
    {
        $entry = $this->payrollEntry->findOrFail($entryId);
        $entry->paid_at = null;
        $entry->save();

        return $entry->fresh(['employee.position']);
    }

    /**
     * Fechar folha de pagamento
     */
    public function close(int $id): Payroll
    {
        $payroll = $this->payroll->findOrFail($id);
        
        $payroll->update([
            'status' => 'completed',
            'closed_at' => now(),
        ]);

        return $payroll->fresh();
    }

    /**
     * Reabrir folha de pagamento
     * Permite reabrir uma folha que foi fechada (status completed) para edição
     */
    public function reopen(int|string $id): Payroll
    {
        $id = (int) $id;
        
        if ($id <= 0) {
            throw new \Exception('ID inválido para reabrir folha.');
        }

        $payroll = $this->payroll->withTrashed()->find($id);
        
        if (!$payroll) {
            throw new \Exception('Folha de pagamento não encontrada.');
        }

        // Verificar se a folha foi deletada
        if ($payroll->trashed()) {
            throw new \Exception('Não é possível reabrir uma folha que foi excluída.');
        }

        // Verificar se a folha está fechada (completed)
        if ($payroll->status !== 'completed') {
            throw new \Exception('Apenas folhas fechadas podem ser reabertas.');
        }

        // Reabrir a folha: mudar status para draft e limpar closed_at
        $payroll->update([
            'status' => 'draft',
            'closed_at' => null,
        ]);

        return $payroll->fresh();
    }

    /**
     * Excluir folha
     */
    public function delete(int|string $id): bool
    {
        $id = (int) $id;
        
        if ($id <= 0) {
            throw new \Exception('ID inválido para exclusão.');
        }

        // Buscar incluindo soft deletes para verificar se existe
        $payroll = $this->payroll->withTrashed()->find($id);
        
        if (!$payroll) {
            throw new \Exception('Folha de pagamento não encontrada.');
        }

        // Se já foi deletada, lançar exceção
        if ($payroll->trashed()) {
            throw new \Exception('Esta folha de pagamento já foi excluída.');
        }

        // Verificar se a folha pode ser deletada (apenas se estiver em draft)
        if ($payroll->status !== 'draft') {
            throw new \Exception('Apenas folhas com status rascunho podem ser excluídas.');
        }

        // Deletar entries relacionadas primeiro (cascade já faz isso, mas vamos garantir)
        $payroll->entries()->delete();
        
        // Deletar a folha (soft delete)
        return $payroll->delete();
    }

    /**
     * Deletar todas as folhas com status draft de um ano específico
     * Retorna array com informações sobre a operação
     */
    public function deleteAllDraft(int $year): array
    {
        // Buscar folhas draft do ano especificado
        $draftPayrolls = $this->payroll
            ->where('year', $year)
            ->where('status', 'draft')
            ->get();
        
        $count = $draftPayrolls->count();

        if ($count === 0) {
            return [
                'success' => false,
                'message' => "Não há folhas com status rascunho para o ano {$year}.",
                'deleted_count' => 0
            ];
        }

        // Verificar se há folhas do mesmo ano que não são draft
        $nonDraftCount = $this->payroll
            ->where('year', $year)
            ->where('status', '!=', 'draft')
            ->count();
        
        if ($nonDraftCount > 0) {
            return [
                'success' => false,
                'message' => "Existem {$nonDraftCount} folha(s) do ano {$year} com status diferente de rascunho. Apenas folhas em rascunho podem ser deletadas.",
                'deleted_count' => 0,
                'non_draft_count' => $nonDraftCount
            ];
        }

        // Deletar todas as folhas draft do ano
        DB::transaction(function () use ($draftPayrolls) {
            foreach ($draftPayrolls as $payroll) {
                $payroll->delete();
            }
        });

        return [
            'success' => true,
            'message' => "{$count} folha(s) do ano {$year} deletada(s) com sucesso!",
            'deleted_count' => $count
        ];
    }

    /**
     * Atualizar uma folha específica
     * Remove colaboradores deletados/desativados e adiciona novos colaboradores ativos
     */
    public function updatePayroll(int $payrollId): array
    {
        $payroll = $this->find($payrollId);
        if (!$payroll) {
            throw new \Exception('Folha de pagamento não encontrada.');
        }

        if (!$payroll->canBeUpdated()) {
            throw new \Exception('Folha de pagamento fechada não pode ser atualizada. Reabra a folha para fazer alterações.');
        }

        $removed = 0;
        $added = 0;

        DB::transaction(function () use ($payrollId, &$removed, &$added) {
            // Buscar todos os colaboradores ativos
            $activeEmployees = Employee::active()->pluck('id')->toArray();

            // Buscar entries existentes
            $existingEntries = $this->payrollEntry
                ->where('payroll_id', $payrollId)
                ->get();

            // Remover entries de colaboradores que não estão mais ativos ou foram deletados
            foreach ($existingEntries as $entry) {
                if (!in_array($entry->employee_id, $activeEmployees)) {
                    $entry->delete();
                    $removed++;
                }
            }

            // Adicionar novos colaboradores ativos que não têm entry
            $existingEmployeeIds = $existingEntries->pluck('employee_id')->toArray();
            
            foreach ($activeEmployees as $employeeId) {
                if (!in_array($employeeId, $existingEmployeeIds)) {
                    $employee = Employee::find($employeeId);
                    if ($employee) {
                        $this->payrollEntry->create([
                            'payroll_id' => $payrollId,
                            'employee_id' => $employeeId,
                            'base_salary' => $employee->salary ?? 0,
                        ]);
                        $added++;
                    }
                }
            }

            // Recalcular totais da folha
            $payroll = $this->find($payrollId);
            $payroll->recalculateTotals();
        });

        return [
            'removed' => $removed,
            'added' => $added,
            'payroll' => $this->find($payrollId)->fresh(),
        ];
    }

    /**
     * Atualizar todas as folhas de um ano específico
     * Remove colaboradores deletados/desativados e adiciona novos colaboradores ativos em todas as folhas do ano
     */
    public function updateAllPayrolls(int $year): array
    {
        $results = [];
        $payrolls = $this->payroll->where('year', $year)->get();

        if ($payrolls->isEmpty()) {
            return [
                'total' => 0,
                'success' => 0,
                'failed' => 0,
                'results' => [],
                'message' => "Não há folhas para o ano {$year}."
            ];
        }

        foreach ($payrolls as $payroll) {
            try {
                $result = $this->updatePayroll($payroll->id);
                $results[] = [
                    'payroll_id' => $payroll->id,
                    'reference' => $payroll->reference,
                    'removed' => $result['removed'],
                    'added' => $result['added'],
                    'success' => true
                ];
            } catch (\Exception $e) {
                $results[] = [
                    'payroll_id' => $payroll->id,
                    'reference' => $payroll->reference,
                    'error' => $e->getMessage(),
                    'success' => false
                ];
            }
        }

        $successCount = count(array_filter($results, fn($r) => $r['success']));
        $failedCount = count(array_filter($results, fn($r) => !$r['success']));

        return [
            'total' => count($results),
            'success' => $successCount,
            'failed' => $failedCount,
            'results' => $results,
            'message' => "{$successCount} folha(s) do ano {$year} atualizada(s) com sucesso." . ($failedCount > 0 ? " {$failedCount} falha(s)." : "")
        ];
    }
}

