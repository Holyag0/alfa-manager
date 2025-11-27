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
                $replaceExisting = $data['replace_existing'] ?? false;
                $addToExisting = $data['add_to_existing'] ?? false;
                
                $existingFee = MonthlyFee::where('enrollment_id', $enrollment->id)
                    ->where('academic_year', $academicYear)
                    ->whereNull('deleted_at') // Apenas contratos não deletados
                    ->first();

                if ($existingFee) {
                    if ($addToExisting) {
                        // Adicionar parcelas ao contrato existente
                        Log::info("Adicionando parcelas ao contrato existente", [
                            'enrollment_id' => $enrollment->id,
                            'monthly_fee_id' => $existingFee->id,
                            'academic_year' => $academicYear,
                        ]);
                        
                        // Calcular período de mensalidades
                        $startMonth = $data['start_month'] ?? 1;
                        $endMonth = $data['end_month'] ?? 12;
                        
                        // Gerar parcelas adicionais (evitando duplicatas)
                        $this->generateInstallments($existingFee, $startMonth, $endMonth, true);
                        
                        // Atualizar total de parcelas
                        $existingFee->refresh();
                        $existingFee->total_installments = $existingFee->installments()->whereNull('deleted_at')->count();
                        $existingFee->save();
                        
                        return $existingFee;
                    }
                    
                    if (!$replaceExisting) {
                        // Se não foi solicitada substituição nem adição, retornar o existente
                        Log::info("Contrato de mensalidade já existe", [
                            'enrollment_id' => $enrollment->id,
                            'monthly_fee_id' => $existingFee->id
                        ]);
                        return $existingFee;
                    }
                    
                    // Se foi solicitada substituição, deletar apenas as parcelas no intervalo selecionado
                    $startMonth = $data['start_month'] ?? 1;
                    $endMonth = $data['end_month'] ?? 12;
                    
                    Log::info("Substituindo mensalidades no intervalo", [
                        'enrollment_id' => $enrollment->id,
                        'monthly_fee_id' => $existingFee->id,
                        'academic_year' => $academicYear,
                        'start_month' => $startMonth,
                        'end_month' => $endMonth,
                    ]);
                    
                    // Deletar apenas parcelas no intervalo selecionado (soft delete)
                    // IMPORTANTE: Precisamos fazer hard delete para liberar os números de parcela
                    // para a constraint de unicidade, já que soft delete mantém os registros
                    $installmentsToDelete = $existingFee->installments()
                        ->whereNull('deleted_at')
                        ->get()
                        ->filter(function ($installment) use ($academicYear, $startMonth, $endMonth) {
                            $refMonth = $installment->reference_month;
                            if (!$refMonth) return false;
                            
                            $monthStr = explode('-', $refMonth)[1] ?? null;
                            if (!$monthStr) return false;
                            
                            $month = (int) $monthStr;
                            return $month >= $startMonth && $month <= $endMonth;
                        });
                    
                    // Fazer hard delete para liberar os números de parcela para a constraint
                    foreach ($installmentsToDelete as $installment) {
                        // Verificar se tem pagamentos confirmados antes de fazer hard delete
                        $hasConfirmedPayment = $installment->payments()
                            ->where('status', 'confirmed')
                            ->exists();
                        
                        if ($hasConfirmedPayment) {
                            // Se tem pagamento confirmado, fazer soft delete e pular este mês na geração
                            Log::warning('Mensalidade com pagamento confirmado não pode ser substituída', [
                                'installment_id' => $installment->id,
                                'reference_month' => $installment->reference_month,
                            ]);
                            $installment->delete(); // Soft delete
                        } else {
                            // Sem pagamento confirmado, fazer hard delete para liberar o número
                            $installment->forceDelete();
                        }
                    }
                    
                    // Não deletar o contrato, apenas as parcelas no intervalo
                    // O contrato será atualizado com as novas parcelas
                    // Usar o contrato existente
                    $monthlyFee = $existingFee;
                    
                    // Atualizar configurações do contrato se necessário
                    if (isset($data['due_day'])) {
                        $monthlyFee->due_day = $data['due_day'];
                    }
                    if (isset($data['has_punctuality_discount'])) {
                        $monthlyFee->has_punctuality_discount = (bool) $data['has_punctuality_discount'];
                    }
                    if (isset($data['punctuality_discount_amount'])) {
                        $monthlyFee->punctuality_discount_amount = (float) $data['punctuality_discount_amount'];
                    }
                    if (isset($data['notes'])) {
                        $monthlyFee->notes = $data['notes'];
                    }
                    $monthlyFee->save();
                } else {
                    // Criar novo contrato apenas se não existir
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
                }

                // Calcular período de mensalidades (se ainda não foi calculado)
                $startMonth = $data['start_month'] ?? 1;
                $endMonth = $data['end_month'] ?? 12;
                
                // Se foi substituição, usar skipExisting=false para criar novas mensalidades
                // mesmo que existam mensalidades deletadas (soft delete) no intervalo
                // Se foi adição, usar skipExisting=true para pular meses que já têm mensalidades
                $skipExisting = $addToExisting ?? false;
                
                // Gerar parcelas automaticamente baseado no período
                $this->generateInstallments($monthlyFee, $startMonth, $endMonth, $skipExisting);
                
                // Atualizar total de parcelas
                $monthlyFee->refresh();
                $monthlyFee->total_installments = $monthlyFee->installments()->whereNull('deleted_at')->count();
                $monthlyFee->save();

                Log::info("Contrato de mensalidade criado/atualizado com sucesso", [
                    'monthly_fee_id' => $monthlyFee->id,
                    'enrollment_id' => $enrollment->id,
                    'replaced' => $replaceExisting,
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
     * @param bool $skipExisting Se true, pula meses que já têm parcelas criadas
     */
    public function generateInstallments(MonthlyFee $monthlyFee, int $startMonth = 1, int $endMonth = 12, bool $skipExisting = false): Collection
    {
        $installments = collect();
        
        // Determinar número inicial da parcela
        if ($skipExisting) {
            // Se está adicionando, começar do último número + 1
            $lastInstallment = $monthlyFee->installments()
                ->whereNull('deleted_at')
                ->orderBy('installment_number', 'desc')
                ->first();
            $installmentNumber = $lastInstallment ? $lastInstallment->installment_number + 1 : 1;
            } else {
                // Se está substituindo ou criando novo, verificar se há parcelas existentes
                // Para substituição parcial, buscar a última mensalidade ANTES do intervalo, não a última global
                $startMonthReference = Carbon::create($monthlyFee->academic_year, $startMonth, 1)->format('Y-m');
                
                // Buscar última mensalidade ANTES do intervalo a ser substituído
                $lastInstallmentBeforeRange = $monthlyFee->installments()
                    ->whereNull('deleted_at')
                    ->where('reference_month', '<', $startMonthReference)
                    ->orderBy('installment_number', 'desc')
                    ->first();
                
                if ($lastInstallmentBeforeRange) {
                    // Há parcelas antes do intervalo, continuar numeração a partir da última antes do intervalo
                    $installmentNumber = $lastInstallmentBeforeRange->installment_number + 1;
                } else {
                    // Não há parcelas antes, verificar se há mensalidades deletadas no intervalo
                    // Se houver, reutilizar os números; se não, começar do 1
                    // Como fizemos hard delete das mensalidades sem pagamento confirmado,
                    // vamos verificar se há soft deleted (com pagamento confirmado) no intervalo
                    $firstSoftDeleted = $monthlyFee->installments()
                        ->withTrashed()
                        ->whereNotNull('deleted_at')
                        ->whereBetween('reference_month', [
                            Carbon::create($monthlyFee->academic_year, $startMonth, 1)->format('Y-m'),
                            Carbon::create($monthlyFee->academic_year, $endMonth, 1)->format('Y-m')
                        ])
                        ->orderBy('installment_number', 'asc')
                        ->first();
                    
                    if ($firstSoftDeleted) {
                        // Reutilizar o número da primeira mensalidade soft deleted
                        $installmentNumber = $firstSoftDeleted->installment_number;
                    } else {
                        // Não há mensalidades deletadas, começar do 1
                        $installmentNumber = 1;
                    }
                }
            }

        for ($month = $startMonth; $month <= $endMonth; $month++) {
            $referenceMonth = Carbon::create($monthlyFee->academic_year, $month, 1)->format('Y-m');
            
            // Se está adicionando e já existe parcela para este mês, pular
            if ($skipExisting) {
                $existingInstallment = $monthlyFee->installments()
                    ->where('reference_month', $referenceMonth)
                    ->whereNull('deleted_at')
                    ->first();
                
                if ($existingInstallment) {
                    // Não incrementar aqui - o número já está correto para a próxima parcela a ser criada
                    continue; // Pular este mês, já tem parcela
                }
            } else {
                // Se está substituindo, verificar se há mensalidade deletada (soft delete) para este mês
                // Se houver e tiver pagamento confirmado, pular este mês
                $deletedInstallment = $monthlyFee->installments()
                    ->withTrashed()
                    ->where('reference_month', $referenceMonth)
                    ->whereNotNull('deleted_at')
                    ->first();
                
                if ($deletedInstallment) {
                    $hasConfirmedPayment = $deletedInstallment->payments()
                        ->where('status', 'confirmed')
                        ->exists();
                    
                    if ($hasConfirmedPayment) {
                        // Pular este mês se tem pagamento confirmado
                        // Não incrementar aqui - o número já está correto para a próxima parcela a ser criada
                        Log::info('Pulando mês com mensalidade deletada que tem pagamento confirmado', [
                            'reference_month' => $referenceMonth,
                            'installment_id' => $deletedInstallment->id,
                        ]);
                        continue;
                    }
                }
            }
            
            // Ajustar due_day para evitar overflow em meses com menos dias
            $daysInMonth = Carbon::create($monthlyFee->academic_year, $month, 1)->daysInMonth;
            $adjustedDay = min($monthlyFee->due_day, $daysInMonth);
            $dueDate = Carbon::create($monthlyFee->academic_year, $month, $adjustedDay);
            
            // Criar parcela apenas com dados de relacionamento
            // O valor será sempre buscado do serviço atual (classroom_service.service.price)
            $installment = MonthlyFeeInstallment::create([
                'monthly_fee_id' => $monthlyFee->id,
                'classroom_service_id' => $monthlyFee->classroom_service_id, // ← Referência ao serviço
                'reference_month' => $referenceMonth,
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
            'skip_existing' => $skipExisting,
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
     * Trocar serviço de uma mensalidade individual
     */
    public function changeInstallmentService(MonthlyFeeInstallment $installment, int $newClassroomServiceId, ?string $reason = null): MonthlyFeeInstallment
    {
        try {
            return DB::transaction(function () use ($installment, $newClassroomServiceId, $reason) {
                // Validar se o novo serviço existe e é do tipo mensalidade
                $newService = ClassroomService::with('service')->findOrFail($newClassroomServiceId);
                
                if (!$newService->service || $newService->service->type !== 'monthly') {
                    throw new \Exception("O serviço selecionado não é do tipo mensalidade.");
                }

                // Validar se a parcela pode ter o serviço trocado
                // Não permitir trocar se já foi paga (a menos que seja estornada)
                if ($installment->status === 'paid') {
                    $hasConfirmedPayment = $installment->payments()
                        ->where('status', 'confirmed')
                        ->exists();
                    
                    if ($hasConfirmedPayment) {
                        throw new \Exception("Não é possível trocar o serviço de uma parcela com pagamento confirmado. Estorne o pagamento primeiro.");
                    }
                }

                $oldServiceId = $installment->classroom_service_id;
                
                // Atualizar o serviço da parcela
                $installment->update([
                    'classroom_service_id' => $newClassroomServiceId,
                ]);

                Log::info("Serviço de mensalidade trocado (individual)", [
                    'installment_id' => $installment->id,
                    'old_service_id' => $oldServiceId,
                    'new_service_id' => $newClassroomServiceId,
                    'reason' => $reason,
                ]);

                return $installment->fresh(['classroomService.service']);
            });
        } catch (\Exception $e) {
            Log::error("Erro ao trocar serviço de mensalidade (individual)", [
                'installment_id' => $installment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Pré-visualizar quais parcelas serão alteradas na troca de serviço em lote
     */
    public function previewChangeAllInstallmentsService(MonthlyFee $monthlyFee, int $newClassroomServiceId): array
    {
        // Validar se o novo serviço existe e é do tipo mensalidade
        $newService = ClassroomService::with('service')->findOrFail($newClassroomServiceId);
        
        if (!$newService->service || $newService->service->type !== 'monthly') {
            throw new \Exception("O serviço selecionado não é do tipo mensalidade.");
        }

        // Buscar todas as parcelas do contrato com seus pagamentos
        $installments = $monthlyFee->installments()->with(['payments'])->get();
        
        if ($installments->isEmpty()) {
            throw new \Exception("Nenhuma parcela encontrada para este contrato.");
        }

        $canUpdate = [];
        $cannotUpdate = [];

        foreach ($installments as $installment) {
            $hasConfirmedPayment = $installment->payments()
                ->where('status', 'confirmed')
                ->exists();

            if ($hasConfirmedPayment) {
                $cannotUpdate[] = [
                    'id' => $installment->id,
                    'reference_month' => $installment->reference_month,
                    'installment_number' => $installment->installment_number,
                    'status' => $installment->status,
                    'reason' => 'Possui pagamento(s) confirmado(s)',
                ];
            } else {
                $canUpdate[] = [
                    'id' => $installment->id,
                    'reference_month' => $installment->reference_month,
                    'installment_number' => $installment->installment_number,
                    'status' => $installment->status,
                ];
            }
        }

        return [
            'can_update' => $canUpdate,
            'cannot_update' => $cannotUpdate,
            'total_installments' => $installments->count(),
            'will_update' => count($canUpdate),
            'will_skip' => count($cannotUpdate),
            'new_service' => [
                'id' => $newService->id,
                'name' => $newService->service->name,
                'price' => $newService->price,
            ],
        ];
    }

    /**
     * Trocar serviço de todas as mensalidades de um ano letivo
     * Ignora parcelas com pagamentos confirmados
     */
    public function changeAllInstallmentsService(MonthlyFee $monthlyFee, int $newClassroomServiceId, ?string $reason = null): array
    {
        try {
            return DB::transaction(function () use ($monthlyFee, $newClassroomServiceId, $reason) {
                // Validar se o novo serviço existe e é do tipo mensalidade
                $newService = ClassroomService::with('service')->findOrFail($newClassroomServiceId);
                
                if (!$newService->service || $newService->service->type !== 'monthly') {
                    throw new \Exception("O serviço selecionado não é do tipo mensalidade.");
                }

                // Buscar todas as parcelas do contrato com seus pagamentos
                $installments = $monthlyFee->installments()->with(['payments'])->get();
                
                if ($installments->isEmpty()) {
                    throw new \Exception("Nenhuma parcela encontrada para este contrato.");
                }

                $oldServiceId = $monthlyFee->classroom_service_id;
                $updated = [];
                $skipped = [];

                // Atualizar apenas parcelas sem pagamentos confirmados
                foreach ($installments as $installment) {
                    $hasConfirmedPayment = $installment->payments()
                        ->where('status', 'confirmed')
                        ->exists();

                    if ($hasConfirmedPayment) {
                        $skipped[] = [
                            'id' => $installment->id,
                            'reference_month' => $installment->reference_month,
                            'installment_number' => $installment->installment_number,
                            'reason' => 'Possui pagamento(s) confirmado(s)',
                        ];
                    } else {
                        $installment->update([
                            'classroom_service_id' => $newClassroomServiceId,
                        ]);
                        $updated[] = [
                            'id' => $installment->id,
                            'reference_month' => $installment->reference_month,
                            'installment_number' => $installment->installment_number,
                        ];
                    }
                }

                // Atualizar também o serviço do contrato (para referência)
                // Apenas se pelo menos uma parcela foi atualizada
                if (count($updated) > 0) {
                    $monthlyFee->update([
                        'classroom_service_id' => $newClassroomServiceId,
                        'base_amount' => $newService->price,
                        'notes' => ($monthlyFee->notes ? $monthlyFee->notes . "\n" : '') . 
                                   "Serviço trocado em " . now()->format('d/m/Y H:i') . 
                                   " de {$oldServiceId} para {$newClassroomServiceId}" .
                                   ($reason ? ": {$reason}" : '') .
                                   (count($skipped) > 0 ? " (Ignoradas " . count($skipped) . " parcela(s) com pagamentos confirmados)" : ''),
                    ]);
                }

                Log::info("Serviço de mensalidade trocado (em lote)", [
                    'monthly_fee_id' => $monthlyFee->id,
                    'old_service_id' => $oldServiceId,
                    'new_service_id' => $newClassroomServiceId,
                    'updated_installments' => count($updated),
                    'skipped_installments' => count($skipped),
                    'reason' => $reason,
                ]);

                return [
                    'updated' => $monthlyFee->installments()
                        ->whereIn('id', array_column($updated, 'id'))
                        ->with(['classroomService.service'])
                        ->get(),
                    'skipped' => $skipped,
                    'summary' => [
                        'total' => $installments->count(),
                        'updated' => count($updated),
                        'skipped' => count($skipped),
                    ],
                ];
            });
        } catch (\Exception $e) {
            Log::error("Erro ao trocar serviço de mensalidade (em lote)", [
                'monthly_fee_id' => $monthlyFee->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
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

