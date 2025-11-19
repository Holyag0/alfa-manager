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
        Schema::create('monthly_fees', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enrollment_id')->constrained('enrollments')->onDelete('cascade');
            $table->foreignId('classroom_service_id')->constrained('classroom_services')->onDelete('restrict');
            $table->string('academic_year', 4);
            $table->string('contract_number')->unique();
            $table->decimal('base_amount', 10, 2);
            $table->integer('total_installments')->default(12);
            $table->boolean('has_sibling_discount')->default(false);
            $table->decimal('sibling_discount_amount', 10, 2)->default(0);
            $table->boolean('has_punctuality_discount')->default(false);
            $table->decimal('punctuality_discount_amount', 10, 2)->default(0);
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('due_day')->default(10);
            $table->enum('status', ['active', 'cancelled', 'completed', 'suspended'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Índices
            $table->index('enrollment_id');
            $table->index('academic_year');
            $table->index('status');
            $table->index('contract_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_fees');
    }
};

