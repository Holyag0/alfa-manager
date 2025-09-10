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
        Schema::create('enrollment_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->decimal('monthly_fee', 10, 2)->default(0); // Mensalidade base
            $table->decimal('reservation_fee', 10, 2)->default(0); // Taxa de reserva
            $table->decimal('total_paid', 10, 2)->default(0); // Total pago
            $table->decimal('total_due', 10, 2)->default(0); // Total em aberto
            $table->date('next_due_date')->nullable(); // Próximo vencimento
            $table->enum('payment_status', ['paid', 'pending', 'overdue', 'partial'])->default('pending');
            $table->text('notes')->nullable(); // Observações financeiras
            $table->timestamps();
            
            // Índices para otimização
            $table->index(['enrollment_id', 'payment_status']);
            $table->index('next_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('enrollment_finances');
    }
};
