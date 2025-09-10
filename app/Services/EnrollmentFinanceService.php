<?php

namespace App\Services;

use App\Models\Enrollment;
use App\Models\EnrollmentFinance;
use App\Models\EnrollmentInvoice;
use App\Models\EnrollmentPayment;
use App\Models\Service;
use App\Models\Package;
use Illuminate\Support\Facades\DB;
use Exception;

class EnrollmentFinanceService
{
    /**
     * Criar registro financeiro para uma matrícula
     */
    public function createFinanceRecord(Enrollment $enrollment, array $data = [])
    {
        return DB::transaction(function () use ($enrollment, $data) {
            // Verificar se já existe registro financeiro
            if ($enrollment->finance) {
                throw new Exception('Registro financeiro já existe para esta matrícula.');
            }

            return EnrollmentFinance::create([
                'enrollment_id' => $enrollment->id,
                'monthly_fee' => $data['monthly_fee'] ?? 0,
                'reservation_fee' => $data['reservation_fee'] ?? 0,
                'total_paid' => 0,
                'total_due' => 0,
                'next_due_date' => $data['next_due_date'] ?? null,
                'payment_status' => 'pending',
                'notes' => $data['notes'] ?? null
            ]);
        });
    }

    /**
     * Criar fatura de reserva
     */
    public function createReservationInvoice(Enrollment $enrollment, $amount = null, $dueDate = null)
    {
        return DB::transaction(function () use ($enrollment, $amount, $dueDate) {
            // Buscar serviço de reserva
            $reservationService = \App\Models\Service::reservation()->first();
            
            if (!$reservationService) {
                throw new \Exception('Serviço de reserva não encontrado');
            }

            // Buscar preço e descrição para a turma específica
            $invoiceAmount = $amount ?? $reservationService->getPriceForClassroom($enrollment->classroom_id);
            $invoiceDescription = $reservationService->getDescriptionForClassroom($enrollment->classroom_id);
            
            // Criar registro financeiro se não existir
            if (!$enrollment->finance) {
                $this->createFinanceRecord($enrollment, [
                    'reservation_fee' => $invoiceAmount,
                    'next_due_date' => $dueDate ?? now()->addDays(7)
                ]);
            }

            // Criar fatura de reserva
            $invoice = EnrollmentInvoice::create([
                'enrollment_id' => $enrollment->id,
                'invoice_number' => EnrollmentInvoice::generateInvoiceNumber(),
                'type' => 'reservation',
                'description' => $invoiceDescription,
                'amount' => $invoiceAmount,
                'due_date' => $dueDate ?? now()->addDays(7),
                'status' => 'pending',
                'notes' => 'Fatura gerada automaticamente para reserva de vaga'
            ]);

            // Atualizar registro financeiro
            if ($enrollment->finance) {
                $enrollment->finance->reservation_fee = $invoiceAmount;
                $enrollment->finance->calculateTotalDue();
            }

            return $invoice;
        });
    }

    /**
     * Criar fatura de mensalidade
     */
    public function createMonthlyInvoice(Enrollment $enrollment, $month, $year, $amount = null)
    {
        return DB::transaction(function () use ($enrollment, $month, $year, $amount) {
            // Buscar serviço de mensalidade
            $monthlyService = \App\Models\Service::monthly()->first();
            
            if (!$monthlyService) {
                throw new \Exception('Serviço de mensalidade não encontrado');
            }

            // Buscar preço e descrição para a turma específica
            $invoiceAmount = $amount ?? $monthlyService->getPriceForClassroom($enrollment->classroom_id);
            $invoiceDescription = $monthlyService->getDescriptionForClassroom($enrollment->classroom_id);
            
            // Verificar se já existe fatura para este mês/ano
            $existingInvoice = $enrollment->invoices()
                ->where('type', 'monthly')
                ->whereYear('due_date', $year)
                ->whereMonth('due_date', $month)
                ->first();

            if ($existingInvoice) {
                throw new Exception('Fatura de mensalidade já existe para este período.');
            }

            // Criar fatura de mensalidade
            $dueDate = \Carbon\Carbon::create($year, $month, 10); // Vencimento no dia 10

            $invoice = EnrollmentInvoice::create([
                'enrollment_id' => $enrollment->id,
                'invoice_number' => EnrollmentInvoice::generateInvoiceNumber(),
                'type' => 'monthly',
                'description' => $invoiceDescription . " - {$month}/{$year}",
                'amount' => $invoiceAmount,
                'due_date' => $dueDate,
                'status' => 'pending',
                'notes' => "Mensalidade referente a {$month}/{$year}"
            ]);

            // Atualizar registro financeiro
            if ($enrollment->finance) {
                $enrollment->finance->monthly_fee = $invoiceAmount;
                $enrollment->finance->calculateTotalDue();
            }

            return $invoice;
        });
    }

