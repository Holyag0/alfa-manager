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
        Schema::dropIfExists('enrollment_finances');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recriar a tabela se necessário (rollback)
        Schema::create('enrollment_finances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->decimal('monthly_fee', 10, 2)->default(0);
            $table->decimal('reservation_fee', 10, 2)->default(0);
            $table->decimal('total_paid', 10, 2)->default(0);
            $table->decimal('total_due', 10, 2)->default(0);
            $table->date('next_due_date')->nullable();
            $table->enum('payment_status', ['paid', 'pending', 'overdue', 'partial'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index(['enrollment_id', 'payment_status']);
            $table->index('next_due_date');
        });
    }
};
