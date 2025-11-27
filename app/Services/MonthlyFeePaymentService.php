<?php

namespace App\Services;

use App\Models\MonthlyFeePayment;
use App\Models\MonthlyFeeInstallment;
use App\Models\Guardian;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MonthlyFeePaymentService
{
    /**
     * Processar pagamento de mensalidade
     */
    public function processPayment(MonthlyFeeInstallment $installment, array $data): MonthlyFeePayment
    {
        try {
            return DB::transaction(function () use ($installment, $data) {
                // Validar se parcela está disponível para pagamento
                if (!$installment->canBePaid()) {
                    $statusMessages = [
                        'paid' => 'Esta parcela já foi paga.',
                        'cancelled' => 'Esta parcela está cancelada.',
                        'waived' => 'Esta parcela foi dispensada/perdoada e não pode receber pagamento.',
                    ];
                    
                    $message = $statusMessages[$installment->status] ?? 'Esta parcela não está disponível para pagamento.';
                    throw new \Exception($message);
                }

                $paymentDate = isset($data['payment_date']) 
                    ? Carbon::parse($data['payment_date']) 
                    : Carbon::now();

                // Obter valor base atual do serviço
                $baseAmount = $installment->base_amount; // Accessor busca do serviço atual
                $siblingDiscount = $installment->sibling_discount; // Accessor busca do contrato
                
                // Calcular descontos e acréscimos
                $punctualityDiscount = $this->applyPunctualityDiscount($installment, $paymentDate);
                $lateFees = $this->calculateLateFees($installment, $paymentDate, $baseAmount);
                $otherDiscounts = (float) ($data['other_discounts'] ?? 0);
                
                // Permitir override de juros e multa se fornecido (para ajustes manuais)
                $interestApplied = isset($data['interest_override']) 
                    ? (float) $data['interest_override'] 
                    : $lateFees['interest'];
                $fineApplied = isset($data['fine_override']) 
                    ? (float) $data['fine_override'] 
                    : $lateFees['fine'];
                
                // Calcular valor final
                $finalAmount = $baseAmount 
                    - $siblingDiscount 
                    - $punctualityDiscount
                    - $otherDiscounts
                    + $interestApplied
                    + $fineApplied;

                // Criar registro de pagamento com todos os detalhes
                $payment = MonthlyFeePayment::create([
                    'monthly_fee_installment_id' => $installment->id,
                    'paid_by_guardian_id' => $data['paid_by_guardian_id'],
                    'payment_number' => $this->generatePaymentNumber(),
                    'amount' => $data['amount'] ?? max(0, $finalAmount),
                    'original_installment_amount' => $baseAmount,
                    'sibling_discount' => $siblingDiscount,
                    'punctuality_discount' => $punctualityDiscount,
                    'other_discounts' => $otherDiscounts,
                    'interest_applied' => $interestApplied,
                    'fine_applied' => $fineApplied,
                    'method' => $data['method'],
                    'payment_date' => $paymentDate,
                    'confirmation_date' => isset($data['auto_confirm']) && $data['auto_confirm'] ? $paymentDate : null,
                    'status' => isset($data['auto_confirm']) && $data['auto_confirm'] ? 'confirmed' : 'pending',
                    'reference' => $data['reference'] ?? null,
                    'transaction_id' => $data['transaction_id'] ?? null,
                    'notes' => $data['notes'] ?? null,
                ]);

                // Se confirmado automaticamente, atualizar status da parcela
                if ($payment->status === 'confirmed') {
                    $installment->status = 'paid';
                    $installment->save();
                }

                Log::info("Pagamento de mensalidade processado", [
                    'payment_id' => $payment->id,
                    'installment_id' => $installment->id,
                    'amount' => $payment->amount,
                ]);

                return $payment;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao processar pagamento de mensalidade", [
                'installment_id' => $installment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Aplicar desconto de pontualidade (se pagar antes do vencimento)
     */
    public function applyPunctualityDiscount(MonthlyFeeInstallment $installment, Carbon $paymentDate): float
    {
        $monthlyFee = $installment->monthlyFee;

        // Verificar se o contrato tem desconto de pontualidade habilitado
        if (!$monthlyFee->has_punctuality_discount) {
            return 0;
        }

        // Verificar se está pagando antes ou na data de vencimento
        if ($paymentDate->lte($installment->due_date)) {
            return $monthlyFee->punctuality_discount_amount;
        }

        return 0;
    }

    /**
     * Calcular juros e multa (se pagar depois do vencimento)
     */
    public function calculateLateFees(MonthlyFeeInstallment $installment, Carbon $paymentDate, float $baseAmount): array
    {
        $interest = 0;
        $fine = 0;

        // Se não está atrasado, retornar zeros
        if ($paymentDate->lte($installment->due_date)) {
            return ['interest' => 0, 'fine' => 0];
        }

        // Calcular dias de atraso
        $daysLate = $paymentDate->diffInDays($installment->due_date);

        // Juros: 1% ao mês (proporcional aos dias)
        $monthlyInterestRate = 0.01; // 1%
        $interest = ($baseAmount * $monthlyInterestRate * $daysLate) / 30;

        // Multa: 2% sobre o valor base (cobrada uma única vez)
        $fineRate = 0.02; // 2%
        $fine = $baseAmount * $fineRate;

        return [
            'interest' => round($interest, 2),
            'fine' => round($fine, 2),
        ];
    }

    /**
     * Confirmar pagamento
     */
    public function confirmPayment(MonthlyFeePayment $payment): bool
    {
        try {
            return DB::transaction(function () use ($payment) {
                if ($payment->status === 'confirmed') {
                    Log::warning("Tentativa de confirmar pagamento já confirmado", [
                        'payment_id' => $payment->id,
                    ]);
                    return true;
                }

                if ($payment->status === 'cancelled' || $payment->status === 'refunded') {
                    throw new \Exception("Não é possível confirmar um pagamento cancelado ou estornado.");
                }

                // Confirmar pagamento
                $payment->update([
                    'status' => 'confirmed',
                    'confirmation_date' => Carbon::now(),
                ]);

                // Atualizar status da parcela
                $installment = $payment->installment;
                $installment->update([
                    'status' => 'paid',
                ]);

                Log::info("Pagamento confirmado com sucesso", [
                    'payment_id' => $payment->id,
                    'installment_id' => $installment->id,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao confirmar pagamento", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Estornar pagamento (apenas confirmados)
     */
    public function refundPayment(MonthlyFeePayment $payment, string $reason = null): bool
    {
        try {
            return DB::transaction(function () use ($payment, $reason) {
                if ($payment->status !== 'confirmed') {
                    throw new \Exception("Apenas pagamentos confirmados podem ser estornados.");
                }

                // Estornar pagamento
                $payment->update([
                    'status' => 'refunded',
                    'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 
                               "Estornado em " . now()->format('d/m/Y H:i') . 
                               ($reason ? ": {$reason}" : ''),
                ]);

                // Reverter status da parcela
                $installment = $payment->installment;
                
                // Verificar se está vencida (comparar apenas datas, não horários)
                $newStatus = Carbon::today()->gt($installment->due_date) ? 'overdue' : 'pending';
                
                $installment->update([
                    'status' => $newStatus,
                ]);

                Log::info("Pagamento estornado com sucesso", [
                    'payment_id' => $payment->id,
                    'installment_id' => $installment->id,
                    'reason' => $reason,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao estornar pagamento", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Reverter pagamento (confirmado ou pendente)
     * Remove o pagamento e restaura a mensalidade ao estado anterior
     */
    public function revertPayment(MonthlyFeePayment $payment, ?string $reason = null): bool
    {
        try {
            return DB::transaction(function () use ($payment, $reason) {
                // Validar se o pagamento pode ser revertido
                if ($payment->status === 'cancelled') {
                    throw new \Exception("Não é possível reverter um pagamento cancelado.");
                }

                if ($payment->status === 'refunded') {
                    throw new \Exception("Este pagamento já foi revertido anteriormente.");
                }

                $installment = $payment->installment;
                $wasConfirmed = $payment->status === 'confirmed';

                // Marcar pagamento como revertido
                $payment->update([
                    'status' => 'refunded',
                    'notes' => ($payment->notes ? $payment->notes . "\n" : '') . 
                               "Revertido em " . now()->format('d/m/Y H:i') . 
                               ($reason ? ": {$reason}" : ''),
                ]);

                // Verificar se há outros pagamentos confirmados para esta parcela
                $hasOtherConfirmedPayments = $installment->payments()
                    ->where('id', '!=', $payment->id)
                    ->where('status', 'confirmed')
                    ->exists();

                // Se havia outros pagamentos confirmados, manter status como 'paid'
                // Caso contrário, restaurar ao estado anterior
                if (!$hasOtherConfirmedPayments && $wasConfirmed) {
                    // Verificar se está vencida (comparar apenas datas, não horários)
                    $newStatus = Carbon::today()->gt($installment->due_date) ? 'overdue' : 'pending';
                    
                    $installment->update([
                        'status' => $newStatus,
                    ]);
                }

                Log::info("Pagamento revertido com sucesso", [
                    'payment_id' => $payment->id,
                    'installment_id' => $installment->id,
                    'was_confirmed' => $wasConfirmed,
                    'new_installment_status' => $installment->status,
                    'reason' => $reason,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao reverter pagamento", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Registrar pagamento parcial
     */
    public function registerPartialPayment(MonthlyFeeInstallment $installment, float $amount, array $data = []): MonthlyFeePayment
    {
        try {
            if ($amount <= 0) {
                throw new \Exception("Valor do pagamento parcial deve ser maior que zero.");
            }

            $finalAmount = $installment->final_amount; // Accessor calcula dinamicamente
            if ($amount >= $finalAmount) {
                throw new \Exception("Para pagamento total, use o método processPayment.");
            }

            $data['amount'] = $amount;
            // Pagamentos parciais nunca devem ser auto-confirmados
            $data['auto_confirm'] = false;

            $payment = $this->processPayment($installment, $data);

            Log::info("Pagamento parcial registrado", [
                'payment_id' => $payment->id,
                'installment_id' => $installment->id,
                'amount' => $amount,
            ]);

            return $payment;
        } catch (\Exception $e) {
            Log::error("Erro ao registrar pagamento parcial", [
                'installment_id' => $installment->id,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Editar pagamento existente
     */
    public function updatePayment(MonthlyFeePayment $payment, array $data): MonthlyFeePayment
    {
        try {
            return DB::transaction(function () use ($payment, $data) {
                // Validar se o pagamento pode ser editado
                if ($payment->status === 'refunded') {
                    throw new \Exception("Não é possível editar um pagamento estornado.");
                }

                // Se o pagamento está confirmado, permitir edição apenas de campos específicos
                // (não permitir alterar valores se já foi confirmado, apenas dados complementares)
                $allowedFields = [];
                
                if ($payment->status === 'confirmed') {
                    // Se confirmado, permitir editar apenas campos não financeiros
                    $allowedFields = ['reference', 'transaction_id', 'notes'];
                    
                    // Verificar se está tentando alterar campos financeiros
                    $financialFields = ['amount', 'original_installment_amount', 'sibling_discount', 
                                      'punctuality_discount', 'other_discounts', 'interest_applied', 
                                      'fine_applied', 'method', 'payment_date'];
                    
                    $hasFinancialChanges = false;
                    foreach ($financialFields as $field) {
                        if (isset($data[$field])) {
                            $newValue = $data[$field];
                            $oldValue = $payment->$field;
                            
                            // Para campos numéricos, normalizar tipos antes de comparar
                            // Isso evita que valores numericamente iguais sejam considerados diferentes
                            // devido a diferenças de tipo (string vs float)
                            if (in_array($field, ['amount', 'original_installment_amount', 'sibling_discount', 
                                                  'punctuality_discount', 'other_discounts', 'interest_applied', 
                                                  'fine_applied'])) {
                                $newValue = (float) $newValue;
                                $oldValue = (float) $oldValue;
                            }
                            
                            // Para datas, comparar como strings formatadas
                            if (in_array($field, ['payment_date']) && $newValue && $oldValue) {
                                $newValue = is_string($newValue) ? $newValue : Carbon::parse($newValue)->format('Y-m-d H:i:s');
                                $oldValue = $oldValue instanceof \DateTime ? $oldValue->format('Y-m-d H:i:s') : (string) $oldValue;
                            }
                            
                            if ($newValue !== $oldValue) {
                                $hasFinancialChanges = true;
                                break;
                            }
                        }
                    }
                    
                    if ($hasFinancialChanges) {
                        throw new \Exception("Não é possível alterar valores financeiros de um pagamento confirmado. Estorne o pagamento primeiro para fazer alterações.");
                    }
                } else {
                    // Se não confirmado, permitir editar todos os campos
                    $allowedFields = [
                        'paid_by_guardian_id',
                        'amount',
                        'original_installment_amount',
                        'sibling_discount',
                        'punctuality_discount',
                        'other_discounts',
                        'interest_applied',
                        'fine_applied',
                        'method',
                        'payment_date',
                        'reference',
                        'transaction_id',
                        'notes',
                    ];
                }

                // Filtrar apenas campos permitidos
                $updateData = array_intersect_key($data, array_flip($allowedFields));

                // Se está alterando a data de pagamento, recalcular juros e multa se necessário
                if (isset($updateData['payment_date']) && $payment->status !== 'confirmed') {
                    $paymentDate = Carbon::parse($updateData['payment_date']);
                    $installment = $payment->installment;
                    
                    // Recalcular juros e multa baseado na nova data
                    $lateFees = $this->calculateLateFees($installment, $paymentDate, $payment->original_installment_amount);
                    
                    // Se não foram fornecidos valores de juros/multa, usar os recalculados
                    if (!isset($updateData['interest_applied'])) {
                        $updateData['interest_applied'] = $lateFees['interest'];
                    }
                    if (!isset($updateData['fine_applied'])) {
                        $updateData['fine_applied'] = $lateFees['fine'];
                    }
                    
                    // Recalcular desconto de pontualidade
                    if (!isset($updateData['punctuality_discount'])) {
                        $updateData['punctuality_discount'] = $this->applyPunctualityDiscount($installment, $paymentDate);
                    }
                    
                    // Recalcular valor final se necessário
                    if (!isset($updateData['amount'])) {
                        $baseAmount = $updateData['original_installment_amount'] ?? $payment->original_installment_amount;
                        $siblingDiscount = $updateData['sibling_discount'] ?? $payment->sibling_discount ?? 0;
                        // Garantir que punctuality_discount existe antes de usar
                        $punctualityDiscount = $updateData['punctuality_discount'] ?? $payment->punctuality_discount ?? 0;
                        $otherDiscounts = $updateData['other_discounts'] ?? $payment->other_discounts ?? 0;
                        // Garantir que interest_applied e fine_applied existem
                        $interest = $updateData['interest_applied'] ?? $payment->interest_applied ?? 0;
                        $fine = $updateData['fine_applied'] ?? $payment->fine_applied ?? 0;
                        
                        $updateData['amount'] = max(0, 
                            $baseAmount 
                            - $siblingDiscount 
                            - $punctualityDiscount
                            - $otherDiscounts
                            + $interest
                            + $fine
                        );
                    }
                }

                // Atualizar pagamento
                $payment->update($updateData);

                Log::info("Pagamento editado com sucesso", [
                    'payment_id' => $payment->id,
                    'updated_fields' => array_keys($updateData),
                ]);

                return $payment->fresh(['installment', 'guardian']);
            });
        } catch (\Exception $e) {
            Log::error("Erro ao editar pagamento", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Gerar recibo de pagamento
     */
    public function generateReceipt(MonthlyFeePayment $payment): string
    {
        // Gerar número de recibo único
        $receiptNumber = 'REC-' . $payment->payment_number;

        // Aqui você pode implementar a lógica para gerar PDF do recibo
        // usando DomPDF ou outra biblioteca
        
        Log::info("Recibo gerado", [
            'payment_id' => $payment->id,
            'receipt_number' => $receiptNumber,
        ]);

        return $receiptNumber;
    }

    /**
     * Gerar número de pagamento único
     * Usa lock pessimista para evitar race conditions em requisições concorrentes
     */
    protected function generatePaymentNumber(): string
    {
        $year = now()->year;
        
        // Lock pessimista para prevenir race condition
        $lastPayment = MonthlyFeePayment::whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        $nextNumber = $lastPayment ? intval(substr($lastPayment->payment_number, -5)) + 1 : 1;
        
        return 'PAY-' . $year . '-' . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

