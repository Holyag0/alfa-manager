<?php

namespace Database\Seeders;

use App\Models\MonthlyFee;
use App\Models\MonthlyFeeInstallment;
use App\Models\MonthlyFeePayment;
use App\Models\Enrollment;
use App\Models\ClassroomService;
use App\Models\Guardian;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class MonthlyFeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buscar matrículas ativas existentes
        $enrollments = Enrollment::where('status', 'active')->limit(5)->get();

        if ($enrollments->isEmpty()) {
            $this->command->info('Nenhuma matrícula ativa encontrada. Pulando seeder de mensalidades.');
            return;
        }

        foreach ($enrollments as $enrollment) {
            // Buscar serviço de mensalidade da turma
            $classroomService = ClassroomService::where('classroom_id', $enrollment->classroom_id)
                ->where('type', 'monthly')
                ->first();

            if (!$classroomService) {
                continue;
            }

            // Verificar se já existe contrato para esta matrícula
            $existingFee = MonthlyFee::where('enrollment_id', $enrollment->id)
                ->where('academic_year', now()->year)
                ->first();

            if ($existingFee) {
                continue;
            }

            // Criar contrato de mensalidades
            $monthlyFee = MonthlyFee::create([
                'enrollment_id' => $enrollment->id,
                'classroom_service_id' => $classroomService->id,
                'academic_year' => now()->year,
                'contract_number' => 'MF-' . now()->year . '-' . str_pad($enrollment->id, 4, '0', STR_PAD_LEFT),
                'base_amount' => $classroomService->amount,
                'total_installments' => 12,
                'has_sibling_discount' => rand(0, 1) == 1,
                'sibling_discount_amount' => rand(0, 1) == 1 ? 30.00 : 0,
                'has_punctuality_discount' => true,
                'punctuality_discount_amount' => 10.00,
                'start_date' => Carbon::create(now()->year, 1, 1),
                'end_date' => Carbon::create(now()->year, 12, 31),
                'due_day' => 10,
                'status' => 'active',
            ]);

            // Gerar 12 parcelas
            for ($month = 1; $month <= 12; $month++) {
                $dueDate = Carbon::create(now()->year, $month, $monthlyFee->due_day);
                $isPast = $dueDate->isPast();
                
                // Definir status baseado na data
                $status = 'pending';
                if ($isPast) {
                    $status = rand(0, 3) == 0 ? 'overdue' : 'paid';
                }

                $baseAmount = $monthlyFee->base_amount;
                $siblingDiscount = $monthlyFee->has_sibling_discount ? $monthlyFee->sibling_discount_amount : 0;
                $punctualityDiscount = 0;
                $interestAmount = 0;
                $fineAmount = 0;

                // Se pago, aplicar desconto de pontualidade
                if ($status === 'paid') {
                    $punctualityDiscount = $monthlyFee->punctuality_discount_amount;
                }

                // Se vencido, calcular juros e multa
                if ($status === 'overdue') {
                    $daysLate = Carbon::now()->diffInDays($dueDate);
                    $interestAmount = ($baseAmount * 0.01 * $daysLate / 30); // 1% ao mês
                    $fineAmount = $baseAmount * 0.02; // 2% de multa
                }

                $finalAmount = $baseAmount - $siblingDiscount - $punctualityDiscount + $interestAmount + $fineAmount;

                $installment = MonthlyFeeInstallment::create([
                    'monthly_fee_id' => $monthlyFee->id,
                    'reference_month' => Carbon::create(now()->year, $month, 1)->format('Y-m'),
                    'installment_number' => $month,
                    'base_amount' => $baseAmount,
                    'sibling_discount' => $siblingDiscount,
                    'punctuality_discount' => $punctualityDiscount,
                    'other_discounts' => 0,
                    'interest_amount' => $interestAmount,
                    'fine_amount' => $fineAmount,
                    'final_amount' => $finalAmount,
                    'due_date' => $dueDate,
                    'paid_date' => $status === 'paid' ? $dueDate->addDays(rand(-3, 3)) : null,
                    'status' => $status,
                ]);

                // Se pago, criar registro de pagamento
                if ($status === 'paid') {
                    $guardian = $enrollment->student->guardians()->first();
                    
                    if ($guardian) {
                        MonthlyFeePayment::create([
                            'monthly_fee_installment_id' => $installment->id,
                            'paid_by_guardian_id' => $guardian->id,
                            'payment_number' => 'PAY-' . now()->year . '-' . str_pad($installment->id, 5, '0', STR_PAD_LEFT),
                            'amount' => $finalAmount,
                            'original_installment_amount' => $baseAmount,
                            'discount_applied' => $siblingDiscount + $punctualityDiscount,
                            'interest_applied' => $interestAmount,
                            'fine_applied' => $fineAmount,
                            'method' => collect(['pix', 'credit_card', 'debit_card', 'cash'])->random(),
                            'payment_date' => $installment->paid_date,
                            'confirmation_date' => $installment->paid_date,
                            'status' => 'confirmed',
                        ]);
                    }
                }
            }

            $this->command->info("Contrato de mensalidades criado para matrícula #{$enrollment->id}");
        }

        $this->command->info('Seeder de mensalidades concluído!');
    }
}

