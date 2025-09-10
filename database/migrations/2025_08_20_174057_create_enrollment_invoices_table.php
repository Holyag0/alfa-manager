<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('enrollment_invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->string('invoice_number')->unique(); // Número da fatura
            $table->enum('type', ['monthly', 'reservation', 'service', 'package', 'other']); // Tipo da fatura
            $table->string('description'); // Descrição da fatura
            $table->decimal('amount', 10, 2); // Valor da fatura
            $table->date('due_date'); // Data de vencimento
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled'])->default('pending');
            $table->date('paid_date')->nullable(); // Data do pagamento
            $table->text('notes')->nullable(); // Observações
            $table->timestamps();
            
            // Índices para otimização
            $table->index(['enrollment_id', 'status']);
            $table->index(['due_date', 'status']);
            $table->index('invoice_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_invoices');
    }
};
