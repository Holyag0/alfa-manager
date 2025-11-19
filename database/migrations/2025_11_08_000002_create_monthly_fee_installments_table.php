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
        Schema::create('monthly_fee_installments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monthly_fee_id')->constrained('monthly_fees')->onDelete('cascade');
            $table->string('reference_month', 7); // YYYY-MM
            $table->integer('installment_number'); // 1-12
            $table->decimal('base_amount', 10, 2);
            $table->decimal('sibling_discount', 10, 2)->default(0);
            $table->decimal('punctuality_discount', 10, 2)->default(0);
            $table->decimal('other_discounts', 10, 2)->default(0);
            $table->decimal('interest_amount', 10, 2)->default(0);
            $table->decimal('fine_amount', 10, 2)->default(0);
            $table->decimal('final_amount', 10, 2);
            $table->date('due_date');
            $table->date('paid_date')->nullable();
            $table->enum('status', ['pending', 'paid', 'overdue', 'cancelled', 'waived'])->default('pending');
            $table->foreignId('invoice_id')->nullable()->constrained('enrollment_invoices')->onDelete('set null');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('monthly_fee_id');
            $table->index('reference_month');
            $table->index('installment_number');
            $table->index('due_date');
            $table->index('status');
            $table->unique(['monthly_fee_id', 'installment_number'], 'mfi_fee_installment_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_fee_installments');
    }
};