    /**
     * Registrar pagamento
     */
    public function registerPayment(Enrollment $enrollment, array $data)
    {
        return DB::transaction(function () use ($enrollment, $data) {
            // Criar pagamento
            $payment = EnrollmentPayment::create([
                'enrollment_id' => $enrollment->id,
                'invoice_id' => $data['invoice_id'] ?? null,
                'payment_number' => EnrollmentPayment::generatePaymentNumber(),
                'type' => $data['type'] ?? 'other',
                'description' => $data['description'],
                'amount' => $data['amount'],
                'method' => $data['method'],
                'payment_date' => $data['payment_date'] ?? now(),
                'reference' => $data['reference'] ?? null,
                'status' => $data['status'] ?? 'confirmed',
                'notes' => $data['notes'] ?? null
            ]);

            // Se o pagamento está confirmado, atualizar fatura e totais
            if ($payment->status === 'confirmed') {
                if ($payment->invoice) {
                    $payment->invoice->markAsPaid($payment->payment_date);
                }

                if ($enrollment->finance) {
                    $enrollment->finance->calculateTotalDue();
                }
            }

            return $payment;
        });
    }

    /**
     * Criar fatura automática baseada no processo da matrícula
     * Lógica inteligente: verifica processo e serviços vinculados à turma
     */
    public function createAutomaticInvoice(Enrollment $enrollment)
    {
        return DB::transaction(function () use ($enrollment) {
            // 1. Buscar serviços vinculados à turma através da tabela pivot
            $classroomServices = \App\Models\ClassroomService::where('classroom_id', $enrollment->classroom_id)
                ->with('service.category')
                ->get();
            
            if ($classroomServices->isEmpty()) {
                throw new Exception('Nenhum serviço vinculado à turma encontrado.');
            }
            
            // 2. Determinar categoria baseada no processo
            $targetCategory = $this->getCategoryByProcess($enrollment->process);
            
            // 3. Filtrar serviços por categoria
            $matchingServices = $classroomServices->filter(function ($classroomService) use ($targetCategory) {
                return $classroomService->service && 
                       $classroomService->service->category === $targetCategory;
            });
            
            if ($matchingServices->isEmpty()) {
                throw new Exception("Nenhum serviço da categoria '{$targetCategory}' encontrado para a turma.");
            }
            
            // 4. Pegar o primeiro serviço da categoria (pode ser melhorado para escolher o mais apropriado)
            $selectedClassroomService = $matchingServices->first();
            $selectedService = $selectedClassroomService->service;
            
            // 5. Usar preço e descrição específicos da turma
            $invoiceAmount = $selectedClassroomService->price;
            $invoiceDescription = $selectedClassroomService->description;
            
            // 6. Criar registro financeiro se não existir
            if (!$enrollment->finance) {
                $this->createFinanceRecord($enrollment, [
                    'reservation_fee' => $enrollment->process === 'reserva' ? $invoiceAmount : 0,
                    'monthly_fee' => $enrollment->process !== 'reserva' ? $invoiceAmount : 0,
                    'next_due_date' => $enrollment->process === 'reserva' ? now()->addDays(7) : now()->addMonth()->day(10)
                ]);
            }
            
            // 7. Criar fatura
            $invoice = EnrollmentInvoice::create([
                'enrollment_id' => $enrollment->id,
                'invoice_number' => EnrollmentInvoice::generateInvoiceNumber(),
                'type' => $enrollment->process === 'reserva' ? 'reservation' : 'monthly',
                'description' => $invoiceDescription,
                'amount' => $invoiceAmount,
                'due_date' => $enrollment->process === 'reserva' ? now()->addDays(7) : now()->addMonth()->day(10),
                'status' => 'pending',
                'notes' => "Fatura automática - " . ($selectedService->name ?? 'Serviço') . " ({$targetCategory})"
            ]);
            
            // 8. Atualizar registro financeiro
            if ($enrollment->finance) {
                if ($enrollment->process === 'reserva') {
                    $enrollment->finance->reservation_fee = $invoiceAmount;
                } else {
                    $enrollment->finance->monthly_fee = $invoiceAmount;
                }
                $enrollment->finance->calculateTotalDue();
            }
            
            return $invoice;
        });
    }
    
