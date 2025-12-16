<?php

namespace App\Traits;

use Spatie\Activitylog\Traits\LogsActivity as BaseLogsActivity;
use Spatie\Activitylog\LogOptions;

trait LogsActivity
{
    use BaseLogsActivity;

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->getLoggableAttributes())
            ->logOnlyDirty() // Log apenas campos alterados
            ->dontSubmitEmptyLogs()
            ->useLogName($this->getLogName())
            ->setDescriptionForEvent(fn(string $eventName) => $this->getDescriptionForEvent($eventName));
    }

    /**
     * Atributos que serão registrados no log
     */
    protected function getLoggableAttributes(): array
    {
        return $this->fillable ?? ['*'];
    }

    /**
     * Nome da categoria de log
     */
    protected function getLogName(): string
    {
        return class_basename($this);
    }

    /**
     * Descrição personalizada por evento
     */
    protected function getDescriptionForEvent(string $eventName): string
    {
        $modelName = $this->getModelDisplayName();
        
        $descriptions = [
            'created' => "criou {$modelName}: {$this->getIdentifier()}",
            'updated' => "atualizou {$modelName}: {$this->getIdentifier()}",
            'deleted' => "excluiu {$modelName}: {$this->getIdentifier()}",
            'restored' => "restaurou {$modelName}: {$this->getIdentifier()}",
        ];

        return $descriptions[$eventName] ?? "{$eventName} {$modelName}";
    }

    /**
     * Nome amigável do modelo
     */
    protected function getModelDisplayName(): string
    {
        $names = [
            'Student' => 'o aluno',
            'Guardian' => 'o responsável',
            'Enrollment' => 'a matrícula',
            'Employee' => 'o colaborador',
            'Service' => 'o serviço',
            'Package' => 'o pacote',
            'Classroom' => 'a turma',
            'MonthlyFee' => 'o contrato de mensalidades',
            'MonthlyFeeInstallment' => 'a parcela de mensalidade',
            'MonthlyFeePayment' => 'o pagamento de mensalidade',
            'EnrollmentInvoice' => 'a fatura de matrícula',
            'EnrollmentPayment' => 'o pagamento de matrícula',
            'FinancialTransaction' => 'a transação financeira',
            'Payroll' => 'a folha de pagamento',
            'PayrollEntry' => 'o lançamento da folha',
            'Position' => 'o cargo',
            'Category' => 'a categoria',
            'Supplier' => 'o fornecedor/pagante',
        ];

        return $names[class_basename($this)] ?? 'o registro';
    }

    /**
     * Identificador do registro para o log
     */
    protected function getIdentifier(): string
    {
        return $this->name ?? $this->id ?? 'ID ' . $this->getKey();
    }
}

