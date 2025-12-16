<?php

namespace App\Services;

use App\Models\FinancialTransaction;
use App\Models\FinancialCategory;
use App\Models\MonthlyFeePayment;
use App\Models\EnrollmentPayment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinancialService
{
    /**
     * Registrar receita de pagamento de mensalidade
     */
    public function registerMonthlyFeeRevenue(MonthlyFeePayment $payment): ?FinancialTransaction
    {
        try {
            // Apenas registrar se o pagamento foi confirmado
            if ($payment->status !== 'confirmed') {
                return null;
            }

            // Buscar categoria padrão de mensalidade
            $category = FinancialCategory::where('type', 'receita')
                ->where('name', 'Mensalidade')
                ->first();

            if (!$category) {
                Log::warning("Categoria 'Mensalidade' não encontrada. Criando categoria padrão.");
                $category = FinancialCategory::create([
                    'name' => 'Mensalidade',
                    'type' => 'receita',
                    'description' => 'Pagamentos de mensalidades escolares',
                    'color' => '#10B981',
                    'is_active' => true,
                ]);
            }

            $installment = $payment->installment;
            $enrollment = $installment?->monthlyFee?->enrollment;
            $student = $enrollment?->student;
            $classroom = $enrollment?->classroom;
            
            // Buscar responsável que pagou
            $guardian = $payment->guardian;
            
            // Armazenar referência do serviço para usar na closure
            $service = $this;

            return DB::transaction(function () use ($service, $payment, $category, $student, $installment, $guardian, $classroom) {
                // Verificar duplicatas dentro da transação com lock para prevenir race condition
                $existingTransaction = FinancialTransaction::where('source_type', MonthlyFeePayment::class)
                    ->where('source_id', $payment->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingTransaction) {
                    Log::info("Transação financeira já existe para pagamento de mensalidade", [
                        'payment_id' => $payment->id,
                        'transaction_id' => $existingTransaction->id,
                    ]);
                    return $existingTransaction;
                }

                $studentName = $student?->name ?? 'N/A';
                $month = $installment?->reference_month ?? 'N/A';
                $classroomName = $classroom?->name ?? '';
                
                // Montar descrição com turma
                $description = "Mensalidade - {$studentName} - {$month}";
                if ($classroomName) {
                    $description .= " - {$classroomName}";
                }
                
                // Tentar criar transação com número único, re-gerando em caso de colisão de UNIQUE
                $maxAttempts = 3;
                $attempt = 0;
                $transaction = null;

                $transactionNumber = null;
                while ($attempt < $maxAttempts && !$transaction) {
                    $attempt++;
                    // Na primeira tentativa, usar o gerador padrão
                    // Em colisões, vamos apenas adicionar um sufixo aleatório ao último número gerado
                    if ($attempt === 1 || !$transactionNumber) {
                        $transactionNumber = $service->generateTransactionNumber('REC');
                    } else {
                        $transactionNumber = $transactionNumber . '-' . random_int(1000, 9999);
                    }

                    try {
                        $transaction = FinancialTransaction::create([
                            'transaction_number' => $transactionNumber,
                            'type' => 'receita',
                            'category_id' => $category->id,
                            'source_type' => MonthlyFeePayment::class,
                            'source_id' => $payment->id,
                            'description' => $description,
                            'amount' => $payment->amount,
                            'transaction_date' => $payment->payment_date,
                            'payment_method' => $payment->method,
                            'reference' => $payment->reference,
                            'notes' => $payment->notes,
                            'status' => 'confirmed',
                            // Usar confirmation_date se disponível, caso contrário usar payment_date para manter integridade temporal
                            'confirmed_at' => $payment->confirmation_date ?? $payment->payment_date ?? now(),
                            'confirmed_by' => auth()->id(),
                            'payer_name' => $guardian?->name ?? null,
                            'payer_document' => $guardian?->cpf ?? null,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        // Em caso de colisão de UNIQUE no transaction_number, tentar novamente com outro número
                        if (str_contains($e->getMessage(), 'Duplicate entry')
                            && str_contains($e->getMessage(), 'financial_transactions_transaction_number_unique')) {
                            Log::warning('Colisão de número de transação ao registrar receita de mensalidade. Gerando novo número.', [
                                'attempt' => $attempt,
                                'payment_id' => $payment->id,
                                'transaction_number' => $transactionNumber,
                            ]);
                            $transaction = null;
                            continue;
                        }

                        throw $e;
                    }
                }

                if (!$transaction) {
                    throw new \Exception('Não foi possível gerar um número único de transação para a receita de mensalidade.');
                }

                Log::info("Receita de mensalidade registrada", [
                    'transaction_id' => $transaction->id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao registrar receita de mensalidade", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Registrar receita de pagamento de matrícula/serviço
     */
    public function registerEnrollmentRevenue(EnrollmentPayment $payment): ?FinancialTransaction
    {
        try {
            // Apenas registrar se o pagamento foi confirmado
            if ($payment->status !== 'confirmed') {
                return null;
            }

            // Categoria fixa para quaisquer pagamentos vinculados à matrícula:
            // todas as receitas de EnrollmentPayment serão agrupadas como "Matrículas"
            $categoryName = 'Matrículas';

            $category = FinancialCategory::where('type', 'receita')
                ->where('name', $categoryName)
                ->first();

            if (!$category) {
                Log::warning("Categoria '{$categoryName}' não encontrada. Criando categoria padrão.");
                $category = FinancialCategory::create([
                    'name' => $categoryName,
                    'type' => 'receita',
                    'description' => "Pagamentos de serviços e mensalidades de matrícula",
                    'color' => '#3B82F6',
                    'is_active' => true,
                ]);
            }

            $enrollment = $payment->enrollment;
            $student = $enrollment?->student;
            $classroom = $enrollment?->classroom;
            $invoice = $payment->invoice;
            
            // Buscar responsável que pagou (se disponível)
            $guardian = $payment->paidByGuardian ?? $enrollment?->student?->guardians?->first();
            
            // Armazenar referência do serviço para usar na closure
            $service = $this;

            return DB::transaction(function () use ($service, $payment, $category, $student, $guardian, $invoice, $classroom) {
                // Verificar duplicatas dentro da transação para prevenir race condition
                $existingTransaction = FinancialTransaction::where('source_type', EnrollmentPayment::class)
                    ->where('source_id', $payment->id)
                    ->lockForUpdate()
                    ->first();

                if ($existingTransaction) {
                    Log::info("Transação financeira já existe para pagamento de matrícula", [
                        'payment_id' => $payment->id,
                        'transaction_id' => $existingTransaction->id,
                    ]);
                    return $existingTransaction;
                }
                // Definir descrição com:
                // - Nome completo do serviço (da fatura, se disponível)
                // - Nome do aluno
                // - Nome da turma
                $serviceDescription = $invoice?->description ?: $payment->description;
                $studentName = $student?->name;
                $classroomName = $classroom?->name;
                $description = $serviceDescription ?: 'Pagamento de matrícula';
                if ($studentName) {
                    $description .= " - {$studentName}";
                }
                if ($classroomName) {
                    $description .= " - {$classroomName}";
                }

                // Tentar criar transação com número único, re-gerando em caso de colisão de UNIQUE
                $maxAttempts = 3;
                $attempt = 0;
                $transaction = null;

                $transactionNumber = null;
                while ($attempt < $maxAttempts && !$transaction) {
                    $attempt++;
                    if ($attempt === 1 || !$transactionNumber) {
                        $transactionNumber = $service->generateTransactionNumber('REC');
                    } else {
                        $transactionNumber = $transactionNumber . '-' . random_int(1000, 9999);
                    }

                    try {
                        $transaction = FinancialTransaction::create([
                            'transaction_number' => $transactionNumber,
                            'type' => 'receita',
                            'category_id' => $category->id,
                            'source_type' => EnrollmentPayment::class,
                            'source_id' => $payment->id,
                            'description' => $description,
                            'amount' => $payment->amount,
                            'transaction_date' => $payment->payment_date,
                            'payment_method' => $payment->method,
                            'reference' => $payment->reference,
                            'notes' => $payment->notes,
                            'status' => 'confirmed',
                            // Usar payment_date para manter integridade temporal (EnrollmentPayment não tem confirmation_date)
                            'confirmed_at' => $payment->payment_date ?? now(),
                            'confirmed_by' => auth()->id(),
                            'payer_name' => $guardian?->name ?? null,
                            'payer_document' => $guardian?->cpf ?? null,
                        ]);
                    } catch (\Illuminate\Database\QueryException $e) {
                        if (str_contains($e->getMessage(), 'Duplicate entry')
                            && str_contains($e->getMessage(), 'financial_transactions_transaction_number_unique')) {
                            Log::warning('Colisão de número de transação ao registrar receita de matrícula. Gerando novo número.', [
                                'attempt' => $attempt,
                                'payment_id' => $payment->id,
                                'transaction_number' => $transactionNumber,
                            ]);
                            $transaction = null;
                            continue;
                        }

                        throw $e;
                    }
                }

                if (!$transaction) {
                    throw new \Exception('Não foi possível gerar um número único de transação para a receita de matrícula.');
                }

                Log::info("Receita de matrícula/serviço registrada", [
                    'transaction_id' => $transaction->id,
                    'payment_id' => $payment->id,
                    'amount' => $payment->amount,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao registrar receita de matrícula", [
                'payment_id' => $payment->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Registrar despesa manual
     */
    public function registerExpense(array $data): FinancialTransaction
    {
        try {
            // Armazenar referência do serviço para usar na closure
            $service = $this;
            
            return DB::transaction(function () use ($service, $data) {
                $transaction = FinancialTransaction::create([
                    'transaction_number' => $service->generateTransactionNumber('DESP'),
                    'type' => 'despesa',
                    'category_id' => $data['category_id'],
                    'description' => $data['description'],
                    'amount' => $data['amount'],
                    'transaction_date' => $data['transaction_date'] ?? now(),
                    'payment_method' => $data['payment_method'] ?? null,
                    'reference' => $data['reference'] ?? null,
                    'notes' => $data['notes'] ?? null,
                    'status' => $data['status'] ?? 'confirmed',
                    'confirmed_at' => ($data['status'] ?? 'confirmed') === 'confirmed' ? now() : null,
                    'confirmed_by' => ($data['status'] ?? 'confirmed') === 'confirmed' ? auth()->id() : null,
                    'payee_name' => $data['payee_name'] ?? null,
                    'payee_document' => $data['payee_document'] ?? null,
                    'payee_supplier_id' => $data['payee_supplier_id'] ?? null,
                ]);

                Log::info("Despesa registrada", [
                    'transaction_id' => $transaction->id,
                    'amount' => $transaction->amount,
                ]);

                return $transaction;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao registrar despesa", [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Cancelar transação (estornar pagamento)
     */
    public function cancelTransaction(FinancialTransaction $transaction, ?string $reason = null): bool
    {
        try {
            return DB::transaction(function () use ($transaction, $reason) {
                $transaction->update([
                    'status' => 'cancelled',
                    'notes' => ($transaction->notes ? $transaction->notes . "\n" : '') .
                               "Cancelado em " . now()->format('d/m/Y H:i') .
                               ($reason ? ": {$reason}" : ''),
                ]);

                Log::info("Transação financeira cancelada", [
                    'transaction_id' => $transaction->id,
                    'reason' => $reason,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao cancelar transação financeira", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Excluir transação cancelada
     * Apenas transações canceladas e não rastreadas podem ser excluídas
     */
    public function deleteTransaction(FinancialTransaction $transaction): bool
    {
        try {
            // Verificar se a transação está cancelada
            if ($transaction->status !== 'cancelled') {
                throw new \Exception('Apenas transações canceladas podem ser excluídas.');
            }

            // Verificar se é uma transação rastreada
            // Regra geral: transações rastreadas não podem ser excluídas manualmente,
            // EXCETO receitas geradas a partir de pagamentos de mensalidade,
            // que podem ser excluídas desde que estejam canceladas.
            if ($transaction->source_type && $transaction->source_type !== MonthlyFeePayment::class) {
                throw new \Exception('Transações rastreadas automaticamente não podem ser excluídas.');
            }

            return DB::transaction(function () use ($transaction) {
                // Excluir anexos relacionados e arquivos físicos se existirem
                foreach ($transaction->attachments as $attachment) {
                    // Excluir arquivo físico do storage
                    if ($attachment->file_path && \Illuminate\Support\Facades\Storage::exists($attachment->file_path)) {
                        \Illuminate\Support\Facades\Storage::delete($attachment->file_path);
                    }
                    // Excluir registro do banco
                    $attachment->delete();
                }

                // Excluir a transação (soft delete)
                $transaction->delete();

                Log::info("Transação financeira excluída", [
                    'transaction_id' => $transaction->id,
                    'transaction_number' => $transaction->transaction_number,
                ]);

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Erro ao excluir transação financeira", [
                'transaction_id' => $transaction->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Obter relatório financeiro por período
     */
    public function getFinancialReport($startDate, $endDate): array
    {
        $transactions = FinancialTransaction::with('category')
            ->confirmed()
            ->byPeriod($startDate, $endDate)
            ->get();

        $receitas = $transactions->where('type', 'receita')->sum('amount');
        $despesas = $transactions->where('type', 'despesa')->sum('amount');
        $saldo = $receitas - $despesas;

        // Filtrar transações com categoria válida antes de agrupar
        $receitasPorCategoria = $transactions->where('type', 'receita')
            ->filter(fn($transaction) => $transaction->category !== null)
            ->groupBy('category_id')
            ->map(function ($group) {
                $category = $group->first()->category;
                return [
                    'category' => $category?->name ?? 'Sem Categoria',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values();

        $despesasPorCategoria = $transactions->where('type', 'despesa')
            ->filter(fn($transaction) => $transaction->category !== null)
            ->groupBy('category_id')
            ->map(function ($group) {
                $category = $group->first()->category;
                return [
                    'category' => $category?->name ?? 'Sem Categoria',
                    'total' => $group->sum('amount'),
                    'count' => $group->count(),
                ];
            })->values();

        return [
            'period' => [
                'start' => $startDate,
                'end' => $endDate,
            ],
            'summary' => [
                'receitas' => $receitas,
                'despesas' => $despesas,
                'saldo' => $saldo,
            ],
            'receitas_por_categoria' => $receitasPorCategoria,
            'despesas_por_categoria' => $despesasPorCategoria,
            'transactions' => $transactions,
        ];
    }

    /**
     * Gerar número único de transação
     */
    public function generateTransactionNumber(string $prefix): string
    {
        // Armazenar now() em uma variável para garantir consistência
        // Evita race condition se o código executar na transição de mês/ano
        $now = now();
        $year = $now->year;
        $month = $now->month; // Inteiro para usar em whereMonth()
        $monthFormatted = $now->format('m'); // String formatada para usar no LIKE e retorno
        
        $lastTransaction = FinancialTransaction::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->where('transaction_number', 'like', "{$prefix}-{$year}-{$monthFormatted}-%")
            ->orderBy('id', 'desc')
            ->lockForUpdate()
            ->first();

        // Extrair número sequencial de forma robusta
        // Formato esperado: PREFIX-YEAR-MONTH-NUMBER (ex: REC-2025-12-00001)
        $nextNumber = 1;
        if ($lastTransaction) {
            $parts = explode('-', $lastTransaction->transaction_number);
            // Verificar se o formato está correto (deve ter pelo menos 4 partes)
            if (count($parts) >= 4) {
                $lastNumber = intval($parts[3] ?? '0');
                $nextNumber = $lastNumber > 0 ? $lastNumber + 1 : 1;
            }
            // Se o formato não corresponder (ex: seeder antigo), começar do 1
        }
        
        return "{$prefix}-{$year}-{$monthFormatted}-" . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
}

