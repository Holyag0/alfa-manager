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
        Schema::create('monthly_fee_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_fee_installment_id')->constrained('monthly_fee_installments')->onDelete('cascade');
            $table->foreignId('paid_by_guardian_id')->constrained('guardians')->onDelete('restrict');
            $table->string('payment_number')->unique();
            $table->decimal('amount', 10, 2);
            $table->decimal('original_installment_amount', 10, 2);
            $table->decimal('discount_applied', 10, 2)->default(0);
            $table->decimal('interest_applied', 10, 2)->default(0);
            $table->decimal('fine_applied', 10, 2)->default(0);
            $table->enum('method', ['pix', 'credit_card', 'debit_card', 'cash', 'bank_transfer', 'check'])->default('cash');
            $table->dateTime('payment_date');
            $table->dateTime('confirmation_date')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled', 'refunded'])->default('pending');
            $table->string('reference')->nullable();
            $table->string('transaction_id')->nullable();
            $table->foreignId('enrollment_invoice_id')->nullable()->constrained('enrollment_invoices')->onDelete('set null');
            $table->foreignId('enrollment_payment_id')->nullable()->constrained('enrollment_payments')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('monthly_fee_installment_id');
            $table->index('paid_by_guardian_id');
            $table->index('payment_date');
            $table->index('status');
            $table->index('method');
            $table->index('payment_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_fee_payments');
    }
};

