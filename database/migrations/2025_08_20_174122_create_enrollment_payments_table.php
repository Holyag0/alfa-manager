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
        Schema::create('enrollment_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('invoice_id')->nullable()->constrained('enrollment_invoices')->onDelete('set null');
            $table->string('payment_number')->unique(); // Número do pagamento
            $table->enum('type', ['reservation', 'monthly', 'service', 'package', 'other']); // Tipo do pagamento
            $table->string('description'); // Descrição do pagamento
            $table->decimal('amount', 10, 2); // Valor do pagamento
            $table->enum('method', ['cash', 'pix', 'credit_card', 'debit_card', 'bank_transfer', 'check']); // Método de pagamento
            $table->date('payment_date'); // Data do pagamento
            $table->string('reference')->nullable(); // Referência (comprovante, etc.)
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('confirmed');
            $table->text('notes')->nullable(); // Observações
            $table->timestamps();
            
            // Índices para otimização
            $table->index(['enrollment_id', 'status']);
            $table->index(['payment_date', 'status']);
            $table->index('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_payments');
    }
};