    /**
     * Determinar categoria baseada no processo da matrícula
     */
    public function getCategoryByProcess($process)
    {
        switch ($process) {
            case 'reserva':
                return 'matricula-reserva';
            case 'completa':
            case 'aguardando_pagamento':
            case 'aguardando_documentos':
                return 'matricula';
            default:
                return 'matricula'; // Padrão para outros processos
        }
    }

    /**
     * Processar reserva de matrícula
     */
    public function processReservation(Enrollment $enrollment, $reservationAmount, $paymentData = [])
    {
        return DB::transaction(function () use ($enrollment, $reservationAmount, $paymentData) {
            // Atualizar processo da matrícula para reserva
            $enrollment->process = 'reserva';
            $enrollment->save();

            // Criar fatura de reserva
            $invoice = $this->createReservationInvoice($enrollment, $reservationAmount);

            // Se há dados de pagamento, registrar o pagamento
            if (!empty($paymentData)) {
                $paymentData['invoice_id'] = $invoice->id;
                $paymentData['type'] = 'reservation';
                $paymentData['description'] = 'Pagamento de Reserva - ' . $enrollment->classroom->name;
                
                $payment = $this->registerPayment($enrollment, $paymentData);

                // Se o pagamento foi confirmado, atualizar processo
                if ($payment->status === 'confirmed') {
                    $enrollment->process = 'aguardando_documentos';
                    $enrollment->save();
                }
            }

            return [
                'enrollment' => $enrollment->fresh(['finance', 'invoices', 'payments']),
                'invoice' => $invoice
            ];
        });
    }

    /**
     * Finalizar matrícula (processo completo)
     */
    public function completeEnrollment(Enrollment $enrollment)
    {
        return DB::transaction(function () use ($enrollment) {
            // Verificar se há reserva paga
            $reservationInvoice = $enrollment->invoices()
                ->where('type', 'reservation')
                ->where('status', 'paid')
                ->first();

            if (!$reservationInvoice) {
                throw new Exception('É necessário ter uma reserva paga para finalizar a matrícula.');
            }

            // Atualizar processo da matrícula
            $enrollment->process = 'completa';
            $enrollment->status = 'active';
            $enrollment->save();

            // Criar primeira mensalidade se não existir
            $currentMonth = now()->month;
            $currentYear = now()->year;

            $existingMonthly = $enrollment->invoices()
                ->where('type', 'monthly')
                ->whereYear('due_date', $currentYear)
                ->whereMonth('due_date', $currentMonth)
                ->first();

            if (!$existingMonthly && $enrollment->finance && $enrollment->finance->monthly_fee > 0) {
                $this->createMonthlyInvoice($enrollment, $currentMonth, $currentYear);
            }

            return $enrollment->fresh(['finance', 'invoices', 'payments']);
        });
    }

    /**
     * Buscar resumo financeiro da matrícula
     */
    public function getFinancialSummary(Enrollment $enrollment)
    {
        $finance = $enrollment->finance;
        
        if (!$finance) {
            return null;
        }

        $pendingInvoices = $enrollment->invoices()->pending()->count();
        $overdueInvoices = $enrollment->invoices()->overdue()->count();
        $totalInvoices = $enrollment->invoices()->where('status', '!=', 'cancelled')->sum('amount');
        $totalPayments = $enrollment->payments()->confirmed()->sum('amount');

        return [
            'finance' => $finance,
            'summary' => [
                'total_invoices' => $totalInvoices,
                'total_payments' => $totalPayments,
                'total_due' => $finance->total_due,
                'pending_invoices' => $pendingInvoices,
                'overdue_invoices' => $overdueInvoices,
                'payment_status' => $finance->payment_status
            ],
            'recent_invoices' => $enrollment->invoices()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_payments' => $enrollment->payments()
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
        ];
    }
}
