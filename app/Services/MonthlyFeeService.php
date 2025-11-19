<?php

namespace App\Services;

use App\Models\MonthlyFee;
use App\Models\MonthlyFeeInstallment;
use App\Models\Enrollment;
use App\Models\ClassroomService;
use App\Models\Guardian;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthlyFeeService
{
    /**
     * Criar contrato de mensalidades para uma matrícula
     */
    public function createMonthlyFee(Enrollment $enrollment, array $data = []): MonthlyFee
    {
        try {
            return DB::transaction(function () use ($enrollment, $data) {
                // Buscar ClassroomService da turma (tipo 'monthly')
                $classroomService = $this->findMonthlyClassroomService($enrollment);

                if (!$classroomService) {
                    throw new \Exception("Serviço de mensalidade não encontrado para a turma.");
                }

                // Verificar se já existe contrato ativo para esta matrícula no ano acadêmico
                $academicYear = $data['academic_year'] ?? now()->year;
                $existingFee = MonthlyFee::where('enrollment_id', $enrollment->id)
                    ->where('academic_year', $academicYear)
                    ->whereNull('deleted_at') // Apenas contratos não deletados
                    ->first();

                if ($existingFee) {
                    Log::info("Contrato de mensalidade já existe", [
                        'enrollment_id' => $enrollment->id,
                        'monthly_fee_id' => $existingFee->id
                    ]);
                    return $existingFee;
                }

                // Descontos podem ser aplicados manualmente através dos dados de entrada
                $hasSiblingDiscount = (bool) ($data['has_sibling_discount'] ?? false);
                $siblingDiscountAmount = $hasSiblingDiscount
                    ? (float) ($data['sibling_discount_amount'] ?? 0)
                    : 0;

                // Calcular período de mensalidades
                $startMonth = $data['start_month'] ?? 1;
                $endMonth = $data['end_month'] ?? 12;
                $totalInstallments = $data['total_installments'] ?? ($endMonth - $startMonth + 1);

                // Criar registro MonthlyFee
                $academicYear = $data['academic_year'] ?? now()->year;
                $monthlyFee = MonthlyFee::create([
                    'enrollment_id' => $enrollment->id,
                    'classroom_service_id' => $classroomService->id,
                    'academic_year' => $academicYear,
                    'contract_number' => $this->generateContractNumber($enrollment, $academicYear),
                    'base_amount' => $classroomService->price,
                    'total_installments' => $totalInstallments,
                    'has_sibling_discount' => $hasSiblingDiscount,
                    'sibling_discount_amount' => $siblingDiscountAmount,
                    'has_punctuality_discount' => $data['has_punctuality_discount'] ?? true,
                    'punctuality_discount_amount' => $data['punctuality_discount_amount'] ?? 10.00,
                    'start_date' => $data['start_date'] ?? Carbon::create($academicYear, $startMonth, 1),
                    'end_date' => $data['end_date'] ?? Carbon::create($academicYear, $endMonth, 1)->endOfMonth(),
                    'due_day' => $data['due_day'] ?? 10,
                    'status' => 'active',
                    'notes' => $data['notes'] ?? null,
                ]);

                // Gerar parcelas automaticamente baseado no período
                $this->generateInstallments($monthlyFee, $startMonth, $endMonth);

                Log::info("Contrato de mensalidade criado com sucesso", [
                    'monthly_fee_id' => $monthlyFee->id,
                    'enrollment_id' => $enrollment->id,
                    'has_sibling_discount' => $hasSiblingDiscount,
                ]);

                return $monthlyFee;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao criar contrato de mensalidades", [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Gerar parcelas automaticamente baseado no período
     */
    public function generateInstallments(MonthlyFee $monthlyFee, int $startMonth = 1, int $endMonth = 12): Collection
    {
        $installments = collect();
        $installmentNumber = 1;

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            // Ajustar due_day para evitar overflow em meses com menos dias
            $daysInMonth = Carbon::create($monthlyFee->academic_year, $month, 1)->daysInMonth;
            $adjustedDay = min($monthlyFee->due_day, $daysInMonth);
            $dueDate = Carbon::create($monthlyFee->academic_year, $month, $adjustedDay);
            
            // Criar parcela apenas com dados de relacionamento
            // O valor será sempre buscado do serviço atual (classroom_service.service.price)
            $installment = MonthlyFeeInstallment::create([
                'monthly_fee_id' => $monthlyFee->id,
                'classroom_service_id' => $monthlyFee->classroom_service_id, // ← Referência ao serviço
                'reference_month' => Carbon::create($monthlyFee->academic_year, $month, 1)->format('Y-m'),
                'installment_number' => $installmentNumber,
                'due_date' => $dueDate,
                'status' => 'pending',
            ]);

            $installments->push($installment);
            $installmentNumber++;
        }

        Log::info("Parcelas geradas com sucesso", [
            'monthly_fee_id' => $monthlyFee->id,
            'total_installments' => $installments->count(),
            'period' => "$startMonth-$endMonth",
        ]);

        return $installments;
    }

    /**
     * Verificar se aluno tem irmão matriculado
     */
    public function checkSiblingDiscount(Enrollment $enrollment): bool
    {
        try {
            $student = $enrollment->student;
            
            // Buscar guardians do aluno
            $guardians = $student->guardians;

            if ($guardians->isEmpty()) {
                return false;
            }

            // Para cada guardian, verificar se tem outros alunos matriculados no mesmo ano
            foreach ($guardians as $guardian) {
                $activeEnrollmentsCount = Enrollment::whereHas('student.guardians', function ($query) use ($guardian) {
                    $query->where('guardians.id', $guardian->id);
                })
                ->where('academic_year', $enrollment->academic_year)
                ->where('status', 'active')
                ->where('id', '!=', $enrollment->id) // Excluir a matrícula atual
                ->count();

                if ($activeEnrollmentsCount > 0) {
                    return true;
                }
            }

            return false;
        } catch (\Exception $e) {
            Log::error("Erro ao verificar desconto de irmão", [
                'enrollment_id' => $enrollment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Calcular valor do desconto de irmão
     */
    public function calculateSiblingDiscount(Enrollment $enrollment, ?float $baseAmount = null): float
    {
        // Lógica de cálculo do desconto de irmão
        // Por padrão, 10% do valor base ou valor fixo configurável
        $defaultDiscountPercentage = 0.10; // 10%
        $defaultDiscountFixed = 30.00; // R$ 30,00

        // Você pode buscar de configurações ou deixar fixo
        return $defaultDiscountFixed;
    }

    /**
     * Cancelar contrato de mensalidades
     */
    public function cancelMonthlyFee(MonthlyFee $monthlyFee, ?string $reason = null): bool
    {
        try {
            return DB::transaction(function () use ($monthlyFee, $reason) {
                // Atualizar status do contrato
                $monthlyFee->update([
                    'status' => 'cancelled',
                    'notes' => ($monthlyFee->notes ? $monthlyFee->notes . "\n" : '') . 
                               "Cancelado em " . now()->format('d/m/Y H:i') . 
                               ($reason ? ": {$reason}" : ''),
                ]);

                // Cancelar parcelas futuras (pendentes)
                $monthlyFee->installments()
                    ->where('status', 'pending')
                    ->where('due_date', '>', now())
                    ->update([
                        'status' => 'cancelled',
                        'notes' => "Cancelado: {$reason}",
                    ]);

                Log::info("Contrato de mensalidade cancelado", [
                    'monthly_fee_id' => $monthlyFee->id,
                    'reason' => $reason,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao cancelar contrato de mensalidades", [
                'monthly_fee_id' => $monthlyFee->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Suspender temporariamente contrato de mensalidades
     */
    public function suspendMonthlyFee(MonthlyFee $monthlyFee, ?string $reason = null): bool
    {
        try {
            $monthlyFee->update([
                'status' => 'suspended',
                'notes' => ($monthlyFee->notes ? $monthlyFee->notes . "\n" : '') . 
                           "Suspenso em " . now()->format('d/m/Y H:i') . 
                           ($reason ? ": {$reason}" : ''),
            ]);

            Log::info("Contrato de mensalidade suspenso", [
                'monthly_fee_id' => $monthlyFee->id,
                'reason' => $reason,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao suspender contrato de mensalidades", [
                'monthly_fee_id' => $monthlyFee->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reativar contrato suspenso
     */
    public function reactivateMonthlyFee(MonthlyFee $monthlyFee): bool
    {
        try {
            if ($monthlyFee->status !== 'suspended') {
                throw new \Exception("Apenas contratos suspensos podem ser reativados.");
            }

            $monthlyFee->update([
                'status' => 'active',
                'notes' => ($monthlyFee->notes ? $monthlyFee->notes . "\n" : '') . 
                           "Reativado em " . now()->format('d/m/Y H:i'),
            ]);

            Log::info("Contrato de mensalidade reativado", [
                'monthly_fee_id' => $monthlyFee->id,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Erro ao reativar contrato de mensalidades", [
                'monthly_fee_id' => $monthlyFee->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Calcular valores finais da parcela
     */
    public function calculateInstallmentAmount(MonthlyFeeInstallment $installment): float
    {
        $amount = $installment->base_amount;
        $amount -= $installment->sibling_discount;
        $amount -= $installment->punctuality_discount;
        $amount -= $installment->other_discounts;
        $amount += $installment->interest_amount;
        $amount += $installment->fine_amount;

        return max(0, $amount); // Garantir que não seja negativo
    }

    /**
     * Buscar serviço de mensalidade da turma
     */
    protected function findMonthlyClassroomService(Enrollment $enrollment): ?ClassroomService
    {
        if (!$enrollment->classroom_id) {
            return null;
        }

        return ClassroomService::where('classroom_id', $enrollment->classroom_id)
            ->whereHas('service', function($query) {
                $query->where('type', 'monthly');
            })
            ->first();
    }

    /**
     * Gerar número de contrato único
     */
    protected function generateContractNumber(Enrollment $enrollment, string|int|null $academicYear = null): string
    {
        $year = $academicYear ?? $enrollment->academic_year ?? now()->year;
        $year = (string) $year; // Garantir que seja string
        $enrollmentId = str_pad($enrollment->id, 4, '0', STR_PAD_LEFT);
        
        // Garantir unicidade: adicionar timestamp se necessário
        $contractNumber = "MF-{$year}-{$enrollmentId}";
        
        // Verificar se já existe (incluindo soft deletes)
        $exists = MonthlyFee::withTrashed()
            ->where('contract_number', $contractNumber)
            ->exists();
        
        if ($exists) {
            // Adicionar sufixo único baseado em timestamp
            $suffix = substr(time(), -4);
            $contractNumber = "MF-{$year}-{$enrollmentId}-{$suffix}";
        }
        
        return $contractNumber;
    }
}

