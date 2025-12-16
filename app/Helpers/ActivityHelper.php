<?php

namespace App\Helpers;

use Spatie\Activitylog\Contracts\Activity;

class ActivityHelper
{
    /**
     * Log de ação customizada
     */
    public static function log(string $description, $subject = null, array $properties = []): Activity
    {
        return activity()
            ->causedBy(auth()->user())
            ->performedOn($subject)
            ->withProperties($properties)
            ->log($description);
    }

    /**
     * Log de ação financeira
     */
    public static function logFinancial(string $action, $subject, float $amount, array $extra = []): Activity
    {
        return self::log(
            $action,
            $subject,
            array_merge([
                'amount' => $amount,
                'formatted_amount' => 'R$ ' . number_format($amount, 2, ',', '.'),
            ], $extra)
        );
    }

    /**
     * Log de ação de matrícula
     */
    public static function logEnrollment(string $action, $enrollment, array $extra = []): Activity
    {
        return self::log(
            $action,
            $enrollment,
            array_merge([
                'student' => $enrollment->student->name ?? null,
                'classroom' => $enrollment->classroom->name ?? null,
                'academic_year' => $enrollment->academic_year,
            ], $extra)
        );
    }

    /**
     * Log de ação de pagamento
     */
    public static function logPayment(string $action, $payment, array $extra = []): Activity
    {
        return self::logFinancial(
            $action,
            $payment,
            $payment->amount ?? 0,
            array_merge([
                'payment_method' => $payment->method ?? null,
                'payment_date' => $payment->payment_date ?? null,
            ], $extra)
        );
    }

    /**
     * Log de ação de folha de pagamento
     */
    public static function logPayroll(string $action, $payroll, array $extra = []): Activity
    {
        return self::log(
            $action,
            $payroll,
            array_merge([
                'reference' => $payroll->reference ?? null,
                'year' => $payroll->year ?? null,
                'month' => $payroll->month ?? null,
                'status' => $payroll->status ?? null,
            ], $extra)
        );
    }
}

