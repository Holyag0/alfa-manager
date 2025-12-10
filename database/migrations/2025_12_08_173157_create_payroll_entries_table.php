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
        Schema::create('payroll_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payroll_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            
            // Proventos
            $table->decimal('base_salary', 10, 2)->default(0);      // SAL. CART
            $table->decimal('gratification', 10, 2)->default(0);    // Gratif
            $table->decimal('bonus', 10, 2)->default(0);            // Abonus
            $table->decimal('gross_salary', 10, 2)->default(0);     // SAL 2025 (calculado)
            
            // Descontos
            $table->decimal('inss_deduction', 10, 2)->default(0);   // INSS
            $table->decimal('advance_deduction', 10, 2)->default(0); // VALE
            $table->decimal('other_deductions', 10, 2)->default(0); // Outros descontos
            
            // Líquido
            $table->decimal('net_salary', 10, 2)->default(0);       // Sal. Líquido (calculado)
            
            // Pagamento
            $table->enum('payment_method', ['pix', 'poupanca', 'conta_corrente', 'dinheiro'])->nullable();
            $table->string('payment_info')->nullable();              // CPF PIX, dados conta, etc.
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            
            $table->timestamps();
            
            $table->unique(['payroll_id', 'employee_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_entries');
    }
};
